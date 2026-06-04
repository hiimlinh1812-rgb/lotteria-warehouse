<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder cho module Tiếp nhận, Đổi trả & Nhập kho
 * Chạy: php artisan db:seed --class=NhapKhoSeeder
 */
class NhapKhoSeeder extends Seeder
{
    public function run(): void
    {
        // ── TaiKhoan ──────────────────────────────────────────────────────
        DB::table('tblTaiKhoan')->insertOrIgnore([
            ['MaTaiKhoan' => 'TK001', 'HoTen' => 'Lý Hoàng Dung',   'MatKhau' => bcrypt('123456'), 'SoDienThoai' => '0901000001', 'VaiTro' => 'Cửa hàng trưởng'],
            ['MaTaiKhoan' => 'TK002', 'HoTen' => 'Lò Thị Hĩnh',     'MatKhau' => bcrypt('123456'), 'SoDienThoai' => '0901000002', 'VaiTro' => 'Quản lý cửa hàng'],
            ['MaTaiKhoan' => 'TK003', 'HoTen' => 'Phạm Thu Thảo',   'MatKhau' => bcrypt('123456'), 'SoDienThoai' => '0901000003', 'VaiTro' => 'Nhân viên'],
        ]);

        // ── NguyenLieu ────────────────────────────────────────────────────
        DB::table('tblNguyenLieu')->insertOrIgnore([
            ['MaNguyenLieu' => 'NL001', 'TenNguyenLieu' => 'Thịt gà',       'DonViTinh' => 'kg',   'NhomHang' => 'Hàng đông', 'SoLuongTonKho' => 120, 'MoTa' => 'Nguyên liệu chế biến gà rán'],
            ['MaNguyenLieu' => 'NL002', 'TenNguyenLieu' => 'Bột chiên',      'DonViTinh' => 'kg',   'NhomHang' => 'Hàng khô',  'SoLuongTonKho' => 80,  'MoTa' => 'Bột phủ chiên giòn'],
            ['MaNguyenLieu' => 'NL003', 'TenNguyenLieu' => 'Dầu ăn',         'DonViTinh' => 'lít',  'NhomHang' => 'Hàng khô',  'SoLuongTonKho' => 150, 'MoTa' => 'Dầu thực vật'],
            ['MaNguyenLieu' => 'NL004', 'TenNguyenLieu' => 'Khoai tây',      'DonViTinh' => 'kg',   'NhomHang' => 'Hàng đông', 'SoLuongTonKho' => 90,  'MoTa' => 'Khoai tây cắt sợi'],
            ['MaNguyenLieu' => 'NL005', 'TenNguyenLieu' => 'Phô mai',        'DonViTinh' => 'kg',   'NhomHang' => 'Hàng đông', 'SoLuongTonKho' => 45,  'MoTa' => 'Phô mai chế biến'],
            ['MaNguyenLieu' => 'NL006', 'TenNguyenLieu' => 'Bánh burger',    'DonViTinh' => 'cái',  'NhomHang' => 'Hàng khô',  'SoLuongTonKho' => 200, 'MoTa' => 'Vỏ bánh burger'],
            ['MaNguyenLieu' => 'NL007', 'TenNguyenLieu' => 'Rau xà lách',   'DonViTinh' => 'kg',   'NhomHang' => 'Hàng đông', 'SoLuongTonKho' => 35,  'MoTa' => 'Rau ăn kèm'],
            ['MaNguyenLieu' => 'NL008', 'TenNguyenLieu' => 'Sốt cay',       'DonViTinh' => 'chai', 'NhomHang' => 'Hàng khô',  'SoLuongTonKho' => 60,  'MoTa' => 'Sốt vị cay'],
        ]);

        // ── DonDatHang (đã được phê duyệt - input cho module này) ────────
        DB::table('tblDonDatHang')->insertOrIgnore([
            ['MaDonDatHang' => 'DDH001', 'NgayDat' => '2026-05-28', 'TrangThai' => 'Chờ nhận hàng', 'GhiChu' => 'Đơn hàng khô tuần 1 tháng 6', 'MaTaiKhoan' => 'TK002'],
            ['MaDonDatHang' => 'DDH002', 'NgayDat' => '2026-05-30', 'TrangThai' => 'Chờ nhận hàng', 'GhiChu' => 'Đơn hàng đông tuần 1 tháng 6', 'MaTaiKhoan' => 'TK002'],
            ['MaDonDatHang' => 'DDH003', 'NgayDat' => '2026-05-25', 'TrangThai' => 'Đã nhập kho',  'GhiChu' => 'Đơn đã hoàn tất',             'MaTaiKhoan' => 'TK002'],
        ]);

        // ── ChiTietDonDatHang ────────────────────────────────────────────
        DB::table('tblChiTietDonDatHang')->insertOrIgnore([
            // DDH001 - hàng khô
            ['MaDonDatHang' => 'DDH001', 'MaNguyenLieu' => 'NL002', 'SoLuongDat' => 50],
            ['MaDonDatHang' => 'DDH001', 'MaNguyenLieu' => 'NL003', 'SoLuongDat' => 30],
            ['MaDonDatHang' => 'DDH001', 'MaNguyenLieu' => 'NL006', 'SoLuongDat' => 200],
            ['MaDonDatHang' => 'DDH001', 'MaNguyenLieu' => 'NL008', 'SoLuongDat' => 20],
            // DDH002 - hàng đông
            ['MaDonDatHang' => 'DDH002', 'MaNguyenLieu' => 'NL001', 'SoLuongDat' => 80],
            ['MaDonDatHang' => 'DDH002', 'MaNguyenLieu' => 'NL004', 'SoLuongDat' => 60],
            ['MaDonDatHang' => 'DDH002', 'MaNguyenLieu' => 'NL005', 'SoLuongDat' => 25],
            ['MaDonDatHang' => 'DDH002', 'MaNguyenLieu' => 'NL007', 'SoLuongDat' => 15],
            // DDH003 - đã xong
            ['MaDonDatHang' => 'DDH003', 'MaNguyenLieu' => 'NL001', 'SoLuongDat' => 50],
            ['MaDonDatHang' => 'DDH003', 'MaNguyenLieu' => 'NL002', 'SoLuongDat' => 40],
        ]);

        // ── PhieuNhanHang ─────────────────────────────────────────────────
        DB::table('tblPhieuNhanHang')->insertOrIgnore([
            // Chờ nhận - nhân viên chưa nhập SL
            ['MaPhieuNhan' => 'PN001', 'NgayNhan' => '2026-06-03', 'TrangThai' => 'Chờ nhận hàng', 'GhiChu' => null, 'MaTaiKhoan' => 'TK003', 'MaDonDatHang' => 'DDH001'],
            // Chờ xử lý - có sai lệch, cần tạo đổi/trả
            ['MaPhieuNhan' => 'PN002', 'NgayNhan' => '2026-06-03', 'TrangThai' => 'Chờ xử lý',     'GhiChu' => 'Phát hiện thiếu thịt gà', 'MaTaiKhoan' => 'TK003', 'MaDonDatHang' => 'DDH002'],
            // Đã nhận - chờ quản lý nhập kho
            ['MaPhieuNhan' => 'PN003', 'NgayNhan' => '2026-05-27', 'TrangThai' => 'Đã nhận hàng',  'GhiChu' => 'Đủ số lượng', 'MaTaiKhoan' => 'TK003', 'MaDonDatHang' => 'DDH003'],
        ]);

        // ── LoHang cho PN002 (có sai lệch - thiếu thịt gà) ───────────────
        DB::table('tblPhieuNhapKho')->insertOrIgnore([]);  // bảng phải tồn tại trước

        DB::table('tblPhieuDoiTra')->insertOrIgnore([]);   // bảng phải tồn tại trước

        DB::table('tblLoHang')->insertOrIgnore([
            // PN002: NL001 nhận được 70 thay vì 80 (thiếu 10)
            ['MaLoHang' => 'LH001', 'NgaySanXuat' => '2026-05-01', 'HanSuDung' => '2026-07-01', 'SoLuongNhap' => 70, 'SoLuongConLai' => 70, 'TrangThai' => 'Còn hạn', 'MaNguyenLieu' => 'NL001', 'MaPhieuNhan' => 'PN002', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
            // PN002: NL004 đủ 60
            ['MaLoHang' => 'LH002', 'NgaySanXuat' => '2026-04-15', 'HanSuDung' => '2026-06-15', 'SoLuongNhap' => 60, 'SoLuongConLai' => 60, 'TrangThai' => 'Cận hạn', 'MaNguyenLieu' => 'NL004', 'MaPhieuNhan' => 'PN002', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
            // PN002: NL005 đủ 25
            ['MaLoHang' => 'LH003', 'NgaySanXuat' => '2026-05-10', 'HanSuDung' => '2026-08-10', 'SoLuongNhap' => 25, 'SoLuongConLai' => 25, 'TrangThai' => 'Còn hạn', 'MaNguyenLieu' => 'NL005', 'MaPhieuNhan' => 'PN002', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
            // PN002: NL007 đủ 15
            ['MaLoHang' => 'LH004', 'NgaySanXuat' => '2026-06-01', 'HanSuDung' => '2026-06-10', 'SoLuongNhap' => 15, 'SoLuongConLai' => 15, 'TrangThai' => 'Cận hạn', 'MaNguyenLieu' => 'NL007', 'MaPhieuNhan' => 'PN002', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
            // PN003: đã nhận đủ DDH003
            ['MaLoHang' => 'LH005', 'NgaySanXuat' => '2026-04-20', 'HanSuDung' => '2026-07-20', 'SoLuongNhap' => 50, 'SoLuongConLai' => 50, 'TrangThai' => 'Còn hạn', 'MaNguyenLieu' => 'NL001', 'MaPhieuNhan' => 'PN003', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
            ['MaLoHang' => 'LH006', 'NgaySanXuat' => '2026-04-20', 'HanSuDung' => '2026-10-20', 'SoLuongNhap' => 40, 'SoLuongConLai' => 40, 'TrangThai' => 'Còn hạn', 'MaNguyenLieu' => 'NL002', 'MaPhieuNhan' => 'PN003', 'MaPhieuDoiTra' => null, 'MaPhieuNhap' => null],
        ]);
    }
}
