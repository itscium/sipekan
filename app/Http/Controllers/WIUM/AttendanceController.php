<?php

namespace App\Http\Controllers\WIUM;

use App\Http\Controllers\Controller;
use App\Models\FPMachine;
use App\Models\FPMachineLog;
use App\Models\FPMachineUser;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

        public function index(){
            function isValidDate($date) {
                $d = DateTime::createFromFormat('Y-m-d', $date);
                return $d && $d->format('Y-m-d') === $date;
            }

// Default date range if no parameters are set or invalid
            if (isset($_GET['tgl_awal']) && isset($_GET['tgl_akhir'])) {
                $tgl_awal = $_GET['tgl_awal'];
                $tgl_akhir = $_GET['tgl_akhir'];

                // Validate both dates
                if (!isValidDate($tgl_awal) || !isValidDate($tgl_akhir)) {
                    alert()->warning('Invalid date format');
                    return back();
                }

                // Ensure tgl_awal is not after tgl_akhir
                if ($tgl_awal > $tgl_akhir) {
                    alert()->warning('The starting date cannot be later than the ending date.');
                    return back();
                }

            } else {
                // Default range: current week's Monday to Friday
                $tgl_awal = date('Y-m-d', strtotime('monday this week'));  // Monday of the current week
                $tgl_akhir = date('Y-m-d', strtotime('friday this week'));  // Friday of the current week
            }

        $pin = FPMachineUser::where('user_id', Auth::id())->pluck('pin')->first();
        $attendance = FPMachineLog::where('pin', $pin)->whereBetween('date', [$tgl_awal, $tgl_akhir])->orderBy('date')->get();
            $attendanceGrouped = $attendance->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->date)->toDateString(); // Groups by date (YYYY-MM-DD)
            });
            $mergedData = [];

            foreach ($attendanceGrouped as $date => $logs) {
                $dayOfWeek = date('N', strtotime($date));
                // Get the earliest time with status == 0 (time in)
                $timeIn = $logs->where('status', 0)->min('time');
                // Get the latest time with status == 1 (time out)
                $timeOut = $logs->where('status', 1)->max('time');
                if ($timeIn !== null) {
                    $instatus = (strtotime($timeIn) > strtotime('07:30:59')) ? 'late' : 'not late';
                } else {
                    $instatus = 'Not Available';
                }

                if ($timeOut !== null) {
                    if ($dayOfWeek == 5 && strtotime($timeOut) < strtotime('13:00:00')) {
                        $outstatus = 'early (Friday)';
                    } elseif (strtotime($timeOut) < strtotime('16:30:00') && $dayOfWeek != 5) {
                        $outstatus = 'early';
                    } else {
                        $outstatus = 'not early';
                    }
                } else {
                    $outstatus = 'Not Available';
                }

                $mergedData[] = [
                    'date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'instatus' => $instatus,
                    'outstatus' => $outstatus,
                ];
            }

        return view('wium.attendance.index', compact( 'mergedData', 'tgl_awal', 'tgl_akhir'));
    }
}
