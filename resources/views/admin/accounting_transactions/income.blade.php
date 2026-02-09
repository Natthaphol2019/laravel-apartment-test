@extends('admin.layout')
@section('content')
    <div class="container py-4 bg-white shadow-sm rounded">
        {{-- แถบควบคุม (Navigation & Filters) --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-xl-5">
                        <div class="btn-group w-100 shadow-sm">
                            <a href="{{ route('admin.accounting_transactions.summary', request()->query()) }}"
                                class="btn {{ request()->routeIs('admin.accounting_transactions.summary') ? 'btn-dark' : 'btn-outline-dark' }} btn-sm px-3">
                                <i class="bi bi-file-earmark-bar-graph me-1"></i> สรุปงบรับ-จ่าย
                            </a>
                            <a href="{{ route('admin.accounting_transactions.income', request()->query()) }}"
                                class="btn {{ request()->routeIs('admin.accounting_transactions.income') ? 'btn-success' : 'btn-outline-success' }} btn-sm px-3">
                                <i class="bi bi-graph-up-arrow me-1"></i> รายงานรายรับ
                            </a>
                            <a href="{{ route('admin.accounting_transactions.expense', request()->query()) }}" 
                                class="btn {{ request()->routeIs('admin.accounting_transactions.expense') ? 'btn-danger' : 'btn-outline-danger' }} btn-sm px-3">
                                <i class="bi bi-graph-down-arrow me-1"></i> รายงานรายจ่าย
                            </a>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <form method="GET" class="row g-2 justify-content-end align-items-center">
                            <div class="col-auto"><label class="small fw-bold text-muted">ตั้งแต่วันที่</label></div>
                            <div class="col-auto"><input type="date" name="date_start"
                                    class="form-control form-control-sm" value="{{ $startDate }}"></div>
                            <div class="col-auto"><label class="small fw-bold text-muted">ถึงวันที่</label></div>
                            <div class="col-auto"><input type="date" name="date_end" class="form-control form-control-sm"
                                    value="{{ $endDate }}"></div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-search"></i>
                                    ค้นหา</button>
                                <a href="{{ route('admin.accounting_transactions.income') }}"
                                    class="btn btn-light btn-sm border" title="ล้างค่า"><i
                                        class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <h3 class="fw-bold text-success">รายรับ {{ $thai_startDate }} - {{ $thai_endDate }}</h3>
        </div>
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.accounting_transactions.printIncomePdf', request()->query()) }}" 
            class="btn btn-outline-danger btn-sm px-3" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> โหลด PDF รายรับ
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle border-dark shadow-sm">
                <thead class="bg-light text-center border-dark fw-bold text-uppercase small">
                    <tr>
                        <th width="45%">รายการ</th>
                        <th width="30%">รายย่อย (บาท)</th>
                        <th width="25%">ยอดรวมสุทธิ (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ⚠️ 1. ส่วนยอดค้างรับ --}}
                    @php
                        $outstandingByItem = $outstandingDetails->flatMap->details
                            ->groupBy('name')
                            ->map->sum('subtotal');
                    @endphp
                    <tr class="bg-light fw-bold">
                        <td colspan="3">ค้างรับ (เดือน {{ $displayDate }})</td>
                    </tr>
                    @foreach ($outstandingByItem as $itemName => $subtotal)
                        <tr>
                            <td class="ps-4">- {{ $itemName }}</td>
                            <td class="text-end pe-4">{{ number_format($subtotal, 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold">
                        <td class="ps-3 text-danger">ยอดรวมค้างรับ</td>
                        <td></td>
                        <td class="text-end pe-4 text-danger">{{ number_format($outstandingAmount, 2) }}</td>
                    </tr>

                    {{-- 💰 2. ส่วนค่ามัดจำ (แสดงต่อท้ายค้างรับตามเงื่อนไข) --}}
                    @php
                        $depositData = $incomeByGroup->get('ค่ามัดจำ');
                        $depositTotal = $depositData ? $depositData->flatten()->sum('amount') : 0;
                        $cashTotal = 0; // ยอดรับรวม (เงินสด)
                    @endphp
                    @if ($depositTotal > 0)
                        <tr class="bg-light fw-bold">
                            <td colspan="3" class="py-2"></td>
                        </tr> {{-- เว้นบรรทัด --}}
                        @foreach ($depositData as $bName => $items)
                            <tr>
                                <td class="ps-3">ค่ามัดจำ ({{ $bName }})</td>
                                <td class="text-end pe-4">{{ number_format($items->sum('amount'), 2) }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold">
                            <td class="ps-3">รวมค่ามัดจำ</td>
                            <td></td>
                            <td class="text-end pe-4">{{ number_format($depositTotal, 2) }}</td>
                        </tr>
                        @php $cashTotal += $depositTotal; @endphp
                    @endif

                    {{-- 🏢 3. ส่วนค่าเช่าและค่าไฟ แยกตามตึก --}}
                    @php
                        $allBuildings = $incomeByGroup->flatMap(fn($b) => array_keys($b->toArray()))->unique();
                    @endphp

                    @foreach ($allBuildings as $bName)
                        @php
                            $bRent = $incomeByGroup->get('ค่าเช่าห้อง')?->get($bName)?->sum('amount') ?? 0;
                            $bElec = $incomeByGroup->get('ค่าไฟ')?->get($bName)?->sum('amount') ?? 0;
                            $bSubTotal = $bRent + $bElec;
                        @endphp
                        @if ($bSubTotal > 0)
                            <tr>
                                <td colspan="3" class=""></td>
                            </tr> {{-- เว้นบรรทัด --}}
                            <tr class="fw-bold">
                                <td colspan="3" class="ps-3">{{ $bName }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">- ค่าเช่า</td>
                                <td class="text-end pe-4">{{ number_format($bRent, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="ps-4">- ค่าไฟ</td>
                                <td class="text-end pe-4">{{ number_format($bElec, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="bg-light italic">
                                <td class="ps-4 fw-bold">รวมค่าเช่าและค่าไฟ ({{ $bName }})</td>
                                <td></td>
                                <td class="text-end pe-4 fw-bold">{{ number_format($bSubTotal, 2) }}</td>
                            </tr>
                            @php $cashTotal += $bSubTotal; @endphp
                        @endif
                    @endforeach

                    {{-- 💰 4. รายรับหมวดหมู่อื่นๆ (ยกเว้น มัดจำ, เช่า, ไฟ) --}}
                    @foreach ($incomeByGroup->except(['ค่าเช่าห้อง', 'ค่าไฟ', 'ค่ามัดจำ']) as $catName => $buildings)
                        <tr>
                            <td colspan="3" class=""></td>
                        </tr>
                        @php $catTotal = 0; @endphp
                        @foreach ($buildings as $bName => $items)
                            @php
                                $amt = $items->sum('amount');
                                $catTotal += $amt;
                            @endphp
                            <tr>
                                <td class="ps-4">{{ $catName }} {{ $bName ? "($bName)" : "" }} </td>
                                <td class="text-end pe-4">{{ number_format($amt, 2) }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr class=" fw-bold ">
                            <td class="ps-3">รวม{{ $catName }}</td>
                            <td></td>
                            <td class="text-end pe-4">{{ number_format($catTotal, 2) }}</td>
                        </tr>
                        @php $cashTotal += $catTotal; @endphp
                    @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="2" class="text-center py-3">รวมยอดรับเงิน (เงินสด เงินโอน)</th>
                        <th class="text-end pe-4 fs-5">{{ number_format($cashTotal, 2) }}</th>
                    </tr>
                    <tr class="bg-primary text-white border-dark">
                        <th colspan="2" class="text-center py-3">รวมรายรับทั้งสิ้น (ยอดรับจริง + ยอดค้าง)</th>
                        <th class="text-end pe-4 fs-4">{{ number_format($cashTotal + $outstandingAmount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <style>
        @media print {
            .d-print-none {
                display: none !important;
            }

            .container {
                max-width: none;
                width: 100%;
                border: 0;
                padding: 0;
            }

            .table {
                border: 2px solid #000 !important;
            }
        }
    </style>
@endsection
