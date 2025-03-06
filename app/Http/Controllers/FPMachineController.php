<?php

namespace App\Http\Controllers;

use App\Models\FPMachine;
use App\Models\FPMachineLog;
use App\Models\FPMachineUser;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function index(){
        $list_machine = FPMachine::all();
        return view('master-data.wilayah.wium.fingerprint.index', compact('list_machine'));
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

            $connect = fsockopen($ip, $port, $errno, $errstr, 30);
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
                dd("Connection Failed");
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
        $current_date = date('Y-m-d');
//        $current_date = '2025-02-24';
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

            $connect = fsockopen($ip, $port, $errno, $errstr, 30);
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

                    if ($date == $current_date ) {
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
                dd("Connection Failed");
            }
        }
        return redirect()->back();
    }
}
