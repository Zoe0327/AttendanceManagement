<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('admin.auth.login');
    }

    //管理者ログイン処理
    public function authenticate(AdminLoginRequest $request)
    {

        if (Auth::guard('admin')->attempt($request->only('email', 'password')
            )
        ) {
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' =>'ログイン情報が登録されていません',
        ])->withInput();
    }

    //管理者ログアウト
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

}
