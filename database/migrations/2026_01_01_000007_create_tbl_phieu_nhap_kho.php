<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblPhieuNhapKho', function (Blueprint $table) {
            $table->string('MaPhieuNhap', 10)->primary();
            $table->date('NgayNhap');
            $table->string('GhiChu', 255)->nullable();
            $table->string('TrangThai', 50); // Chờ nhập / Đã nhập / Hoàn tất
            $table->string('MaTaiKhoan', 10);
            $table->string('MaPhieuNhan', 10);

            $table->foreign('MaTaiKhoan')
                  ->references('MaTaiKhoan')
                  ->on('tblTaiKhoan');

            $table->foreign('MaPhieuNhan')
                  ->references('MaPhieuNhan')
                  ->on('tblPhieuNhanHang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblPhieuNhapKho');
    }
};
