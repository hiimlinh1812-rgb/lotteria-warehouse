<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietPhieuXuat extends Model
{
    protected $table = 'chitietphieuxuat';
    public $timestamps = false;

    // Bảng này dùng khóa chính kép trong DB, Eloquent không hỗ trợ mặc định tốt khóa kép,
    // Nên chúng ta chỉ định fillable để insert dữ liệu an toàn.
    protected $fillable = [
        'MaPhieuXuat',
        'MaLoHang',
        'SoLuongXuat'
    ];
}
