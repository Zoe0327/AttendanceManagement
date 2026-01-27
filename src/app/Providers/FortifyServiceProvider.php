<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            CreatesNewUsers::class,
            CreateNewUser::class
        );
    }

    public function boot(): void
    {
        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');

        });

        // ログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });


        // メール認証案内
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::authenticateUsing(function (Request $request) {
            if (
                Auth::attempt(
                    $request->only('email', 'password'),
                    $request->boolean('remember')
                )
            ) {
                return Auth::user();
            }

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
