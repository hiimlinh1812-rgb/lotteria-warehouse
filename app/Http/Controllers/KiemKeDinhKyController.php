<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class KiemKeDinhKyController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('khochinh.kiemke');
    }
}
