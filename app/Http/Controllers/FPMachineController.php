<?php

namespace App\Http\Controllers;

use App\Models\FPMachine;
use App\Models\FPMachineLog;
use App\Models\FPMachineUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;


class FPMachineController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    function parse_data($data,$p1,$p2)
    {
        $data = " " . $data;
        $hasil = "";
        $awal = strpos($data, $p1);
        if ($awal != "") {
            $akhir = strpos(strstr($data, $p1), $p2);
            if ($akhir != "") {
                $hasil = substr($data, $awal + strlen($p1), $akhir - strlen($p1));
            }
        }
        return $hasil;
    }

    public function index($id){
        $id_wilayah = $id;
        $list_machine = FPMachine::withCount('users')->orderBy('id')->get();
        $last_log = FPMachineLog::select('fp_machine_id', DB::raw('MAX([date]) AS last_date'))
            ->groupBy('fp_machine_id')->pluck('last_date', 'fp_machine_id');
        return view('master-data.wilayah.wium.fingerprint.index', compact('list_machine', 'id_wilayah', 'last_log'));
    }

    /* ----------------------- CRUD mesin ----------------------- */

    private function rules(?int $ignoreId = null): array
    {
        return [
            'location' => ['required', 'string', 'max:100'],
            'ip_address' => ['required', 'ipv4'],
            'port' => [
                'required', 'integer', 'between:1,65535',
                Rule::unique('fp_machine', 'port')
                    ->where(fn ($q) => $q->where('ip_address', request('ip_address')))
                    ->ignore($ignoreId),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'location.required' => 'Lokasi wajib diisi.',
            'ip_address.required' => 'IP address wajib diisi.',
            'ip_address.ipv4' => 'Format IP address tidak valid. Contoh: 10.5.0.200',
            'port.required' => 'Port wajib diisi.',
            'port.integer' => 'Port harus berupa angka.',
            'port.between' => 'Port harus di antara 1 dan 65535.',
            'port.unique' => 'Mesin dengan IP dan port ini sudah terdaftar.',
        ];
    }

    public function create($id)
    {
        return view('master-data.wilayah.wium.fingerprint.form', [
            'id_wilayah' => $id,
            'machine' => new FPMachine(['port' => 80]),
        ]);
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate($this->rules(), $this->messages());
        FPMachine::create($data);
        alert()->success('Tersimpan', 'Mesin fingerprint ditambahkan.');
        return redirect()->route('wilayah.fingerprint-machine', $id);
    }

    public function edit($id, $machine)
    {
        return view('master-data.wilayah.wium.fingerprint.form', [
            'id_wilayah' => $id,
            'machine' => FPMachine::findOrFail($machine),
        ]);
    }

    public function update(Request $request, $id, $machine)
    {
        $fp = FPMachine::findOrFail($machine);
        $data = $request->validate($this->rules($fp->id), $this->messages());
        $old = $fp->ip_address . ':' . $fp->port;
        $fp->update($data);
        Log::info("Mesin fingerprint #{$fp->id} diubah dari {$old} ke {$fp->ip_address}:{$fp->port} oleh user #" . auth()->id());
        alert()->success('Tersimpan', 'Data mesin fingerprint diperbarui.');
        return redirect()->route('wilayah.fingerprint-machine', $id);
    }

    public function destroy($id, $machine)
    {
        $fp = FPMachine::withCount(['users', 'logs'])->findOrFail($machine);
        if ($fp->users_count > 0 || $fp->logs_count > 0) {
            alert()->warning('Tidak bisa dihapus',
                "Mesin ini masih punya {$fp->users_count} user dan {$fp->logs_count} log absensi. Jika mesin diganti, cukup ubah IP dan port-nya.");
            return redirect()->back();
        }
        $fp->delete();
        alert()->success('Terhapus', 'Mesin fingerprint dihapus.');
        return redirect()->route('wilayah.fingerprint-machine', $id);
    }

    /**
     * Cek apakah server SIPekan bisa menjangkau mesin: koneksi TCP ke IP:port,
     * lalu (jika terbuka) apakah mesin menjawab web service /iWsService
     * yang dipakai untuk menarik user dan log absensi.
     */
    public function testConnection($id, $machine)
    {
        $fp = FPMachine::findOrFail($machine);
        $target = "{$fp->ip_address}:{$fp->port}";

        $conn = @fsockopen($fp->ip_address, (int) $fp->port, $errno, $errstr, 5);
        if (!$conn) {
            alert()->error('Tidak terhubung', "Server tidak bisa menjangkau {$target}. Pastikan mesin menyala, satu jaringan dengan server, dan IP/port benar.");
            return redirect()->back();
        }

        stream_set_timeout($conn, 5);
        $soap = '<GetDate><ArgComKey xsi:type="xsd:integer">0</ArgComKey></GetDate>';
        fwrite($conn, "POST /iWsService HTTP/1.0\r\nContent-Type: text/xml\r\nContent-Length: " . strlen($soap) . "\r\n\r\n" . $soap . "\r\n");
        $reply = '';
        while (!feof($conn) && ($line = fgets($conn, 1024)) !== false) {
            $reply .= $line;
            if (strlen($reply) > 4096) break;
        }
        fclose($conn);

        if (stripos($reply, 'GetDateResponse') !== false || stripos($reply, 'HTTP/') === 0) {
            alert()->success('Terhubung', "Mesin di {$target} menjawab web service. Tarik user dan log absensi siap dipakai.");
        } else {
            alert()->warning('Port terbuka, tapi bukan web service',
                "{$target} bisa dijangkau tetapi tidak menjawab web service SIPekan. Port 4370 adalah protokol bawaan ZKTeco; SIPekan memakai web service yang biasanya di port 80.");
        }
        return redirect()->back();
    }

    public function getUsersMachine()
    {
        $machine = FPMachine::all();
        $ip = '';
        $location = '';
        $machine_id = '';
        $key = '0';
        $port = '';
        foreach ($machine as $m){
            $ip = $m->ip_address;
            $port = $m->port;
            $location = $m->location;
            $machine_id = $m->id;

            $connect = @fsockopen($ip, (int) $port, $errno, $errstr, 10);
            if ($connect) {
//            echo "Connected to $location, $ip:$port\n";
                $soap_request="<GetUserInfo>
                            <ArgComKey xsi:type=\"xsd:integer\">".$key."</ArgComKey>
                            <Arg><PIN xsi:type=\"xsd:integer\">ALL</PIN></Arg>
                        </GetUserInfo>";
                $newLine="\r\n";
                fputs($connect, "POST /iWsService HTTP/1.0".$newLine);
                fputs($connect, "Content-Type: text/xml".$newLine);
                fputs($connect, "Content-Length: ".strlen($soap_request).$newLine.$newLine);
                fputs($connect, $soap_request.$newLine);
                $buffer="";
                while($Response=fgets($connect, 1024)){
                    $buffer=$buffer.$Response;
                }

                $buffer = $this->parse_data($buffer,"<GetUserInfoResponse>","</GetUserInfoResponse>");
                $buffer = explode("\r\n",$buffer);
//                dd(count($buffer));
                $Selisih = 0;

//            dd($buffer);
                for($a=0;$a<count($buffer);$a++){
                    $data= $this->parse_data($buffer[$a], "<Row>", "</Row>");

                    $pin = $this->parse_data($data, "<PIN2>", "</PIN2>");
                    $name = $this->parse_data($data, "<Name>", "</Name>");
                    $name = strtolower($name);

                    if ($pin != '' && $name != '') {
                        $cek = FPMachineUser::where('fp_machine_id', $machine_id)->where('pin', $pin)->count();
//                        $selisih = array_sum($data) - $cek;
//                        dd($cek);
                        if ($cek == 0) {
                            FPMachineUser::insert([
                                'fp_machine_id' => $machine_id,
                                'pin' => $pin,
                                'name' => $name,
                            ]);
                            alert()->success('Success','Data Telah Ditambahkan');
                        }else{
                            alert()->info('Info','Data Sudah Terupdate');
                        }
                    }
                }
            }else{
//            echo "Connected to $ip:80\n";
                alert()->error('Tidak terhubung', "Mesin {$location} ({$ip}:{$port}) tidak bisa dijangkau. Periksa IP dan port di halaman Fingerprint Machine.");
                return redirect()->back();
            }
        }
        return redirect()->back();
    }

    public function ListUser($id_wilayah, $id_machine)
    {
        $list_user = FPMachineUser::where('fp_machine_id', $id_machine)->get();
        $user_sipekan = User::where('wilayah_id', $id_wilayah)->orderBy('name')->get();
        return view('master-data.wilayah.wium.fingerprint.list-user', compact('list_user', 'user_sipekan'));
    }

    public function HubungkanUser($user_id, $id)
    {
        FPMachineUser::where('id', $id)->update(['user_id' => $user_id]);
        alert()->success('Success','ID Berhasil Dihubungkan');
        return redirect()->back();
    }

    public function connectUser(Request $request)
    {
        FPMachineUser::where('id', $request->id)->update(['user_id' => $request->user_id]);
        alert()->success('Success','ID Berhasil Dihubungkan');
        return redirect()->back();
    }

    public function LogAttendance($id_wilayah, $id_machine)
    {
//        $datetime = date('Y-m-2');
        $list_log = FPMachineLog::all();
        return view('master-data.wilayah.wium.fingerprint.log-attendance', compact('list_log'));
    }

    public function GetLogAttendance()
    {
        $machine = FPMachine::all();
        $current_date1 = date('2025-03-09');
        $current_date2 = date('Y-m-d');
        $ip = '';
        $location = '';
        $machine_id = '';
        $key = '0';
        $port = '';
        foreach ($machine as $m){
            $ip = $m->ip_address;
            $port = $m->port;
            $location = $m->location;
            $machine_id = $m->id;

            $connect = @fsockopen($ip, (int) $port, $errno, $errstr, 10);
            if ($connect) {
//            echo "Connected to $location, $ip:$port\n";
                $soap_request="<GetAttLog>
                                <ArgComKey xsi:type=\"xsd:integer\">".$key."</ArgComKey>
                                <Arg><PIN xsi:type=\"xsd:integer\">ALL</PIN></Arg>
                            </GetAttLog>";
                $newLine="\r\n";
                fputs($connect, "POST /iWsService HTTP/1.0".$newLine);
                fputs($connect, "Content-Type: text/xml".$newLine);
                fputs($connect, "Content-Length: ".strlen($soap_request).$newLine.$newLine);
                fputs($connect, $soap_request.$newLine);
                $buffer="";
                while($Response=fgets($connect, 1024)){
                    $buffer=$buffer.$Response;
                }

                $buffer = $this->parse_data($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
                $buffer = explode("\r\n",$buffer);

//            dd($buffer);
                //COde Lama
                for($a=count($buffer) - 1;$a>0;$a--){
//                for($a=0;$a<count($buffer);$a++){
                    $data= $this->parse_data($buffer[$a], "<Row>", "</Row>");
//dd($data);
                    $pin = $this->parse_data($data, "<PIN>", "</PIN>");
                    $datetime = $this->parse_data($data, "<DateTime>", "</DateTime>");
                    $verified = $this->parse_data($data, "<Verified>", "</Verified>");
                    $status = $this->parse_data($data, "<Status>", "</Status>");
                    $workcode = $this->parse_data($data, "<WorkCode>", "</WorkCode>");
                    $date = date('Y-m-d', strtotime($datetime));
                    $time = date('H:i:s', strtotime($datetime));

                    if ($date >= $current_date1 && $date < $current_date2 ) {
                        $cek = FPMachineLog::where('fp_machine_id', $machine_id)
                            ->where('pin', $pin)
                            ->where('status', $status)
                            ->where('datetime', $datetime)
                            ->count();
                        if ($cek == 0) {
                            FPMachineLog::insert([
                                'fp_machine_id' => $machine_id,
                                'pin' => $pin,
                                'datetime' => $datetime,
                                'verified' => $verified,
                                'status' => $status,
                                'workcode' => $workcode,
                                'date' => $date,
                                'time' => $time,
                            ]);
                        }
                    }
                }

            }else{
//            echo "Connected to $ip:80\n";
                alert()->error('Tidak terhubung', "Mesin {$location} ({$ip}:{$port}) tidak bisa dijangkau. Periksa IP dan port di halaman Fingerprint Machine.");
                return redirect()->back();
            }
        }
        return redirect()->back();
    }
}
