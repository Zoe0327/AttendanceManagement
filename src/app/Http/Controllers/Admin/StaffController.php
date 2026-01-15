<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('admin.attendance.staff', [
            'users' => $users,
        ]);
    }
}
