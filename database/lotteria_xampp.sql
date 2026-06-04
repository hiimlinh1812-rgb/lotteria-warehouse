SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS Lotteria
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE Lotteria;

DROP TABLE IF EXISTS ChiTietPhieuKiemKeCuoiNgay;
DROP TABLE IF EXISTS ChiTietPhieuKiemKeDinhKy;
DROP TABLE IF EXISTS ChiTietPhieuHuy;
DROP TABLE IF EXISTS ChiTietPhieuXuat;
DROP TABLE IF EXISTS ChiTietDonDatHang;
DROP TABLE IF EXISTS PhieuGiaiTrinh;
DROP TABLE IF EXISTS PhieuXuatHuy;
DROP TABLE IF EXISTS PhieuKiemKe;
DROP TABLE IF EXISTS PhieuXuatKho;
DROP TABLE IF EXISTS LoHang;
DROP TABLE IF EXISTS PhieuNhapKho;
DROP TABLE IF EXISTS PhieuDoiTra;
DROP TABLE IF EXISTS PhieuNhanHang;
DROP TABLE IF EXISTS DonDatHang;
DROP TABLE IF EXISTS NguyenLieu;
DROP TABLE IF EXISTS TaiKhoan;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE TaiKhoan (
    MaTaiKhoan VARCHAR(10) NOT NULL,
    HoTen VARCHAR(100) NOT NULL,
    MatKhau VARCHAR(255) NOT NULL,
    SoDienThoai VARCHAR(10) NOT NULL,
    VaiTro VARCHAR(50) NOT NULL,
    PRIMARY KEY (MaTaiKhoan),
    UNIQUE KEY uq_taikhoan_sodienthoai (SoDienThoai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE NguyenLieu (
    MaNguyenLieu VARCHAR(10) NOT NULL,
    TenNguyenLieu VARCHAR(100) NOT NULL,
    DonViTinh VARCHAR(20) NOT NULL,
    NhomHang VARCHAR(50) NOT NULL,
    SoLuongTonKho INT NOT NULL DEFAULT 0,
    MoTa VARCHAR(255) NULL,
    PRIMARY KEY (MaNguyenLieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE DonDatHang (
    MaDonDatHang VARCHAR(10) NOT NULL,
    NgayDat DATE NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    GhiChu VARCHAR(255) NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaDonDatHang),
    CONSTRAINT fk_dondathang_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuNhanHang (
    MaPhieuNhan VARCHAR(10) NOT NULL,
    NgayNhan DATE NOT NULL,
    GhiChu VARCHAR(255) NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    MaDonDatHang VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuNhan),
    CONSTRAINT fk_phieunhanhang_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan),
    CONSTRAINT fk_phieunhanhang_dondathang
        FOREIGN KEY (MaDonDatHang) REFERENCES DonDatHang(MaDonDatHang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuDoiTra (
    MaPhieuDoiTra VARCHAR(10) NOT NULL,
    NgayTao DATE NOT NULL,
    LoaiXuLy VARCHAR(50) NOT NULL,
    LyDo VARCHAR(255) NOT NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    MaPhieuNhan VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuDoiTra),
    CONSTRAINT fk_phieudoitra_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan),
    CONSTRAINT fk_phieudoitra_phieunhanhang
        FOREIGN KEY (MaPhieuNhan) REFERENCES PhieuNhanHang(MaPhieuNhan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuNhapKho (
    MaPhieuNhap VARCHAR(10) NOT NULL,
    NgayNhap DATE NOT NULL,
    GhiChu VARCHAR(255) NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    MaPhieuNhan VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuNhap),
    CONSTRAINT fk_phieunhapkho_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan),
    CONSTRAINT fk_phieunhapkho_phieunhanhang
        FOREIGN KEY (MaPhieuNhan) REFERENCES PhieuNhanHang(MaPhieuNhan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE LoHang (
    MaLoHang VARCHAR(10) NOT NULL,
    NgaySanXuat DATE NOT NULL,
    HanSuDung DATE NOT NULL,
    SoLuongNhap INT NOT NULL,
    SoLuongConLai INT NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    MaNguyenLieu VARCHAR(10) NOT NULL,
    MaPhieuNhan VARCHAR(10) NULL,
    MaPhieuDoiTra VARCHAR(10) NULL,
    MaPhieuNhap VARCHAR(10) NULL,
    PRIMARY KEY (MaLoHang),
    CONSTRAINT fk_lohang_nguyenlieu
        FOREIGN KEY (MaNguyenLieu) REFERENCES NguyenLieu(MaNguyenLieu),
    CONSTRAINT fk_lohang_phieunhanhang
        FOREIGN KEY (MaPhieuNhan) REFERENCES PhieuNhanHang(MaPhieuNhan),
    CONSTRAINT fk_lohang_phieudoitra
        FOREIGN KEY (MaPhieuDoiTra) REFERENCES PhieuDoiTra(MaPhieuDoiTra),
    CONSTRAINT fk_lohang_phieunhapkho
        FOREIGN KEY (MaPhieuNhap) REFERENCES PhieuNhapKho(MaPhieuNhap)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuXuatKho (
    MaPhieuXuat VARCHAR(10) NOT NULL,
    NgayXuat DATE NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuXuat),
    CONSTRAINT fk_phieuxuatkho_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuKiemKe (
    MaPhieuKiemKe VARCHAR(10) NOT NULL,
    NgayKiemKe DATE NOT NULL,
    LoaiKiemKe VARCHAR(50) NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    GhiChu VARCHAR(255) NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuKiemKe),
    CONSTRAINT fk_phieukiemke_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuXuatHuy (
    MaPhieuHuy VARCHAR(10) NOT NULL,
    NgayTao DATE NOT NULL,
    LyDoHuy VARCHAR(255) NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    MaPhieuKiemKe VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuHuy),
    CONSTRAINT fk_phieuxuathuy_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan),
    CONSTRAINT fk_phieuxuathuy_phieukiemke
        FOREIGN KEY (MaPhieuKiemKe) REFERENCES PhieuKiemKe(MaPhieuKiemKe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PhieuGiaiTrinh (
    MaPhieuGiaiTrinh VARCHAR(10) NOT NULL,
    NgayTao DATE NOT NULL,
    NoiDung VARCHAR(255) NOT NULL,
    NguyenNhan VARCHAR(255) NOT NULL,
    MaTaiKhoan VARCHAR(10) NOT NULL,
    MaPhieuKiemKe VARCHAR(10) NOT NULL,
    PRIMARY KEY (MaPhieuGiaiTrinh),
    CONSTRAINT fk_phieugiaitrinh_taikhoan
        FOREIGN KEY (MaTaiKhoan) REFERENCES TaiKhoan(MaTaiKhoan),
    CONSTRAINT fk_phieugiaitrinh_phieukiemke
        FOREIGN KEY (MaPhieuKiemKe) REFERENCES PhieuKiemKe(MaPhieuKiemKe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ChiTietDonDatHang (
    MaDonDatHang VARCHAR(10) NOT NULL,
    MaNguyenLieu VARCHAR(10) NOT NULL,
    SoLuongDat INT NOT NULL,
    PRIMARY KEY (MaDonDatHang, MaNguyenLieu),
    CONSTRAINT fk_ctdondathang_dondathang
        FOREIGN KEY (MaDonDatHang) REFERENCES DonDatHang(MaDonDatHang),
    CONSTRAINT fk_ctdondathang_nguyenlieu
        FOREIGN KEY (MaNguyenLieu) REFERENCES NguyenLieu(MaNguyenLieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ChiTietPhieuXuat (
    MaPhieuXuat VARCHAR(10) NOT NULL,
    MaLoHang VARCHAR(10) NOT NULL,
    SoLuongXuat INT NOT NULL,
    PRIMARY KEY (MaPhieuXuat, MaLoHang),
    CONSTRAINT fk_ctphieuxuat_phieuxuatkho
        FOREIGN KEY (MaPhieuXuat) REFERENCES PhieuXuatKho(MaPhieuXuat),
    CONSTRAINT fk_ctphieuxuat_lohang
        FOREIGN KEY (MaLoHang) REFERENCES LoHang(MaLoHang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ChiTietPhieuHuy (
    MaPhieuHuy VARCHAR(10) NOT NULL,
    MaNguyenLieu VARCHAR(10) NOT NULL,
    SoLuongHuy INT NOT NULL,
    PRIMARY KEY (MaPhieuHuy, MaNguyenLieu),
    CONSTRAINT fk_ctphieuhuy_phieuxuathuy
        FOREIGN KEY (MaPhieuHuy) REFERENCES PhieuXuatHuy(MaPhieuHuy),
    CONSTRAINT fk_ctphieuhuy_nguyenlieu
        FOREIGN KEY (MaNguyenLieu) REFERENCES NguyenLieu(MaNguyenLieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ChiTietPhieuKiemKeDinhKy (
    MaPhieuKiemKe VARCHAR(10) NOT NULL,
    MaLoHang VARCHAR(10) NOT NULL,
    SoLuongHeThong INT NOT NULL,
    SoLuongThucTe INT NOT NULL,
    ChenhLech INT NOT NULL,
    TinhTrang VARCHAR(50) NOT NULL,
    PRIMARY KEY (MaPhieuKiemKe, MaLoHang),
    CONSTRAINT fk_ctkkdinhky_phieukiemke
        FOREIGN KEY (MaPhieuKiemKe) REFERENCES PhieuKiemKe(MaPhieuKiemKe),
    CONSTRAINT fk_ctkkdinhky_lohang
        FOREIGN KEY (MaLoHang) REFERENCES LoHang(MaLoHang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ChiTietPhieuKiemKeCuoiNgay (
    MaPhieuKiemKe VARCHAR(10) NOT NULL,
    MaNguyenLieu VARCHAR(10) NOT NULL,
    SoLuongHeThong INT NOT NULL,
    SoLuongThucTe INT NOT NULL,
    ChenhLech INT NOT NULL,
    TinhTrang VARCHAR(50) NOT NULL,
    PRIMARY KEY (MaPhieuKiemKe, MaNguyenLieu),
    CONSTRAINT fk_ctkkcuoingay_phieukiemke
        FOREIGN KEY (MaPhieuKiemKe) REFERENCES PhieuKiemKe(MaPhieuKiemKe),
    CONSTRAINT fk_ctkkcuoingay_nguyenlieu
        FOREIGN KEY (MaNguyenLieu) REFERENCES NguyenLieu(MaNguyenLieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO TaiKhoan (MaTaiKhoan, HoTen, MatKhau, SoDienThoai, VaiTro) VALUES
    ('CHT001', 'Nguyen Van An', 'hashed_pw_1', '0901234567', 'Cua hang truong'),
    ('QL001', 'Tran Thi Binh', 'hashed_pw_2', '0912345678', 'Quan ly'),
    ('QL002', 'Le Van Cuong', 'hashed_pw_3', '0923456789', 'Quan ly'),
    ('NV001', 'Pham Thi Dung', 'hashed_pw_4', '0934567890', 'Nhan vien'),
    ('NV002', 'Hoang Van Em', 'hashed_pw_5', '0945678901', 'Nhan vien'),
    ('NV003', 'Do Thi Phuong', 'hashed_pw_6', '0956789012', 'Nhan vien');

INSERT INTO NguyenLieu (MaNguyenLieu, TenNguyenLieu, DonViTinh, NhomHang, SoLuongTonKho, MoTa) VALUES
    ('NL001', 'Thit bo patty', 'Kg', 'Hang dong', 50, 'Thit bo xay dong lanh'),
    ('NL002', 'Thit ga fillet', 'Kg', 'Hang dong', 40, 'Uc ga phi le dong lanh'),
    ('NL003', 'Banh mi hamburger', 'Cai', 'Hang kho', 200, 'Banh mi tron'),
    ('NL004', 'Khoai tay chien', 'Kg', 'Hang dong', 80, 'Khoai tay cat soi dong lanh'),
    ('NL005', 'Bo sot mayonnaise', 'Lo', 'Hang kho', 30, 'Sot mayonnaise dong lo 500g'),
    ('NL006', 'Pho mai slice', 'Goi', 'Hang dong', 60, 'Pho mai lat dong goi'),
    ('NL007', 'Xa lach', 'Kg', 'Hang dong', 20, 'Xa lach tuoi dong lanh'),
    ('NL008', 'Muoi', 'Kg', 'Hang kho', 15, NULL);

INSERT INTO DonDatHang (MaDonDatHang, NgayDat, TrangThai, GhiChu, MaTaiKhoan) VALUES
    ('DDH001', '2026-05-01', 'Da nhap kho', NULL, 'QL001'),
    ('DDH002', '2026-05-10', 'Da nhan hang', 'Giao sang', 'QL001'),
    ('DDH003', '2026-05-20', 'Cho phe duyet', 'Don khan', 'QL002');

INSERT INTO ChiTietDonDatHang (MaDonDatHang, MaNguyenLieu, SoLuongDat) VALUES
    ('DDH001', 'NL001', 20),
    ('DDH001', 'NL003', 100),
    ('DDH001', 'NL004', 30),
    ('DDH002', 'NL002', 15),
    ('DDH002', 'NL006', 25),
    ('DDH003', 'NL005', 10),
    ('DDH003', 'NL008', 5);

INSERT INTO PhieuNhanHang (MaPhieuNhan, NgayNhan, GhiChu, MaTaiKhoan, MaDonDatHang) VALUES
    ('PN001', '2026-05-03', NULL, 'NV001', 'DDH001'),
    ('PN002', '2026-05-12', 'Thieu 2 kg', 'NV002', 'DDH002');

INSERT INTO PhieuDoiTra (MaPhieuDoiTra, NgayTao, LoaiXuLy, LyDo, MaTaiKhoan, MaPhieuNhan) VALUES
    ('PDT001', '2026-05-04', 'Doi hang', 'Hang bi hong', 'NV001', 'PN001'),
    ('PDT002', '2026-05-13', 'Tra hang', 'Giao thieu so luong', 'NV002', 'PN002');

INSERT INTO PhieuNhapKho (MaPhieuNhap, NgayNhap, GhiChu, MaTaiKhoan, MaPhieuNhan) VALUES
    ('PNK001', '2026-05-04', NULL, 'NV001', 'PN001'),
    ('PNK002', '2026-05-13', 'Nhap bu', 'NV002', 'PN002');

INSERT INTO LoHang (MaLoHang, NgaySanXuat, HanSuDung, SoLuongNhap, SoLuongConLai, TrangThai, MaNguyenLieu, MaPhieuNhan, MaPhieuDoiTra, MaPhieuNhap) VALUES
    ('LH001', '2026-04-01', '2026-07-01', 20, 18, 'Con han', 'NL001', 'PN001', NULL, 'PNK001'),
    ('LH002', '2026-03-15', '2026-06-15', 100, 95, 'Sap het han', 'NL003', 'PN001', NULL, 'PNK001'),
    ('LH003', '2026-04-10', '2026-06-10', 30, 28, 'Sap het han', 'NL004', 'PN001', NULL, 'PNK001'),
    ('LH004', '2026-05-01', '2026-08-01', 15, 13, 'Con han', 'NL002', 'PN002', NULL, 'PNK002'),
    ('LH005', '2026-02-01', '2026-05-30', 25, 0, 'Het han', 'NL006', 'PN002', 'PDT002', 'PNK002');

INSERT INTO PhieuXuatKho (MaPhieuXuat, NgayXuat, TrangThai, MaTaiKhoan) VALUES
    ('PX001', '2026-05-05', 'Hoan tat', 'NV001'),
    ('PX002', '2026-05-15', 'Cho xuat', 'NV003');

INSERT INTO ChiTietPhieuXuat (MaPhieuXuat, MaLoHang, SoLuongXuat) VALUES
    ('PX001', 'LH001', 2),
    ('PX001', 'LH002', 5),
    ('PX002', 'LH003', 2);

INSERT INTO PhieuKiemKe (MaPhieuKiemKe, NgayKiemKe, LoaiKiemKe, TrangThai, GhiChu, MaTaiKhoan) VALUES
    ('PKK001', '2026-05-06', 'Cuoi ngay', 'Da duyet', NULL, 'QL001'),
    ('PKK002', '2026-05-20', 'Cuoi ky', 'Cho duyet', 'Kiem ky T5', 'QL002');

INSERT INTO ChiTietPhieuKiemKeCuoiNgay (MaPhieuKiemKe, MaNguyenLieu, SoLuongHeThong, SoLuongThucTe, ChenhLech, TinhTrang) VALUES
    ('PKK001', 'NL001', 18, 16, -2, 'Thieu'),
    ('PKK001', 'NL003', 95, 95, 0, 'Du'),
    ('PKK001', 'NL004', 28, 27, -1, 'Thieu');

INSERT INTO ChiTietPhieuKiemKeDinhKy (MaPhieuKiemKe, MaLoHang, SoLuongHeThong, SoLuongThucTe, ChenhLech, TinhTrang) VALUES
    ('PKK002', 'LH001', 16, 15, -1, 'Thieu'),
    ('PKK002', 'LH002', 95, 95, 0, 'Du'),
    ('PKK002', 'LH004', 13, 13, 0, 'Du');

INSERT INTO PhieuXuatHuy (MaPhieuHuy, NgayTao, LyDoHuy, TrangThai, MaTaiKhoan, MaPhieuKiemKe) VALUES
    ('PH001', '2026-05-21', 'Lo hang het han su dung', 'Da duyet', 'QL001', 'PKK001');

INSERT INTO ChiTietPhieuHuy (MaPhieuHuy, MaNguyenLieu, SoLuongHuy) VALUES
    ('PH001', 'NL006', 0);

INSERT INTO PhieuGiaiTrinh (MaPhieuGiaiTrinh, NgayTao, NoiDung, NguyenNhan, MaTaiKhoan, MaPhieuKiemKe) VALUES
    ('PGT001', '2026-05-21', 'Giai trinh hang thieu sau kiem ke dinh ky thang 5', 'That thoat trong qua trinh so che', 'NV001', 'PKK002');
