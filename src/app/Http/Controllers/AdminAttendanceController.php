<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request) {
        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : today();
        $dateString = $currentDate->toDateString();
        $users = User::where('role', 'user')
            ->with(['attendances' => function ($query) use ($dateString) {
                $query->where('work_date', $dateString)
                    ->with('breaks');
            }])
            ->get();
        $previousDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();
        return view('admin.attendance.list', compact(
            'users',
            'currentDate',
            'previousDate',
            'nextDate'
        ));
    }

    public function details($id) {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);
        return view('admin.attendance.details', compact('attendance'));
    }
}
