<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * 勤怠（1ユーザー：複数日）
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * 修正申請
     */
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
