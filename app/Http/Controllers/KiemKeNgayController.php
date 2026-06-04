<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KiemKeNgayController extends Controller
{
    public function index() 
    {
        return view('dang-trien-khai');
    }
}
