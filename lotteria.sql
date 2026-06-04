-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 08:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lotteria`
--

-- --------------------------------------------------------

--
-- Table structure for table `chitietdondathang`
--

CREATE TABLE `chitietdondathang` (
  `MaDonDatHang` varchar(10) NOT NULL,
  `MaNguyenLieu` varchar(10) NOT NULL,
  `SoLuongDat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietdondathang`
--

INSERT INTO `chitietdondathang` (`MaDonDatHang`, `MaNguyenLieu`, `SoLuongDat`) VALUES
('DDH001', 'NL001', 20),
('DDH001', 'NL003', 100),
('DDH001', 'NL004', 30),
('DDH002', 'NL002', 15),
('DDH002', 'NL006', 25),
('DDH003', 'NL005', 10),
('DDH003', 'NL008', 5);

-- --------------------------------------------------------

--
-- Table structure for table `chitietphieuhuy`
--

CREATE TABLE `chitietphieuhuy` (
  `MaPhieuHuy` varchar(10) NOT NULL,
  `MaNguyenLieu` varchar(10) NOT NULL,
  `SoLuongHuy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietphieuhuy`
--

INSERT INTO `chitietphieuhuy` (`MaPhieuHuy`, `MaNguyenLieu`, `SoLuongHuy`) VALUES
('PH001', 'NL006', 0);

-- --------------------------------------------------------

--
-- Table structure for table `chitietphieukiemkecuoingay`
--

CREATE TABLE `chitietphieukiemkecuoingay` (
  `MaPhieuKiemKe` varchar(10) NOT NULL,
  `MaNguyenLieu` varchar(10) NOT NULL,
  `SoLuongHeThong` int(11) NOT NULL,
  `SoLuongThucTe` int(11) NOT NULL,
  `ChenhLech` int(11) NOT NULL,
  `TinhTrang` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietphieukiemkecuoingay`
--

INSERT INTO `chitietphieukiemkecuoingay` (`MaPhieuKiemKe`, `MaNguyenLieu`, `SoLuongHeThong`, `SoLuongThucTe`, `ChenhLech`, `TinhTrang`) VALUES
('PKK001', 'NL001', 18, 16, -2, 'Thiếu'),
('PKK001', 'NL003', 95, 95, 0, 'Đủ'),
('PKK001', 'NL004', 28, 27, -1, 'Thiếu');

-- --------------------------------------------------------

--
-- Table structure for table `chitietphieukiemkedinhky`
--

