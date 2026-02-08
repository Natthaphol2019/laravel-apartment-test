@php
    // 1. คำนวณจำนวนคอลัมน์เพื่อปรับขนาดฟอนต์แบบอัตโนมัติ
    $totalCols = 7 + count($show_columns);

    if ($totalCols > 15) {
        $dynamicFontSize = '7pt';
    } elseif ($totalCols > 12) {
        $dynamicFontSize = '8pt';
    } elseif ($totalCols > 10) {
        $dynamicFontSize = '9pt';
    } else {
        $dynamicFontSize = '10pt';
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @font-face {
            font-family: 'Sarabun-Regular';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Sarabun-Regular.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Sarabun-Regular';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/Sarabun-Bold.ttf') }}") format('truetype');
        }

        body {
            font-family: 'Sarabun-Regular';
            font-size: {{ $dynamicFontSize }};
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* ✅ แก้ไขตารางไม่ให้มีช่องว่างระหว่างเส้น (No gaps) */
        .table {
            width: 100%;
            border-collapse: collapse; /* เชื่อมเส้นขอบให้เป็นเส้นเดียว */
            margin-top: 15px;
            table-layout: fixed; /* บังคับความกว้างให้คงที่ */
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 3px 2px;
            font-size: {{ $dynamicFontSize }}; /* ปรับขนาดฟอนต์ตามคอลัมน์ */
            word-wrap: break-word;
        }

        .table th {
            font-weight: bold;
            background-color: #f0f0f0;
            vertical-align: middle;
        }

        .header-title {
            font-size: 16pt;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="header-title fw-bold">รายงานเก็บเงินประจำเดือน {{ $thai_month }} {{ $apartment->name ?? 'อาทิตย์ อพาร์ทเม้นท์' }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">ลำดับ</th>
                <th width="45">ห้อง</th>
                <th width="30">ว่าง</th>
                <th width="30">เช่า</th>
                <th>ชื่อ-นามสกุล</th>
                {{-- ✅ แสดงหัวคอลัมน์ตาม Checkbox ที่เลือกมา --}}
                @foreach ($show_columns as $colName)
                    <th>{{ $colName }}</th>
                @endforeach
                <th width="60">รวมเงิน</th>
                <th width="80">วันที่รับเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rooms as $index => $room)
                @php
                    $tenant = $room->tenants->first();
                    $isOccupied = $room->status == 'มีผู้เช่า';
                    // คำนวณยอดรวมเฉพาะคอลัมน์ที่เลือก
                    $rowTotal = $room->expense_details->only($show_columns)->sum();
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $room->room_number }}</td>
                    <td class="text-center">{{ !$isOccupied ? '1' : '-' }}</td>
                    <td class="text-center">{{ $isOccupied ? '1' : '-' }}</td>
                    <td style="padding-left: 5px;">
                        @if ($isOccupied && $tenant)
                            {{ $tenant->first_name }} {{ $tenant->last_name }}
                        @else
                            <span style="color: #888;">ชื่อ ว่าง</span>
                        @endif
                    </td>
                    @foreach ($show_columns as $colName)
                        <td class="text-end">
                            {{ isset($room->expense_details[$colName]) ? number_format($room->expense_details[$colName], 0) : '-' }}
                        </td>
                    @endforeach
                    <td class="text-end fw-bold">
                        {{ $rowTotal > 0 ? number_format($rowTotal, 0) : '-' }}
                    </td>
                    {{-- ✅ คอลัมน์วันที่ใช้ขนาดฟอนต์ไดนามิกตามคอลัมน์อื่น --}}
                    <td class="text-center">{{ $room->payment_date_display }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #f9f9f9; font-weight: bold;">
            <tr>
                <td colspan="2" class="text-center">รวม</td>
                <td class="text-center">{{ $rooms->where('status', 'ว่าง')->count() ?: '-' }}</td>
                <td class="text-center">{{ $rooms->where('status', 'มีผู้เช่า')->count() ?: '-' }}</td>
                <td class="text-center">{{ $rooms->where('status', 'มีผู้เช่า')->count() }} ราย</td>

                {{-- ยอดรวมแต่ละคอลัมน์ (Fix scope bug ด้วย use ($show_columns)) --}}
                @foreach ($show_columns as $colName)
                    <td class="text-end">
                        @php
                            $columnSum = $rooms->sum(function ($r) use ($colName) {
                                return $r->expense_details->get($colName) ?? 0;
                            });
                        @endphp
                        {{ number_format($columnSum, 0) }}
                    </td>
                @endforeach

                {{-- ยอดรวมสุทธิท้ายตาราง --}}
                <td class="text-end">
                    @php
                        $grandTotal = $rooms->sum(function ($r) use ($show_columns) {
                            return $r->expense_details ? $r->expense_details->only($show_columns)->sum() : 0;
                        });
                    @endphp
                    {{ number_format($grandTotal, 0) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>