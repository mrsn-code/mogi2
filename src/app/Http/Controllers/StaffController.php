<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;


class StaffController extends Controller
{
    public function index() {
        $staffs = User::where('role', 'user')
            ->orderBy('id')
            ->get();
        return view('admin.staff.list', compact('staffs'));
    }

    public function attendanceList(Request $request, $id) {
        $user = User::where('role', 'user')->findOrFail($id);
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now()->startOfMonth();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy('work_date');
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $attendanceList = [];
        foreach ($dates as $date) {
            $dateString = $date->toDateString();
            $attendanceList[] = [
                'date' => $date->copy(),
                'attendance' => $attendances->get($dateString),
            ];
        }
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        return view('admin.staff.attendance_list', compact(
            'user',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'attendanceList'
        ));
    }
}
