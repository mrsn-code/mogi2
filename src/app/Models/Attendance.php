<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    #ステータス
    public const STATUS_OFF = 'off';
    public const STATUS_WORKING = 'working';
    public const STATUS_BREAKING = 'breaking';
    public const STATUS_FINISHED = 'finished';

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function breaks() {
        return $this->hasMany(BreakTime::class);
    }
    
    public function getStatusLabelAttribute() {
        return match ($this->status) {
            self::STATUS_WORKING => '出勤中',
            self::STATUS_BREAKING => '休憩中',
            self::STATUS_FINISHED => '退勤済',
            default => '勤務外',
        };
    }

    public function getTotalBreakSecondsAttribute() {
        return $this->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) {
                return 0;
            }

            return Carbon::parse($break->break_start)
                ->diffInSeconds(Carbon::parse($break->break_end));
        });
    }

    public function getTotalWorkSecondsAttribute() {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $totalSeconds = Carbon::parse($this->clock_in)
            ->diffInSeconds(Carbon::parse($this->clock_out));

        return $totalSeconds - $this->total_break_seconds;
    }

    public function formatSecondsToTime($seconds) {
        if ($seconds <= 0) {
            return '';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getTotalBreakTimeAttribute() {
        return $this->formatSecondsToTime($this->total_break_seconds);
    }

    public function getTotalWorkTimeAttribute() {
        return $this->formatSecondsToTime($this->total_work_seconds);
    }

    public function corrections() {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
