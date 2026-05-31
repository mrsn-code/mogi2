<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function breakCorrections() {
        return $this->hasMany(BreakCorrection::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute() {
        return match ($this->status) {
            self::STATUS_APPROVED => '承認済み',
            self::STATUS_REJECTED => '却下',
            default => '承認待ち',
        };
    }
}
