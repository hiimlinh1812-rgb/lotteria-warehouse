<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nguyen_lieus', function (Blueprint $table) {
        $table->string('MaNguyenLieu', 10)->primary(); 
        $table->string('TenNguyenLieu', 100); 
        $table->string('DonViTinh', 20); 
        $table->string('NhomHang', 50); 
        $table->integer('SoLuongTonKho')->default(0); 
        $table->string('MoTa', 255)->nullable(); 
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguyen_lieus');
    }
};
