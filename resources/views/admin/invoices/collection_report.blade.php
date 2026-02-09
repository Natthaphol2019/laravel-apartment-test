@extends('admin.layout')
@section('content')
    <div class="container-fluid py-4">
        {{-- ส่วนตัวกรอง (Checkbox) --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4 bg-white">
                <form method="GET" action="{{ route('admin.invoices.collectionReport') }}">
                    <div class="row g-4">
                        <div class="col-md-3 border-end">
                            <label class="small fw-bold text-muted mb-2">รอบเดือน</label>
                            <input type="month" name="billing_month" class="form-control" value="{{ $billing_month }}"
                                onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3 border-end">
                            <label class="small fw-bold text-muted mb-2">สถานะห้อง</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="status_filter[]" value="ว่าง"
                                        id="st_vacant" {{ in_array('ว่าง', $status_filter) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="st_vacant">ว่าง</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="status_filter[]" value="มีผู้เช่า"
                                        id="st_occupied" {{ in_array('มีผู้เช่า', $status_filter) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="st_occupied">เช่า</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">แสดงรายการค่าใช้จ่าย</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($allExpenseSettings as $expense)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="show_columns[]"
                                            value="{{ $expense->name }}" id="col_{{ $loop->index }}"
                                            {{ in_array($expense->name, $show_columns) ? 'checked' : '' }}>
                                        <label class="form-check-label small"
                                            for="col_{{ $loop->index }}">{{ $expense->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search me-1"></i>
                                ค้นหาข้อมูล</button>
                                {{-- วางปุ่มนี้ไว้ในส่วน Filter ของคุณ --}}
                            <a href="{{ route('admin.invoices.print_collection_report', request()->query()) }}" 
                            class="btn btn-danger px-4 rounded-pill shadow-sm" 
                            target="_blank">
                                <i class="bi bi-file-pdf me-2"></i>โหลด PDF รายงาน
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mb-4 pt-3">
            <h3 class="fw-bold text-dark mb-1">
                รายงานเก็บเงินประจำเดือน 
                <span class="text-primary">{{ $thai_month }}</span>  
                อาทิตย์ อพาร์ทเม้นท์
            </h3>
        </div>
        {{-- ส่วนตารางรายงาน --}}
        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-bordered table-hover align-middle mb-0 text-center border-dark"
                style="font-size: 0.9rem;">
                <thead class="table-dark">
                    <tr>
                        <th>ลำดับ</th>
                        <th>ห้อง</th>
                        <th>ว่าง</th>
                        <th>เช่า</th>
                        <th>ชื่อ-นามสกุล</th>
                        @foreach ($show_columns as $colName)
                            <th>{{ $colName }}</th>
                        @endforeach
                        <th>รวมเงิน</th>
                        <th>วันที่รับเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rooms as $index => $room)
                        @php
                            $tenant = $room->tenants->first();
                            $isOccupied = $room->status == 'มีผู้เช่า'; // เช็คจากสถานะห้อง
                            // 1. คำนวณยอดรวมรายบรรทัด เฉพาะคอลัมน์ที่เลือก
                            // ใช้ method 'only' เพื่อดึงเฉพาะ key ที่อยู่ใน $show_columns มาบวกกัน
                            $rowTotal = $room->expense_details->only($show_columns)->sum();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $room->room_number }}</td>
                            <td>{{ !$isOccupied ? '1' : '-' }}</td>
                            <td class="fw-bold">{{ $isOccupied ? '1' : '-' }}</td>
                            <td class="text-start ps-2">
                                @if ($isOccupied && $tenant)
                                    {{ $tenant->first_name }} {{ $tenant->last_name }}
                                @else
                                    <span class="text-muted italic small">ชื่อ ว่าง</span>
                                @endif
                            </td>
                            @foreach ($show_columns as $colName)
                                <td>{{ isset($room->expense_details[$colName]) ? number_format($room->expense_details[$colName], 2) : '-' }}
                                </td>
                            @endforeach
                            <td class="fw-bold text-primary">{{ $rowTotal > 0 ? number_format($rowTotal, 2) : '-' }}</td>
                            <td class="small">{{ $room->payment_date_display }}</td>
                        </tr>
                    @endforeach
                </tbody>
                {{-- ส่วนสรุปยอดรวม (tfoot) --}}
                <tfoot class="table-light fw-bold border-dark">
                    <tr>
                        <td colspan="2">รวม</td>
                        <td>{{ $rooms->where('status', 'ว่าง')->count() ?: '-' }}</td>
                        <td>{{ $rooms->where('status', 'มีผู้เช่า')->count() ?: '-' }}</td>
                        <td>{{ $rooms->where('status', 'มีผู้เช่า')->count() }} ราย</td>
                        @foreach ($show_columns as $colName)
                            <td>{{ number_format($rooms->sum(fn($r) => $r->expense_details[$colName] ?? 0), 0) }}</td>
                        @endforeach
                        <td class="text-primary">
                            @php
                                $grandTotal = $rooms->sum(function ($r) use ($show_columns) {
                                    return $r->expense_details->only($show_columns)->sum();
                                });
                            @endphp
                            {{ number_format($grandTotal, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
