<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class XuatKhoController extends Controller
{
    public function index() 
    {
        return view('dang-trien-khai');
    }
}
