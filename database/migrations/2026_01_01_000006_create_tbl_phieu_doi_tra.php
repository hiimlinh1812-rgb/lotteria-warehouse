<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblPhieuDoiTra', function (Blueprint $table) {
            $table->string('MaPhieuDoiTra', 10)->primary();
            $table->date('NgayTao');
            $table->string('LoaiXuLy', 50);   // Đổi hàng / Trả hàng
            $table->string('LyDo', 255);
            $table->string('TrangThaiXuLy', 50); // Đang xử lý / Đã xử lý
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
        Schema::dropIfExists('tblPhieuDoiTra');
    }
};
