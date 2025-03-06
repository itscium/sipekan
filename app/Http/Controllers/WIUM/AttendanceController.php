<?php

namespace App\Http\Controllers\WIUM;

use App\Http\Controllers\Controller;
use App\Models\FPMachine;
use App\Models\FPMachineLog;
use App\Models\FPMachineUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

        public function index(){
        $pin = FPMachineUser::where('user_id', Auth::id())->pluck('pin')->first();
        $attendance = FPMachineLog::where('pin', $pin)->get();
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

//            dd($mergedData);

// You can then process $mergedData or display it as needed
//            dd($mergedData);
        return view('wium.attendance.index', compact('attendance', 'attendanceGrouped', 'mergedData'));
    }
}
