<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KiemKeDinhKyController extends Controller
{
    public function index() 
    {
        return view('dang-trien-khai');
    }
}
