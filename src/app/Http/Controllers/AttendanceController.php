<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AttendanceController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breaks')
            ->first();
        return view('attendance.index', compact('attendance'));
    }
    public function clockIn() {
        $user = Auth::user();
        $today = now()->toDateString();
        #勤怠は1日1回登録可
        $exists = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->exists();
        if ($exists) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '出勤は1日1回までです。');
        }
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now(),
            'status' => Attendance::STATUS_WORKING,
        ]);
        return redirect()
            ->route('attendance.index')
            ->with('success', '出勤しました。');
    }
    public function breakIn() {
        $attendance = $this->getTodayAttendance();
        if (!$attendance) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '出勤していません。');
        }
        if ($attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '休憩に入ることができません。');
        }
        DB::transaction(function () use ($attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
            $attendance->update([
                'status' => Attendance::STATUS_BREAKING,
            ]);
        });
        return redirect()
            ->route('attendance.index')
            ->with('success', '休憩に入りました。');
    }
    public function breakOut() {
        $attendance = $this->getTodayAttendance();
        if (!$attendance) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '出勤していません。');
        }
        if ($attendance->status !== Attendance::STATUS_BREAKING) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '休憩中ではありません。');
        }
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();
        if (!$break) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '開始中の休憩がありません。');
        }
        DB::transaction(function () use ($attendance, $break) {
            $break->update([
                'break_end' => now(),
            ]);
            $attendance->update([
                'status' => Attendance::STATUS_WORKING,
            ]);
        });
        return redirect()
            ->route('attendance.index')
            ->with('success', '休憩から戻りました。');
    }
    public function clockOut() {
        $attendance = $this->getTodayAttendance();
        if (!$attendance) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '出勤していません。');
        }
        if ($attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '退勤できる状態ではありません。');
        }
        if ($attendance->clock_out !== null) {
            return redirect()
                ->route('attendance.index')
                ->with('error', '退勤は1日1回までです。');
        }
        $attendance->update([
            'clock_out' => now(),
            'status' => Attendance::STATUS_FINISHED,
        ]);
        return redirect()
            ->route('attendance.index')
            ->with('success', '退勤しました。');
    }
    private function getTodayAttendance() {
        return Attendance::where('user_id', Auth::id())
            ->where('work_date', now()->toDateString())
            ->first();
    }

    public function list(Request $request) {
        $user = Auth::user();
        // URLに ?month=2026-05 があればその月、なければ今月
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now()->startOfMonth();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // その月の勤怠を取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy('work_date');

        // その月の全日付を作成
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $attendanceList = [];
        foreach ($dates as $date) {
            $dateString = $date->toDateString();
            $attendance = $attendances->get($dateString);
            $attendanceList[] = [
                'date' => $date->copy(),
                'attendance' => $attendance,
            ];
        }

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        return view('attendance.list', compact(
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'attendanceList'
        ));
    }

    public function details($id) {
        $attendance = Attendance::with(['user', 'breaks'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $pendingCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->first();
        return view('attendance.details', compact('attendance', 'pendingCorrection'));
    }
}
