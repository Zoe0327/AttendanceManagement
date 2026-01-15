<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩（1勤怠に対して複数）
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getTotalWorkMinutesAttribute()
    {
        // 出勤 or 退勤がない場合は 0
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // 総勤務時間（分）
        $totalMinutes = $start->diffInMinutes($end);

        // 休憩時間合計（分）
        $breakMinutes = $this->breaks
            ->whereNotNull('end_time')
            ->sum(function ($break) {
                return Carbon::parse($break->start_time)
                    ->diffInMinutes(Carbon::parse($break->end_time));
            });
        return max(0, $totalMinutes - $breakMinutes);
    }
    public function getTotalBreakTimeAttribute()
    {
        $minutes = $this->breaks
            ->whereNotNull('end_time')
            ->sum(function ($break) {
                return \Carbon\Carbon::parse($break->start_time)
                    ->diffInMinutes(\Carbon\Carbon::parse($break->end_time));
            });

        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }

    // 表示用（H:i 形式）
    public function getTotalWorkTimeAttribute()
    {
        $minutes = $this->total_work_minutes;

        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }
    /**
     * 修正申請（1勤怠に対して複数）
     */
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function stamps()
    {
        return $this->hasMany(AttendanceStamp::class);
    }
}
