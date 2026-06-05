<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblDonDatHang', function (Blueprint $table) {
            $table->string('MaDonDatHang', 10)->primary();
            $table->date('NgayDat');
            $table->string('TrangThai', 50);
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaTaiKhoan', 10);

            $table->foreign('MaTaiKhoan')
                  ->references('MaTaiKhoan')
                  ->on('tblTaiKhoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblDonDatHang');
    }
};
