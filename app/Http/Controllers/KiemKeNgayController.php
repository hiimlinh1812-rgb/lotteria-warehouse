<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class KiemKeNgayController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('kiemke.bep');
    }
}
