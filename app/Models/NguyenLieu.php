<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NguyenLieu extends Model
{
    public $timestamps = false;
    protected $table = 'NguyenLieu';
    protected $primaryKey = 'MaNguyenLieu';
    public $incrementing = false; // Báo cho hệ thống biết mã NL là chuỗi (VD: NL001)
    protected $keyType = 'string';

    // Khai báo các cột được phép nhập dữ liệu
    protected $fillable = [
        'MaNguyenLieu', 
        'TenNguyenLieu', 
        'DonViTinh', 
        'NhomHang', 
        'SoLuongTonKho', 
        'MoTa'
    ];
}