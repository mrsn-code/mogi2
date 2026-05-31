<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrection extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_correction_id',
        'break_time_id',
        'requested_break_start',
        'requested_break_end',
    ];

    public function attendanceCorrection() {
        return $this->belongsTo(AttendanceCorrection::class);
    }

    public function breakTime() {
        return $this->belongsTo(BreakTime::class);
    }
}
