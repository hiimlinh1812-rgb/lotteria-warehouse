<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhieuXuatController extends Controller
{
    public function index() 
    {
        return view('dang-trien-khai');
    }
}
