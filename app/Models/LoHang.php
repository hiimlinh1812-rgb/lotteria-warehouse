<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LoHang extends Model
{
    protected static ?string $resolvedTable = null;

    protected $table = 'LoHang';
    protected $primaryKey = 'MaLoHang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [

        'MaLoHang', 'NgaySanXuat', 'HanSuDung', 'SoLuongNhap',
        'SoLuongConLai', 'TrangThai', 'MaNguyenLieu',
        'MaPhieuNhan', 'MaPhieuDoiTra', 'MaPhieuNhap'
    ];

    public function nguyenLieu()
    {
        return $this->belongsTo(NguyenLieu::class, 'MaNguyenLieu', 'MaNguyenLieu');
    }

    public function phieuNhanHang()
    {
        return $this->belongsTo(PhieuNhanHang::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }

    public function phieuNhapKho()
    {
        return $this->belongsTo(PhieuNhapKho::class, 'MaPhieuNhap', 'MaPhieuNhap');
    }

    public function phieuDoiTra()
    {
        return $this->belongsTo(PhieuDoiTra::class, 'MaPhieuDoiTra', 'MaPhieuDoiTra');
    }

    public function getTable()
    {
        if (static::$resolvedTable !== null) {
            return static::$resolvedTable;
        }

        foreach (['LoHang', 'tblLoHang', 'lo_hang', 'LoHang'] as $candidate) {
            if (Schema::hasTable($candidate)) {
                return static::$resolvedTable = $candidate;
            }
        }

        return $this->table;
    }

}
