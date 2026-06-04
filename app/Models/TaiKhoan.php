<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// app/Models/TaiKhoan.php

class TaiKhoan extends Authenticatable
{
    use Notifiable;

    protected $table = 'TaiKhoan';
    protected $primaryKey = 'MaTaiKhoan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['MaTaiKhoan', 'HoTen', 'MatKhau', 'SoDienThoai', 'VaiTro'];

    public function getAuthPassword()
    {
        return $this->MatKhau;
    }
    public $timestamps = false;
}
