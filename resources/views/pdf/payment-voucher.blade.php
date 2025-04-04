<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Phiếu Chi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 11px;
            margin-bottom: 15px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            text-align: center;
        }

        .content {
            margin: 20px 0;
        }

        .content-row {
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }

        .label {
            font-weight: bold;
            width: 120px;
            display: table-cell;
            vertical-align: top;
        }

        .value {
            display: table-cell;
            vertical-align: top;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 70px;
        }

        .signature-box {
            text-align: center;
            width: 120px;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
        }

        .signature-name {
            margin-top: 10px;
            font-style: italic;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td,
        .table th {
            border: 1px solid #000;
            padding: 5px;
        }

        .amount {
            font-weight: bold;
            font-size: 14px;
        }

        .amount-in-words {
            font-style: italic;
            font-size: 11px;
        }

        .signature-column {
            float: left;
            width: 33%;
            text-align: center;
        }

        .signature-line {
            margin-top: 60px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .date-section {
            text-align: right;
            margin-bottom: 15px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-address">{{ $company['address'] }}</div>
            <div class="title">PHIẾU CHI</div>
            <div>Mã phiếu: {{ $paymentVoucher->code }}</div>
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
                <div class="value">{{ $paymentVoucher->description ?? ($paymentVoucher->project ? 'Chi phí dự án ' .
                    $paymentVoucher->project->name : 'N/A') }}</div>
            </div>

            <div class="content-row">
                <div class="label">Số tiền:</div>
                <div class="value amount">{{ number_format($paymentVoucher->amount, 0, ',', '.') }} VNĐ</div>
            </div>

            <div class="content-row">
                <div class="label">Bằng chữ:</div>
                <div class="value amount-in-words">{{ ucfirst($amountInWords) }}</div>
            </div>
        </div>

        <div class="date-section">
            Vũng Tàu, ngày {{ date('d', strtotime($paymentVoucher->created_at)) }} tháng {{ date('m',
            strtotime($paymentVoucher->created_at)) }} năm {{ date('Y', strtotime($paymentVoucher->created_at)) }}
        </div>

        <div class="clearfix">
            <div class="signature-column">
                <div><strong>Giám đốc</strong></div>
                <div class="signature-line"></div>
            </div>

            <div class="signature-column">
                <div><strong>Kế toán trưởng</strong></div>
                <div class="signature-line"></div>
            </div>

            <div class="signature-column">
                <div><strong>Thủ quỹ</strong></div>
                <div class="signature-line"></div>
            </div>
        </div>

        <div style="margin-top: 30px;"></div>

        <div class="clearfix">
            <div class="signature-column">
                <div><strong>Người lập phiếu</strong></div>
                <div class="signature-line"></div>
            </div>

            <div class="signature-column">
                <div><strong>Người nhận tiền</strong></div>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
</body>

</html>