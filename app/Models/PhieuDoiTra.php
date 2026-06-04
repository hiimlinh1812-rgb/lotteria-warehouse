<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuDoiTra extends Model
{
    protected $table = 'tblPhieuDoiTra';
    protected $primaryKey = 'MaPhieuDoiTra';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaPhieuDoiTra', 'NgayTao', 'LoaiXuLy', 'LyDo',
        'TrangThaiXuLy', 'MaTaiKhoan', 'MaPhieuNhan'
    ];

    const LOAI_DOI_HANG = 'Đổi hàng';
    const LOAI_TRA_HANG = 'Trả hàng';
    const TRANG_THAI_DANG_XU_LY = 'Đang xử lý';
    const TRANG_THAI_DA_XU_LY   = 'Đã xử lý';

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function phieuNhanHang()
    {
        return $this->belongsTo(PhieuNhanHang::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }

    public function loHangs()
    {
        return $this->hasMany(LoHang::class, 'MaPhieuDoiTra', 'MaPhieuDoiTra');
    }
}
