<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Phiếu Chi</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .page-container {
            position: relative;
        }

        .voucher {
            position: relative;
            height: calc(50% - 20mm);
        }

        .voucher-separator {
            border-top: 1px dashed #000;
            margin: 10mm 0;
        }

        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .company-name {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 6px 0;
            text-align: center;
        }

        .content {
            margin: 10px 0;
        }

        .content-row {
            margin-bottom: 6px;
            display: table;
            width: 100%;
        }

        .label {
            font-weight: bold;
            width: 100px;
            display: table-cell;
            vertical-align: top;
        }

        .value {
            display: table-cell;
            vertical-align: top;
        }

        .amount {
            font-weight: bold;
            font-size: 12px;
        }

        .amount-in-words {
            font-style: italic;
            font-size: 10px;
        }

        .signatures-container {
            margin-top: 12px;
            width: 100%;
        }

        .signatures-row {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .signature-cell {
            display: table-cell;
            text-align: center;
            padding: 0 5px;
            font-size: 10px;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 25px;
            border-top: 0.5px dotted #000;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        .date-section {
            text-align: right;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .copy-mark {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 9px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <!-- Liên 1 -->
        <div class="voucher">
            <div class="container">
                <div class="header">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-address">{{ $company['address'] }}</div>
                    <div class="title">PHIẾU CHI</div>
                    <div>
                        <span style="font-style: italic;">Ngày........</span>
                        <span style="font-style: italic;">Tháng........</span>
                        <span style="font-style: italic;">Năm........</span>
                    </div>
                </div>

                <div class="content">
                    <div class="content-row">
                        <div class="label">Người nhận:</div>
                        <div class="value">{{ $paymentVoucher->contractor->name }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Địa chỉ:</div>
                        <div class="value">{{ $paymentVoucher->contractor->address ?? 'N/A' }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Lý do:</div>
                        <div class="value">{{ $paymentVoucher->description ?? ($paymentVoucher->project ? 'Chi phí dự án
                            ' . $paymentVoucher->project->name : 'N/A') }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Số tiền:</div>
                        <div class="value amount">{{ number_format($paymentVoucher->amount, 0, ',', '.') }} VNĐ</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Bằng chữ:</div>
                        <div class="value amount-in-words">{{ ucfirst($amountInWords) }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Kèm theo:</div>
                        <div class="signature-line" style="margin-top: 15px; margin-left: 6px; width: 50%;"></div>
                    </div>
                </div>

                <div class="date-section">
                    <div>
                        <span style="font-style: italic;">Ngày........</span>
                        <span style="font-style: italic;">Tháng........</span>
                        <span style="font-style: italic;">Năm........</span>
                    </div>
                </div>

                <div class="signatures-container">
                    <div class="signatures-row">
                        <div class="signature-cell">
                            <strong>Giám đốc</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">Lê Hoàng Tâm</div>
                        </div>
                        <div class="signature-cell">
                            <strong>Kế toán trưởng</strong>
                        </div>
                        <div class="signature-cell">
                            <strong>Thủ quỹ</strong>
                        </div>
                        <div class="signature-cell">
                            <strong>Người lập phiếu</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">
                                Lê Thị Thu Hoài
                            </div>
                        </div>
                        <div class="signature-cell">
                            <strong>Người nhận</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">
                                {{ $paymentVoucher->contractor->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="voucher-separator"></div>

        <!-- Liên 2 -->
        <div class="voucher">
            <div class="container">
                <div class="header">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-address">{{ $company['address'] }}</div>
                    <div class="title">PHIẾU CHI</div>
                    <div>
                        <span style="font-style: italic;">Ngày........</span>
                        <span style="font-style: italic;">Tháng........</span>
                        <span style="font-style: italic;">Năm........</span>
                    </div>
                </div>

                <div class="content">
                    <div class="content-row">
                        <div class="label">Người nhận:</div>
                        <div class="value">{{ $paymentVoucher->contractor->name }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Địa chỉ:</div>
                        <div class="value">{{ $paymentVoucher->contractor->address ?? 'N/A' }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Lý do:</div>
                        <div class="value">{{ $paymentVoucher->description ?? ($paymentVoucher->project ? 'Chi phí dự án
                            ' . $paymentVoucher->project->name : 'N/A') }}</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Số tiền:</div>
                        <div class="value amount">{{ number_format($paymentVoucher->amount, 0, ',', '.') }} VNĐ</div>
                    </div>

                    <div class="content-row">
                        <div class="label">Bằng chữ:</div>
                        <div class="value amount-in-words">{{ ucfirst($amountInWords) }}</div>
                    </div>
                    <div class="content-row">
                        <div class="label">Kèm theo:</div>
                        <div class="signature-line" style="margin-top: 15px; margin-left: 6px; width: 50%;"></div>
                    </div>
                </div>

                <div class="date-section">
                    <div>
                        <span style="font-style: italic;">Ngày........</span>
                        <span style="font-style: italic;">Tháng........</span>
                        <span style="font-style: italic;">Năm........</span>
                    </div>
                </div>

                <div class="signatures-container">
                    <div class="signatures-row">
                        <div class="signature-cell">
                            <strong>Giám đốc</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">Lê Hoàng Tâm</div>
                        </div>
                        <div class="signature-cell">
                            <strong>Kế toán trưởng</strong>
                        </div>
                        <div class="signature-cell">
                            <strong>Thủ quỹ</strong>
                        </div>
                        <div class="signature-cell">
                            <strong>Người lập phiếu</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">
                                Lê Thị Thu Hoài
                            </div>
                        </div>
                        <div class="signature-cell">
                            <strong>Người nhận</strong>
                            <div style="height: 60px;"></div>
                            <div style="text-align: center; font-weight: bold;">
                                {{ $paymentVoucher->contractor->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>