<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblPhieuNhanHang', function (Blueprint $table) {
            $table->string('MaPhieuNhan', 10)->primary();
            $table->date('NgayNhan');
            $table->string('TrangThai', 50);
            // Chờ nhận hàng / Đã nhận hàng / Chờ xử lý / Đang xử lý đổi/trả / Hoàn tất
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaTaiKhoan', 10);
            $table->string('MaDonDatHang', 10);

            $table->foreign('MaTaiKhoan')
                  ->references('MaTaiKhoan')
                  ->on('tblTaiKhoan');

            $table->foreign('MaDonDatHang')
                  ->references('MaDonDatHang')
                  ->on('tblDonDatHang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblPhieuNhanHang');
    }
};