CREATE TABLE `chitietphieukiemkedinhky` (
  `MaPhieuKiemKe` varchar(10) NOT NULL,
  `MaLoHang` varchar(10) NOT NULL,
  `SoLuongHeThong` int(11) NOT NULL,
  `SoLuongThucTe` int(11) NOT NULL,
  `ChenhLech` int(11) NOT NULL,
  `TinhTrang` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietphieukiemkedinhky`
--

INSERT INTO `chitietphieukiemkedinhky` (`MaPhieuKiemKe`, `MaLoHang`, `SoLuongHeThong`, `SoLuongThucTe`, `ChenhLech`, `TinhTrang`) VALUES
('PKK002', 'LH001', 16, 15, -1, 'Thiếu'),
('PKK002', 'LH002', 95, 95, 0, 'Đủ'),
('PKK002', 'LH004', 13, 13, 0, 'Đủ');

-- --------------------------------------------------------

--
-- Table structure for table `chitietphieuxuat`
--

CREATE TABLE `chitietphieuxuat` (
  `MaPhieuXuat` varchar(10) NOT NULL,
  `MaLoHang` varchar(10) NOT NULL,
  `SoLuongXuat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietphieuxuat`
--

INSERT INTO `chitietphieuxuat` (`MaPhieuXuat`, `MaLoHang`, `SoLuongXuat`) VALUES
('PX001', 'LH001', 2),
('PX001', 'LH002', 5),
('PX002', 'LH003', 2);

-- --------------------------------------------------------

--
-- Table structure for table `dondathang`
--

CREATE TABLE `dondathang` (
  `MaDonDatHang` varchar(10) NOT NULL,
  `NgayDat` date NOT NULL,
  `TrangThai` varchar(50) NOT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dondathang`
--

INSERT INTO `dondathang` (`MaDonDatHang`, `NgayDat`, `TrangThai`, `GhiChu`, `MaTaiKhoan`) VALUES
('DDH001', '2026-05-01', 'Đã nhập kho', NULL, 'QL001'),
('DDH002', '2026-05-10', 'Đã nhận hàng', 'Giao sáng', 'QL001'),
('DDH003', '2026-05-20', 'Chờ phê duyệt', 'Đơn khẩn', 'QL002');

-- --------------------------------------------------------

--
-- Table structure for table `lohang`
--

CREATE TABLE `lohang` (
  `MaLoHang` varchar(10) NOT NULL,
  `NgaySanXuat` date NOT NULL,
  `HanSuDung` date NOT NULL,
  `SoLuongNhap` int(11) NOT NULL,
  `SoLuongConLai` int(11) NOT NULL,
  `TrangThai` varchar(50) NOT NULL,
  `MaNguyenLieu` varchar(10) NOT NULL,
  `MaPhieuNhan` varchar(10) DEFAULT NULL,
  `MaPhieuDoiTra` varchar(10) DEFAULT NULL,
  `MaPhieuNhap` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lohang`
--

INSERT INTO `lohang` (`MaLoHang`, `NgaySanXuat`, `HanSuDung`, `SoLuongNhap`, `SoLuongConLai`, `TrangThai`, `MaNguyenLieu`, `MaPhieuNhan`, `MaPhieuDoiTra`, `MaPhieuNhap`) VALUES
('LH001', '2026-04-01', '2026-07-01', 20, 18, 'Còn hạn', 'NL001', 'PN001', NULL, 'PNK001'),
('LH002', '2026-03-15', '2026-06-15', 100, 95, 'Sắp hết hạn', 'NL003', 'PN001', NULL, 'PNK001'),
('LH003', '2026-04-10', '2026-06-10', 30, 28, 'Sắp hết hạn', 'NL004', 'PN001', NULL, 'PNK001'),
('LH004', '2026-05-01', '2026-08-01', 15, 13, 'Còn hạn', 'NL002', 'PN002', NULL, 'PNK002'),
('LH005', '2026-02-01', '2026-05-30', 25, 0, 'Hết hạn', 'NL006', 'PN002', 'PDT002', 'PNK002');

-- --------------------------------------------------------

--
-- Table structure for table `nguyenlieu`
--

CREATE TABLE `nguyenlieu` (
  `MaNguyenLieu` varchar(10) NOT NULL,
  `TenNguyenLieu` varchar(100) NOT NULL,
  `DonViTinh` varchar(20) NOT NULL,
  `NhomHang` varchar(50) NOT NULL,
  `SoLuongTonKho` int(11) NOT NULL DEFAULT 0,
  `MoTa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguyenlieu`
--

INSERT INTO `nguyenlieu` (`MaNguyenLieu`, `TenNguyenLieu`, `DonViTinh`, `NhomHang`, `SoLuongTonKho`, `MoTa`) VALUES
('NL001', 'Thịt bò patty', 'Kg', 'Hàng đông', 55, 'Thịt bò xay đông lạnh'),
('NL002', 'Thịt gà fillet', 'Kg', 'Hàng đông', 40, 'Ức gà phi lê đông lạnh'),
('NL003', 'Bánh mì hamburger', 'Cái', 'Hàng khô', 200, 'Bánh mì tròn'),
('NL004', 'Khoai tây chiên', 'Kg', 'Hàng đông', 80, 'Khoai tây cắt sợi đông lạnh'),
('NL005', 'Bơ sốt mayonnaise', 'Lọ', 'Hàng khô', 30, 'Sốt mayonnaise đóng lọ 500g'),
('NL006', 'Phô mai slice', 'Gói', 'Hàng đông', 60, 'Phô mai lát đóng gói'),
('NL007', 'Xà lách', 'Kg', 'Hàng đông', 20, 'Xà lách tươi đông lạnh'),
('NL008', 'Muối', 'Kg', 'Hàng khô', 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phieudoitra`
--

CREATE TABLE `phieudoitra` (
  `MaPhieuDoiTra` varchar(10) NOT NULL,
  `NgayTao` date NOT NULL,
  `LoaiXuLy` varchar(50) NOT NULL,
  `LyDo` varchar(255) NOT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL,
  `MaPhieuNhan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieudoitra`
--

INSERT INTO `phieudoitra` (`MaPhieuDoiTra`, `NgayTao`, `LoaiXuLy`, `LyDo`, `MaTaiKhoan`, `MaPhieuNhan`) VALUES
('PDT001', '2026-05-04', 'Đổi hàng', 'Hàng bị hỏng', 'NV001', 'PN001'),
('PDT002', '2026-05-13', 'Trả hàng', 'Giao thiếu số lượng', 'NV002', 'PN002');

-- --------------------------------------------------------

--
-- Table structure for table `phieugiaitrinh`
--

CREATE TABLE `phieugiaitrinh` (
  `MaPhieuGiaiTrinh` varchar(10) NOT NULL,
  `NgayTao` date NOT NULL,
  `NoiDung` varchar(255) NOT NULL,
  `NguyenNhan` varchar(255) NOT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL,
  `MaPhieuKiemKe` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieugiaitrinh`
--

INSERT INTO `phieugiaitrinh` (`MaPhieuGiaiTrinh`, `NgayTao`, `NoiDung`, `NguyenNhan`, `MaTaiKhoan`, `MaPhieuKiemKe`) VALUES
('PGT001', '2026-05-21', 'Giải trình hàng thiếu sau kiểm kê định kỳ tháng 5', 'Thất thoát trong quá trình sơ chế', 'NV001', 'PKK002');

-- --------------------------------------------------------

--
-- Table structure for table `phieukiemke`
--

CREATE TABLE `phieukiemke` (
  `MaPhieuKiemKe` varchar(10) NOT NULL,
  `NgayKiemKe` date NOT NULL,
  `LoaiKiemKe` varchar(50) NOT NULL,
  `TrangThai` varchar(50) NOT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieukiemke`
--

INSERT INTO `phieukiemke` (`MaPhieuKiemKe`, `NgayKiemKe`, `LoaiKiemKe`, `TrangThai`, `GhiChu`, `MaTaiKhoan`) VALUES
('PKK001', '2026-05-06', 'Cuối ngày', 'Đã duyệt', NULL, 'QL001'),
('PKK002', '2026-05-20', 'Cuối kỳ', 'Chờ duyệt', 'Kiểm kỳ T5', 'QL002');

-- --------------------------------------------------------

--
-- Table structure for table `phieunhanhang`
--

CREATE TABLE `phieunhanhang` (
  `MaPhieuNhan` varchar(10) NOT NULL,
  `NgayNhan` date NOT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL,
  `MaDonDatHang` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieunhanhang`
--

INSERT INTO `phieunhanhang` (`MaPhieuNhan`, `NgayNhan`, `GhiChu`, `MaTaiKhoan`, `MaDonDatHang`) VALUES
('PN001', '2026-05-03', NULL, 'NV001', 'DDH001'),
('PN002', '2026-05-12', 'Thiếu 2 kg', 'NV002', 'DDH002');

-- --------------------------------------------------------

--
-- Table structure for table `phieunhapkho`
--

CREATE TABLE `phieunhapkho` (
  `MaPhieuNhap` varchar(10) NOT NULL,
  `NgayNhap` date NOT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL,
  `MaPhieuNhan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieunhapkho`
--

INSERT INTO `phieunhapkho` (`MaPhieuNhap`, `NgayNhap`, `GhiChu`, `MaTaiKhoan`, `MaPhieuNhan`) VALUES
('PNK001', '2026-05-04', NULL, 'NV001', 'PN001'),
('PNK002', '2026-05-13', 'Nhập bù', 'NV002', 'PN002');

-- --------------------------------------------------------

--
-- Table structure for table `phieuxuathuy`
--

CREATE TABLE `phieuxuathuy` (
  `MaPhieuHuy` varchar(10) NOT NULL,
  `NgayTao` date NOT NULL,
  `LyDoHuy` varchar(255) NOT NULL,
  `TrangThai` varchar(50) NOT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL,
  `MaPhieuKiemKe` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieuxuathuy`
--

INSERT INTO `phieuxuathuy` (`MaPhieuHuy`, `NgayTao`, `LyDoHuy`, `TrangThai`, `MaTaiKhoan`, `MaPhieuKiemKe`) VALUES
('PH001', '2026-05-21', 'Lô hàng hết hạn sử dụng', 'Đã duyệt', 'QL001', 'PKK001');

-- --------------------------------------------------------

--
-- Table structure for table `phieuxuatkho`
--

CREATE TABLE `phieuxuatkho` (
  `MaPhieuXuat` varchar(10) NOT NULL,
  `NgayXuat` date NOT NULL,
  `TrangThai` varchar(50) NOT NULL,
  `MaTaiKhoan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieuxuatkho`
--

INSERT INTO `phieuxuatkho` (`MaPhieuXuat`, `NgayXuat`, `TrangThai`, `MaTaiKhoan`) VALUES
('PX001', '2026-05-05', 'Hoàn tất', 'NV001'),
('PX002', '2026-05-15', 'Chờ xuất', 'NV003');

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan`
--

CREATE TABLE `taikhoan` (
  `MaTaiKhoan` varchar(10) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `SoDienThoai` varchar(10) NOT NULL,
  `VaiTro` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taikhoan`
--

INSERT INTO `taikhoan` (`MaTaiKhoan`, `HoTen`, `MatKhau`, `SoDienThoai`, `VaiTro`) VALUES
('CHT001', 'Nguyễn Văn An', '$2y$12$KJEmsPKg3QIx7KuCi67Qx.bS6sPcX4bkkyQtszdG0LjjlbZ9YuC9S', '0901234567', 'Cửa hàng trưởng'),
('NV001', 'Phạm Thị Dung', '$2y$12$FGQNkhuNX17kw6BWffK2Yu7NoTMtPxom0dHSM93K2rHoWoMeVOqI.', '0934567890', 'Nhân viên'),
('NV002', 'Hoàng Văn Em', '$2y$12$6j53fvR3S7Ma2ynebi.CFee2lD/XitYptoz.tmMtYJIHQjYxE0Snq', '0945678901', 'Nhân viên'),
('NV003', 'Đỗ Thị Phương', '$2y$12$qAplDtdK7n65ia1NdNTeluZm/gze68mUpGlvgcXcoOmu3p22S4Dye', '0956789012', 'Nhân viên'),
('QL001', 'Trần Thị Bình', '$2y$12$MBPM1cgcK3D7rhA5r9s3QuqmXtrGrVqYdkZrbvEe1Ek2PtnyDYgVe', '0912345678', 'Quản lý'),
('QL002', 'Lê Văn Cường', '$2y$12$7h/6uNGWqXG16p/4Lo2TXeEVkJEeZDJ8xtelVJOthUDNHYzuANZrS', '0923456789', 'Quản lý'),
('QL003', 'Tô Thị Phương Anh', '$2y$12$PPXQtDqbWolAhp.SkYVE2.vzK8/tv6BUVyPn0XKfNGbypFgVCgQEa', '0111111111', 'Quản lý');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chitietdondathang`
--
ALTER TABLE `chitietdondathang`
  ADD PRIMARY KEY (`MaDonDatHang`,`MaNguyenLieu`),
  ADD KEY `MaNguyenLieu` (`MaNguyenLieu`);

--
-- Indexes for table `chitietphieuhuy`
--
ALTER TABLE `chitietphieuhuy`
  ADD PRIMARY KEY (`MaPhieuHuy`,`MaNguyenLieu`),
  ADD KEY `MaNguyenLieu` (`MaNguyenLieu`);

--
-- Indexes for table `chitietphieukiemkecuoingay`
--
ALTER TABLE `chitietphieukiemkecuoingay`
  ADD PRIMARY KEY (`MaPhieuKiemKe`,`MaNguyenLieu`),
  ADD KEY `MaNguyenLieu` (`MaNguyenLieu`);

--
-- Indexes for table `chitietphieukiemkedinhky`
--
ALTER TABLE `chitietphieukiemkedinhky`
  ADD PRIMARY KEY (`MaPhieuKiemKe`,`MaLoHang`),
  ADD KEY `MaLoHang` (`MaLoHang`);

--
-- Indexes for table `chitietphieuxuat`
--
ALTER TABLE `chitietphieuxuat`
  ADD PRIMARY KEY (`MaPhieuXuat`,`MaLoHang`),
  ADD KEY `MaLoHang` (`MaLoHang`);

--
-- Indexes for table `dondathang`
--
ALTER TABLE `dondathang`
  ADD PRIMARY KEY (`MaDonDatHang`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`);

--
-- Indexes for table `lohang`
--
ALTER TABLE `lohang`
  ADD PRIMARY KEY (`MaLoHang`),
  ADD KEY `MaNguyenLieu` (`MaNguyenLieu`),
  ADD KEY `MaPhieuNhan` (`MaPhieuNhan`),
  ADD KEY `MaPhieuDoiTra` (`MaPhieuDoiTra`),
  ADD KEY `MaPhieuNhap` (`MaPhieuNhap`);

--
-- Indexes for table `nguyenlieu`
--
ALTER TABLE `nguyenlieu`
  ADD PRIMARY KEY (`MaNguyenLieu`);

--
-- Indexes for table `phieudoitra`
--
ALTER TABLE `phieudoitra`
  ADD PRIMARY KEY (`MaPhieuDoiTra`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`),
  ADD KEY `MaPhieuNhan` (`MaPhieuNhan`);

--
-- Indexes for table `phieugiaitrinh`
--
ALTER TABLE `phieugiaitrinh`
  ADD PRIMARY KEY (`MaPhieuGiaiTrinh`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`),
  ADD KEY `MaPhieuKiemKe` (`MaPhieuKiemKe`);

--
-- Indexes for table `phieukiemke`
--
ALTER TABLE `phieukiemke`
  ADD PRIMARY KEY (`MaPhieuKiemKe`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`);

--
-- Indexes for table `phieunhanhang`
--
ALTER TABLE `phieunhanhang`
  ADD PRIMARY KEY (`MaPhieuNhan`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`),
  ADD KEY `MaDonDatHang` (`MaDonDatHang`);

--
-- Indexes for table `phieunhapkho`
--
ALTER TABLE `phieunhapkho`
  ADD PRIMARY KEY (`MaPhieuNhap`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`),
  ADD KEY `MaPhieuNhan` (`MaPhieuNhan`);

--
-- Indexes for table `phieuxuathuy`
--
ALTER TABLE `phieuxuathuy`
  ADD PRIMARY KEY (`MaPhieuHuy`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`),
  ADD KEY `MaPhieuKiemKe` (`MaPhieuKiemKe`);

--
-- Indexes for table `phieuxuatkho`
--
ALTER TABLE `phieuxuatkho`
  ADD PRIMARY KEY (`MaPhieuXuat`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`);

--
-- Indexes for table `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`MaTaiKhoan`),
  ADD UNIQUE KEY `SoDienThoai` (`SoDienThoai`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitietdondathang`
--
ALTER TABLE `chitietdondathang`
  ADD CONSTRAINT `chitietdondathang_ibfk_1` FOREIGN KEY (`MaDonDatHang`) REFERENCES `dondathang` (`MaDonDatHang`),
  ADD CONSTRAINT `chitietdondathang_ibfk_2` FOREIGN KEY (`MaNguyenLieu`) REFERENCES `nguyenlieu` (`MaNguyenLieu`);

--
-- Constraints for table `chitietphieuhuy`
--
ALTER TABLE `chitietphieuhuy`
  ADD CONSTRAINT `chitietphieuhuy_ibfk_1` FOREIGN KEY (`MaPhieuHuy`) REFERENCES `phieuxuathuy` (`MaPhieuHuy`),
  ADD CONSTRAINT `chitietphieuhuy_ibfk_2` FOREIGN KEY (`MaNguyenLieu`) REFERENCES `nguyenlieu` (`MaNguyenLieu`);

--
-- Constraints for table `chitietphieukiemkecuoingay`
--
ALTER TABLE `chitietphieukiemkecuoingay`
  ADD CONSTRAINT `chitietphieukiemkecuoingay_ibfk_1` FOREIGN KEY (`MaPhieuKiemKe`) REFERENCES `phieukiemke` (`MaPhieuKiemKe`),
  ADD CONSTRAINT `chitietphieukiemkecuoingay_ibfk_2` FOREIGN KEY (`MaNguyenLieu`) REFERENCES `nguyenlieu` (`MaNguyenLieu`);

--
-- Constraints for table `chitietphieukiemkedinhky`
--
ALTER TABLE `chitietphieukiemkedinhky`
  ADD CONSTRAINT `chitietphieukiemkedinhky_ibfk_1` FOREIGN KEY (`MaPhieuKiemKe`) REFERENCES `phieukiemke` (`MaPhieuKiemKe`),
  ADD CONSTRAINT `chitietphieukiemkedinhky_ibfk_2` FOREIGN KEY (`MaLoHang`) REFERENCES `lohang` (`MaLoHang`);

--
-- Constraints for table `chitietphieuxuat`
--
ALTER TABLE `chitietphieuxuat`
  ADD CONSTRAINT `chitietphieuxuat_ibfk_1` FOREIGN KEY (`MaPhieuXuat`) REFERENCES `phieuxuatkho` (`MaPhieuXuat`),
  ADD CONSTRAINT `chitietphieuxuat_ibfk_2` FOREIGN KEY (`MaLoHang`) REFERENCES `lohang` (`MaLoHang`);

--
-- Constraints for table `dondathang`
--
ALTER TABLE `dondathang`
  ADD CONSTRAINT `dondathang_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`);

--
-- Constraints for table `lohang`
--
ALTER TABLE `lohang`
  ADD CONSTRAINT `lohang_ibfk_1` FOREIGN KEY (`MaNguyenLieu`) REFERENCES `nguyenlieu` (`MaNguyenLieu`),
  ADD CONSTRAINT `lohang_ibfk_2` FOREIGN KEY (`MaPhieuNhan`) REFERENCES `phieunhanhang` (`MaPhieuNhan`),
  ADD CONSTRAINT `lohang_ibfk_3` FOREIGN KEY (`MaPhieuDoiTra`) REFERENCES `phieudoitra` (`MaPhieuDoiTra`),
  ADD CONSTRAINT `lohang_ibfk_4` FOREIGN KEY (`MaPhieuNhap`) REFERENCES `phieunhapkho` (`MaPhieuNhap`);

--
-- Constraints for table `phieudoitra`
--
ALTER TABLE `phieudoitra`
  ADD CONSTRAINT `phieudoitra_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `phieudoitra_ibfk_2` FOREIGN KEY (`MaPhieuNhan`) REFERENCES `phieunhanhang` (`MaPhieuNhan`);

--
-- Constraints for table `phieugiaitrinh`
--
ALTER TABLE `phieugiaitrinh`
  ADD CONSTRAINT `phieugiaitrinh_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `phieugiaitrinh_ibfk_2` FOREIGN KEY (`MaPhieuKiemKe`) REFERENCES `phieukiemke` (`MaPhieuKiemKe`);

--
-- Constraints for table `phieukiemke`
--
ALTER TABLE `phieukiemke`
  ADD CONSTRAINT `phieukiemke_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`);

--
-- Constraints for table `phieunhanhang`
--
ALTER TABLE `phieunhanhang`
  ADD CONSTRAINT `phieunhanhang_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `phieunhanhang_ibfk_2` FOREIGN KEY (`MaDonDatHang`) REFERENCES `dondathang` (`MaDonDatHang`);

--
-- Constraints for table `phieunhapkho`
--
ALTER TABLE `phieunhapkho`
  ADD CONSTRAINT `phieunhapkho_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `phieunhapkho_ibfk_2` FOREIGN KEY (`MaPhieuNhan`) REFERENCES `phieunhanhang` (`MaPhieuNhan`);

--
-- Constraints for table `phieuxuathuy`
--
ALTER TABLE `phieuxuathuy`
  ADD CONSTRAINT `phieuxuathuy_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `phieuxuathuy_ibfk_2` FOREIGN KEY (`MaPhieuKiemKe`) REFERENCES `phieukiemke` (`MaPhieuKiemKe`);

--
-- Constraints for table `phieuxuatkho`
--
ALTER TABLE `phieuxuatkho`
  ADD CONSTRAINT `phieuxuatkho_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
