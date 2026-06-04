<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Tạm thời trả về view dashboard.index
        return view('dashboard.index'); 
    }
}