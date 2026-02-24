<?php

namespace App\Models;
use Carbon\Carbon;
use App\Models\CorrectionRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_FIXED   = 1;   // 確定
    const STATUS_ADMIN   = 2;   // 管理者修正

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'remark',
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

    //休憩（1勤怠に対して複数）
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    //修正申請中か
    public function hasPendingCorrection(): bool
    {
        return $this->correctionRequests()
            ->where('status', '0')
            ->exists();
    }

    // 勤務時間（分）
    public function getTotalWorkMinutesAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $totalMinutes = $this->start_time->diffInMinutes($this->end_time);

        $breakMinutes = $this->breaks
            ->whereNotNull('end_time')
            ->sum(
                fn($break) =>
                Carbon::parse($break->start_time)
                    ->diffInMinutes(Carbon::parse($break->end_time))
            );

        return max(0, $totalMinutes - $breakMinutes);
    }

    // 勤務時間（H:i 表示）
    public function getTotalWorkTimeAttribute()
    {
        $minutes = $this->total_work_minutes;

        if (is_null($minutes)) {
            return null;
        }
        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }

    // 休憩時間（H:i 表示）
    public function getTotalBreakTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        
        $minutes = $this->breaks
            ->whereNotNull('end_time')
            ->sum(
                fn($break) =>
                Carbon::parse($break->start_time)
                    ->diffInMinutes(Carbon::parse($break->end_time))
            );

        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }

}