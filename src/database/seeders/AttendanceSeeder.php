<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run():void
    {
        //一般ユーザーを複数作成
        User::factory()
            ->count(5)
            ->create()
            ->each(function ($user) {
                collect(range(1, 5))->each(function ($i) use ($user) {
                    Attendance::factory()
                        ->for($user)
                        ->state([
                            'work_date' => now()->subDays($i)->toDateString(),
                        ])
                        ->has(BreakTime::factory()->count(rand(0, 2)), 'breaks')
                        ->create();
                });
            });
    }
}
