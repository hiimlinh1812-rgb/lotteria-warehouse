<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

class TaiKhoanSeeder extends Seeder
{
    public function run(): void
    {
        TaiKhoan::create([
            'MaTaiKhoan' => 'TK001',
            'HoTen' => 'Lý Hoàng Dung',
            'MatKhau' => Hash::make('123456'),
            'SoDienThoai' => '0987654321',
            'VaiTro' => 'Cửa hàng trưởng',
        ]);
    }
}