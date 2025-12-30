<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CorrectionRequestController extends Controller
{
    public function index()
    {
        return view('admin.request.index');
    }
}
