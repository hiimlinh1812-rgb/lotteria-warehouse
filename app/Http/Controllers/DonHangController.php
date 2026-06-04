<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonHangController extends Controller
{
    // Hàm index phải nằm TRONG cặp dấu ngoặc nhọn này
    public function index() 
    {
        return view('dang-trien-khai');
    }
}