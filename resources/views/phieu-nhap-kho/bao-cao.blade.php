<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Nhập Kho – {{ $phieuNhapKho->MaPhieuNhap }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; color: #000; background: #fff; }

        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px double #000; padding-bottom: 16px; }
        .header .company { font-size: 11px; color: #555; margin-bottom: 6px; }
        .header h1 { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header .doc-code { font-size: 12px; margin-top: 4px; }

        .info-section { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .info-row { display: flex; gap: 8px; margin-bottom: 6px; }
        .info-row label { font-weight: bold; min-width: 140px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #000; padding: 7px 10px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        td.center { text-align: center; }
        tfoot td { font-weight: bold; background: #f0f0f0; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 40px; }
        .sig-box { text-align: center; }
        .sig-box .title { font-weight: bold; margin-bottom: 4px; }
        .sig-box .note { font-size: 11px; font-style: italic; color: #555; }
        .sig-line { border-bottom: 1px dashed #000; height: 60px; margin: 12px 0 8px; }

        .footer { margin-top: 30px; font-size: 11px; color: #555; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding:16px; background:#f9f9f9; border-bottom:2px solid #E31837; text-align:right">
    <button onclick="window.print()" style="padding:8px 20px; background:#E31837; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; font-weight:bold;">🖨 In báo cáo</button>
    <button onclick="window.close()" style="padding:8px 20px; background:#e5e7eb; color:#333; border:none; border-radius:6px; font-size:14px; cursor:pointer; margin-left:8px;">✖ Đóng</button>
</div>

<div style="padding: 24px 40px;">

    {{-- Header --}}
    <div class="header">
        <div class="company">CÔNG TY TNHH LOTTERIA VIỆT NAM – CHI NHÁNH BÀ TRIỆU</div>
        <div class="company">Địa chỉ: 154 Phố Bà Triệu, Nguyễn Du, Hai Bà Trưng, Hà Nội</div>
        <h1>Phiếu Nhập Kho</h1>
        <div class="doc-code">Mã phiếu: <strong>{{ $phieuNhapKho->MaPhieuNhap }}</strong></div>
    </div>

    {{-- Thông tin chung --}}
    <div class="info-section">
        <div>
            <div class="info-row"><label>Mã phiếu nhập:</label><span>{{ $phieuNhapKho->MaPhieuNhap }}</span></div>
            <div class="info-row"><label>Phiếu nhận hàng:</label><span>{{ $phieuNhapKho->MaPhieuNhan }}</span></div>
            <div class="info-row"><label>Đơn đặt hàng:</label><span>{{ $phieuNhapKho->phieuNhanHang->MaDonDatHang ?? '–' }}</span></div>
        </div>
        <div>
            <div class="info-row"><label>Ngày nhập kho:</label><span>{{ \Carbon\Carbon::parse($phieuNhapKho->NgayNhap)->format('d/m/Y') }}</span></div>
            <div class="info-row"><label>Người xác nhận:</label><span>{{ $phieuNhapKho->taiKhoan->HoTen ?? '–' }}</span></div>
            <div class="info-row"><label>Ghi chú:</label><span>{{ $phieuNhapKho->GhiChu ?? '–' }}</span></div>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    <table>
        <thead>
            <tr>
                <th style="width:5%">STT</th>
                <th style="width:28%">Tên nguyên liệu</th>
                <th style="width:10%">Nhóm hàng</th>
                <th style="width:8%">ĐVT</th>
                <th style="width:10%">SL nhập</th>
                <th style="width:13%">Ngày SX</th>
                <th style="width:13%">Hạn SD</th>
                <th style="width:13%">Tình trạng</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 1; $tongSL = 0; @endphp
            @foreach($loHangTheoNL as $maNL => $loHangs)
            @php
                $nl = $loHangs->first()->nguyenLieu;
                $slTong = $loHangs->sum('SoLuongNhap');
                $tongSL += $slTong;
            @endphp
            <tr>
                <td class="center">{{ $stt++ }}</td>
                <td>{{ $nl->TenNguyenLieu ?? $maNL }}</td>
                <td class="center">{{ $nl->NhomHang ?? '' }}</td>
                <td class="center">{{ $nl->DonViTinh ?? '' }}</td>
                <td class="center"><strong>{{ $slTong }}</strong></td>
                <td class="center">
                    @foreach($loHangs as $lh)
                        {{ \Carbon\Carbon::parse($lh->NgaySanXuat)->format('d/m/Y') }}<br>
                    @endforeach
                </td>
                <td class="center">
                    @foreach($loHangs as $lh)
                        {{ \Carbon\Carbon::parse($lh->HanSuDung)->format('d/m/Y') }}<br>
                    @endforeach
                </td>
                <td class="center">
                    @foreach($loHangs as $lh){{ $lh->TrangThai }}<br>@endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right">Tổng số lượng nhập:</td>
                <td class="center">{{ $tongSL }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Chữ ký --}}
    <div class="signatures">
        <div class="sig-box">
            <div class="title">Nhân viên nhận hàng</div>
            <div class="note">(Ký, ghi rõ họ tên)</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig-box">
            <div class="title">Quản lý xác nhận</div>
            <div class="note">(Ký, ghi rõ họ tên)</div>
            <div class="sig-line"></div>
            <div style="font-size:12px">{{ $phieuNhapKho->taiKhoan->HoTen ?? '' }}</div>
        </div>
        <div class="sig-box">
            <div class="title">Cửa hàng trưởng</div>
            <div class="note">(Ký, ghi rõ họ tên)</div>
            <div class="sig-line"></div>
        </div>
    </div>

    <div class="footer">
        In lúc: {{ now()->format('H:i – d/m/Y') }} | Hệ thống quản lý kho Lotteria Bà Triệu
    </div>
</div>

</body>
</html>
