<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AttendanceRequestController extends Controller
{
    public function store(Request $request, $id) {
        $attendance = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $request->validate([
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:1000'],
        ], [
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_out.required' => '退勤時間を入力してください。',
            'note.required' => '備考を記入してください。',
        ]);

        if ($request->clock_out <= $request->clock_in) {
            return back()
                ->withErrors(['clock_out' => '退勤時間は出勤時間より後にしてください。'])
                ->withInput();
        }

        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                if (
                    !empty($breakData['break_start']) &&
                    !empty($breakData['break_end']) &&
                    $breakData['break_end'] <= $breakData['break_start']
                ) {

                    return back()
                        ->withErrors(['breaks' => '休憩戻り時間は休憩入り時間より後にしてください。'])
                        ->withInput();
                }
            }
        }

        $alreadyPending = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->exists();
        if ($alreadyPending) {
            return back()
                ->withErrors(['request' => 'この勤怠にはすでに承認待ちの修正申請があります。'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->toDateString();
            $correctionRequest = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'user_id' => Auth::id(),
                'requested_clock_in' => Carbon::parse($workDate . ' ' . $request->clock_in),
                'requested_clock_out' => Carbon::parse($workDate . ' ' . $request->clock_out),
                'note' => $request->note,
                'status' => AttendanceCorrection::STATUS_PENDING,
            ]);

            if ($request->has('breaks')) {
                foreach ($request->breaks as $breakId => $breakData) {
                    $break = BreakTime::where('attendance_id', $attendance->id)
                        ->where('id', $breakId)
                        ->first();
                    if (!$break) {
                        continue;
                    }

                    BreakCorrection::create([
                        'attendance_correction_id' => $correctionRequest->id,
                        'break_time_id' => $break->id,
                        'requested_break_start' => !empty($breakData['break_start'])
                            ? Carbon::parse($workDate . ' ' . $breakData['break_start'])
                            : null,
                        'requested_break_end' => !empty($breakData['break_end'])
                            ? Carbon::parse($workDate . ' ' . $breakData['break_end'])
                            : null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('attendance.request.index', ['status' => AttendanceCorrection::STATUS_PENDING])
            ->with('success', '修正申請を送信しました。');
    }

    public function index(Request $request) {
        $status = $request->input('status', AttendanceCorrection::STATUS_PENDING);
        $query = AttendanceCorrection::with([
            'user',
            'attendance',
            'breakCorrections',
        ])
            ->where('status', $status)
            ->latest();
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }
        $requests = $query->get();
        if (Auth::user()->isAdmin()) {
            return view('admin.requests.index', compact('requests', 'status'));
        }
        return view('attendance.requests', compact('requests', 'status'));
    }
    public function show($id) {
        $requestItem = AttendanceCorrection::with([
            'user',
            'attendance',
            'breakCorrections.breakTime',
        ])->findOrFail($id);
        if (!Auth::user()->isAdmin() && $requestItem->user_id !== Auth::id()) {
            abort(403);
        }
        if (Auth::user()->isAdmin()) {
            return view('admin.requests.show', compact('requestItem'));
        }
        return view('attendance.request_show', compact('requestItem'));
    }

    public function approve($id) {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $requestItem = AttendanceCorrection::with([
                'attendance',
                'breakCorrections.breakTime',
            ])
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->findOrFail($id);
        DB::transaction(function () use ($requestItem) {
            $attendance = $requestItem->attendance;
            $attendance->update([
                'clock_in' => $requestItem->requested_clock_in,
                'clock_out' => $requestItem->requested_clock_out,
                'note' => $requestItem->note,
            ]);
            foreach ($requestItem->breakCorrections as $breakCorrection) {
                if ($breakCorrection->breakTime) {
                    $breakCorrection->breakTime->update([
                        'break_start' => $breakCorrection->requested_break_start,
                        'break_end' => $breakCorrection->requested_break_end,
                    ]);
                }
            }
            $requestItem->update([
                'status' => AttendanceCorrection::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
        return redirect()
            ->route('attendance.request.show', ['id' => $requestItem->id])
            ->with('success', '修正申請を承認しました。');
    }
}
