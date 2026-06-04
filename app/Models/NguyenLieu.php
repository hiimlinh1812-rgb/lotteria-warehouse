<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NguyenLieu extends Model
{
    protected $table = 'nguyenlieu';
    protected $primaryKey = 'MaNguyenLieu';
    public $incrementing = false; // Khóa chính là chuỗi (VARCHAR), không phải số tự tăng
    protected $keyType = 'string';
    public $timestamps = false; // Bỏ qua created_at và updated_at mặc định của Laravel

    protected $fillable = [
        'MaNguyenLieu',
        'TenNguyenLieu',
        'DonViTinh',
        'NhomHang',
        'SoLuongTonKho',
        'MoTa'
    ];
}
