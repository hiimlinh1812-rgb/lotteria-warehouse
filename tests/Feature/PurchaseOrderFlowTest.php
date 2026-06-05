<?php

namespace Tests\Feature;

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseOrderFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('TaiKhoan', function ($table) {
            $table->string('MaTaiKhoan', 10)->primary();
            $table->string('HoTen', 100);
            $table->string('MatKhau', 255);
            $table->string('SoDienThoai', 10)->unique();
            $table->string('VaiTro', 50);
        });

        Schema::create('NguyenLieu', function ($table) {
            $table->string('MaNguyenLieu', 10)->primary();
            $table->string('TenNguyenLieu', 100);
            $table->string('DonViTinh', 20);
            $table->string('NhomHang', 50);
            $table->integer('SoLuongTonKho')->default(0);
            $table->string('MoTa', 255)->nullable();
        });

        Schema::create('DonDatHang', function ($table) {
            $table->string('MaDonDatHang', 10)->primary();
            $table->date('NgayDat');
            $table->string('TrangThai', 50);
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaTaiKhoan', 10);
        });

        Schema::create('ChiTietDonDatHang', function ($table) {
            $table->string('MaDonDatHang', 10);
            $table->string('MaNguyenLieu', 10);
            $table->integer('SoLuongDat');
            $table->primary(['MaDonDatHang', 'MaNguyenLieu']);
        });

        Schema::create('TruyVetDonDatHang', function ($table) {
            $table->id();
            $table->string('MaDonDatHang', 10);
            $table->string('HanhDong', 100);
            $table->string('TrangThaiTruoc', 50)->nullable();
            $table->string('TrangThaiSau', 50)->nullable();
            $table->string('MaTaiKhoan', 10)->nullable();
            $table->string('NoiDung', 255)->nullable();
            $table->timestamps();
        });

        DB::table('TaiKhoan')->insert([
            [
                'MaTaiKhoan' => 'QL001',
                'HoTen' => 'Tran Thi Binh',
                'MatKhau' => 'pw',
                'SoDienThoai' => '0912345678',
                'VaiTro' => 'Quan ly',
            ],
            [
                'MaTaiKhoan' => 'CHT001',
                'HoTen' => 'Nguyen Van An',
                'MatKhau' => 'pw',
                'SoDienThoai' => '0901234567',
                'VaiTro' => 'Cua hang truong',
            ],
            [
                'MaTaiKhoan' => 'NV001',
                'HoTen' => 'Pham Thi Dung',
                'MatKhau' => 'pw',
                'SoDienThoai' => '0934567890',
                'VaiTro' => 'Nhan vien',
            ],
        ]);

        DB::table('NguyenLieu')->insert([
            [
                'MaNguyenLieu' => 'NL001',
                'TenNguyenLieu' => 'Ga sot',
                'DonViTinh' => 'Kg',
                'NhomHang' => 'Dong lanh',
                'SoLuongTonKho' => 20,
                'MoTa' => null,
            ],
            [
                'MaNguyenLieu' => 'NL002',
                'TenNguyenLieu' => 'Pho mai',
                'DonViTinh' => 'Hop',
                'NhomHang' => 'Hang mat',
                'SoLuongTonKho' => 15,
                'MoTa' => null,
            ],
        ]);

        DB::table('DonDatHang')->insert([
            'MaDonDatHang' => 'DDH001',
            'NgayDat' => '2026-06-01',
            'TrangThai' => 'Cho phe duyet',
            'GhiChu' => 'Don ban dau',
            'MaTaiKhoan' => 'NV001',
        ]);

        DB::table('ChiTietDonDatHang')->insert([
            'MaDonDatHang' => 'DDH001',
            'MaNguyenLieu' => 'NL001',
            'SoLuongDat' => 2,
        ]);

        $this->actingAs(TaiKhoan::query()->find('QL001'));
    }

    public function test_index_displays_purchase_order_screen(): void
    {
        $response = $this->get('/purchase-orders');

        $response->assertOk();
        $response->assertSee('DDH001');
        $response->assertSee('/purchase-orders/DDH001/cancel', false);
    }

    public function test_index_can_sort_by_code_and_quantity(): void
    {
        DB::table('DonDatHang')->insert([
            [
                'MaDonDatHang' => 'DDH010',
                'NgayDat' => '2026-06-03',
                'TrangThai' => 'Cho phe duyet',
                'GhiChu' => 'Đơn nhiều mặt hàng',
                'MaTaiKhoan' => 'QL001',
            ],
            [
                'MaDonDatHang' => 'DDH002',
                'NgayDat' => '2026-06-02',
                'TrangThai' => 'Da duyet',
                'GhiChu' => 'Đơn ít mặt hàng',
                'MaTaiKhoan' => 'QL001',
            ],
        ]);

        DB::table('ChiTietDonDatHang')->insert([
            [
                'MaDonDatHang' => 'DDH010',
                'MaNguyenLieu' => 'NL001',
                'SoLuongDat' => 9,
            ],
            [
                'MaDonDatHang' => 'DDH010',
                'MaNguyenLieu' => 'NL002',
                'SoLuongDat' => 5,
            ],
            [
                'MaDonDatHang' => 'DDH002',
                'MaNguyenLieu' => 'NL002',
                'SoLuongDat' => 1,
            ],
        ]);

        $codeResponse = $this->get('/purchase-orders?sort=code&direction=asc');
        $codeResponse->assertOk();
        $codeResponse->assertSeeInOrder(['DDH001', 'DDH002', 'DDH010']);

        $quantityResponse = $this->get('/purchase-orders?sort=quantity&direction=desc');
        $quantityResponse->assertOk();
        $quantityResponse->assertSeeInOrder(['DDH010', 'DDH001', 'DDH002']);
    }

    public function test_store_creates_new_pending_order_and_merges_duplicate_items(): void
    {
        $response = $this->post('/purchase-orders', [
            'NgayDat' => '2026-06-04',
            'MaTaiKhoan' => 'NV001',
            'GhiChu' => 'Tao moi',
            'items' => [
                ['MaNguyenLieu' => 'NL001', 'SoLuongDat' => 2],
                ['MaNguyenLieu' => 'NL001', 'SoLuongDat' => 3],
                ['MaNguyenLieu' => 'NL002', 'SoLuongDat' => 4],
            ],
        ]);

        $response->assertRedirect('/purchase-orders/DDH002');

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH002',
            'TrangThai' => 'Cho phe duyet',
            'MaTaiKhoan' => 'NV001',
        ]);

        $this->assertSame(
            5,
            DB::table('ChiTietDonDatHang')
                ->where('MaDonDatHang', 'DDH002')
                ->where('MaNguyenLieu', 'NL001')
                ->value('SoLuongDat')
        );

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH002',
            'HanhDong' => 'Tạo đơn',
            'TrangThaiSau' => 'Cho phe duyet',
            'MaTaiKhoan' => 'NV001',
        ]);
    }

    public function test_update_replaces_line_items_for_editable_order(): void
    {
        $response = $this->put('/purchase-orders/DDH001', [
            'NgayDat' => '2026-06-05',
            'MaTaiKhoan' => 'QL001',
            'GhiChu' => 'Da cap nhat',
            'items' => [
                ['MaNguyenLieu' => 'NL002', 'SoLuongDat' => 6],
            ],
        ]);

        $response->assertRedirect('/purchase-orders/DDH001');

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'NgayDat' => '2026-06-05',
            'MaTaiKhoan' => 'QL001',
            'GhiChu' => 'Da cap nhat',
        ]);

        $this->assertDatabaseMissing('ChiTietDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'MaNguyenLieu' => 'NL001',
        ]);

        $this->assertDatabaseHas('ChiTietDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'MaNguyenLieu' => 'NL002',
            'SoLuongDat' => 6,
        ]);

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Cập nhật đơn',
            'TrangThaiTruoc' => 'Cho phe duyet',
            'TrangThaiSau' => 'Cho phe duyet',
            'MaTaiKhoan' => 'QL001',
        ]);
    }

    public function test_store_chief_can_approve_pending_order(): void
    {
        $this->actingAs(TaiKhoan::query()->find('CHT001'));

        $this->post('/purchase-orders/DDH001/approve', [
            'MaTaiKhoan' => 'CHT001',
            'GhiChuDuyet' => 'Dong y',
        ])->assertRedirect();

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'TrangThai' => 'Da duyet',
        ]);

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Phê duyệt đơn',
            'TrangThaiTruoc' => 'Cho phe duyet',
            'TrangThaiSau' => 'Da duyet',
            'MaTaiKhoan' => 'CHT001',
        ]);
    }

    public function test_non_store_chief_cannot_approve_order(): void
    {
        $response = $this->post('/purchase-orders/DDH001/approve', [
            'MaTaiKhoan' => 'QL001',
            'GhiChuDuyet' => 'Thu phe duyet',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'TrangThai' => 'Cho phe duyet',
        ]);
    }

    public function test_store_chief_cannot_create_update_cancel_or_stock_order(): void
    {
        $this->actingAs(TaiKhoan::query()->find('CHT001'));

        $this->post('/purchase-orders', [
            'NgayDat' => '2026-06-04',
            'MaTaiKhoan' => 'NV001',
            'GhiChu' => 'Thu tao moi',
            'items' => [
                ['MaNguyenLieu' => 'NL001', 'SoLuongDat' => 2],
            ],
        ])->assertForbidden();

        $this->put('/purchase-orders/DDH001', [
            'NgayDat' => '2026-06-05',
            'MaTaiKhoan' => 'QL001',
            'GhiChu' => 'Thu cap nhat',
            'items' => [
                ['MaNguyenLieu' => 'NL002', 'SoLuongDat' => 3],
            ],
        ])->assertForbidden();

        $this->post('/purchase-orders/DDH001/cancel')->assertForbidden();

        DB::table('DonDatHang')
            ->where('MaDonDatHang', 'DDH001')
            ->update(['TrangThai' => 'Da nhan hang']);

        $this->post('/purchase-orders/DDH001/stock')->assertForbidden();
    }

    public function test_cancel_marks_pending_order_as_cancelled(): void
    {
        $this->post('/purchase-orders/DDH001/cancel')->assertRedirect();

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'TrangThai' => 'Da huy',
        ]);

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Hủy đơn',
            'TrangThaiTruoc' => 'Cho phe duyet',
            'TrangThaiSau' => 'Da huy',
        ]);
    }

    public function test_receive_then_stock_moves_order_to_next_steps(): void
    {
        DB::table('DonDatHang')
            ->where('MaDonDatHang', 'DDH001')
            ->update(['TrangThai' => 'Da duyet']);

        $this->post('/purchase-orders/DDH001/receive')->assertRedirect();

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'TrangThai' => 'Da nhan hang',
        ]);

        $this->post('/purchase-orders/DDH001/stock')->assertRedirect();

        $this->assertDatabaseHas('DonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'TrangThai' => 'Da nhap kho',
        ]);

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Nhận hàng',
            'TrangThaiTruoc' => 'Da duyet',
            'TrangThaiSau' => 'Da nhan hang',
        ]);

        $this->assertDatabaseHas('TruyVetDonDatHang', [
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Nhập kho',
            'TrangThaiTruoc' => 'Da nhan hang',
            'TrangThaiSau' => 'Da nhap kho',
        ]);
    }

    public function test_show_displays_hidden_audit_trail_section(): void
    {
        DB::table('TruyVetDonDatHang')->insert([
            'MaDonDatHang' => 'DDH001',
            'HanhDong' => 'Tạo đơn',
            'TrangThaiTruoc' => null,
            'TrangThaiSau' => 'Cho phe duyet',
            'MaTaiKhoan' => 'NV001',
            'NoiDung' => 'Khởi tạo đơn mua',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/purchase-orders/DDH001');

        $response->assertOk();
        $response->assertSee('Truy vết thao tác', false);
        $response->assertSee('Tạo đơn', false);
    }
}
