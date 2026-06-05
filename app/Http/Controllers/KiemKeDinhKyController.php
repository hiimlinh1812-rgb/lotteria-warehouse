<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KiemKeDinhKyController extends Controller
{
    public function index() 
    {
        return view('kiem_ke_bep');
    }
}
