@extends('admin.layout')
@section('title', 'แก้ไขใบแจ้งหนี้')
@section('content')
    <style>
        /* ขยายขนาดฟอนต์ให้ใหญ่ขึ้นตามคำขอ */
        #editInvoiceForm {
            font-size: 1rem;
        }

        #itemsTable input,
        #itemsTable select {
            font-size: 1rem;
            padding: 0.5rem;
            height: auto;
        }

        .table-primary-light {
            background-color: #f8fbff;
        }

        .fw-bold {
            font-weight: 700;
        }
    </style>

    <div class="container py-4">
        <form action="{{ route('admin.invoices.updateDetails', $invoice->id) }}" method="POST" id="editInvoiceForm">
            @csrf @method('PUT')
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- แถวบน: ชื่อบิล + ปุ่มหลัก -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">
                                <i class="bi bi-receipt-cutoff me-1"></i>
                                แก้ไขรายการใบแจ้งหนี้
                            </h4>
                            <div class="small text-muted">
                                เลขที่บิล: <span class="fw-semibold text-dark">#{{ $invoice->invoice_number }}</span>
                                • รอบเดือน: {{ $thai_billing_month }}
                            </div>
                        </div>

                        <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="addRow('standard')">
                                <i class="bi bi-plus-circle me-1"></i> รายการมาตรฐาน
                            </button>
                            {{-- <button type="button" class="btn btn-outline-secondary" onclick="addRow('custom')">
                                <i class="bi bi-pencil-square me-1"></i> รายการอื่น
                            </button> --}}
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- แถวล่าง: วันที่ออกบิล -->
                    <div class="row justify-content-end align-items-center mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>
                                วันที่ออกบิล
                            </label>
                            <input type="date" name="issue_date" class="form-control shadow-sm"
                                value="{{ old('issue_date', $invoice->issue_date) }}" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="35%">รายการเรียกเก็บ</th>
                                    <th width="10%">จำนวน</th>
                                    <th width="20%">ราคา/หน่วย</th>
                                    <th width="20%">จำนวนเงิน</th>
                                    <th width="5%">ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->details as $index => $item)
                                    @php
                                        // รายการระบบที่ห้ามเปลี่ยนชื่อ: ค่าเช่า, ค่าน้ำ, ค่าไฟ
                                        $isSystemItem =
                                            $item->name == 'ค่าเช่าห้อง' ||
                                            $item->name == 'ค่าน้ำ' ||
                                            $item->name == 'ค่าไฟ' ||
                                            $item->meter_reading_id;
                                        // รายการห้องพักที่ห้ามแก้จำนวน
                                        $isRoom = $item->name == 'ค่าเช่าห้อง';
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($item->tenant_expense_id && !$isSystemItem)
                                                {{-- รายการมาตรฐานจาก Dropdown --}}
                                                <select name="items[{{ $index }}][expense_id]"
                                                    class="form-select expense-select"
                                                    onchange="updatePriceFromSelect(this)">
                                                    @foreach ($expenses as $ex)
                                                        <option value="{{ $ex->id }}" data-price="{{ $ex->price }}"
                                                            {{ $item->tenant_expense_id == $ex->id ? 'selected' : '' }}>
                                                            {{ $ex->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="items[{{ $index }}][name]"
                                                    value="{{ $item->name }}">
                                            @else
                                                {{-- รายการอื่นๆ หรือรายการระบบ (ถ้าเป็นรายการอื่นๆ จะพิมพ์แก้ชื่อได้) --}}
                                                <input type="text" name="items[{{ $index }}][name]"
                                                    class="form-control @if ($isSystemItem) bg-light @endif"
                                                    value="{{ $item->name }}"
                                                    @if ($isSystemItem) readonly @endif required>
                                                <input type="hidden" name="items[{{ $index }}][expense_id]"
                                                    value="{{ $item->tenant_expense_id }}">
                                            @endif

                                            {{-- รักษาข้อมูลมิเตอร์ --}}
                                            <input type="hidden" name="items[{{ $index }}][meter_reading_id]"
                                                value="{{ $item->meter_reading_id }}">
                                            <input type="hidden" name="items[{{ $index }}][previous_unit]"
                                                value="{{ $item->previous_unit }}">
                                            <input type="hidden" name="items[{{ $index }}][current_unit]"
                                                value="{{ $item->current_unit }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]"
                                                min="0"
                                                class="form-control text-center qty @if ($isRoom || $item->meter_reading_id) bg-light @endif"
                                                value="{{ $item->quantity }}" step="any" oninput="calculateRow(this)"
                                                @if ($isRoom || $item->meter_reading_id) readonly @endif required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][price]" min="0"
                                                class="form-control text-end price" value="{{ $item->price_per_unit }}"
                                                step="0.01" oninput="calculateRow(this)" required>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control text-end border-0 bg-transparent row-subtotal fw-bold"
                                                value="{{ number_format($item->subtotal, 2) }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            @if (!$isSystemItem)
                                                <button type="button" class="btn btn-link text-danger p-0"
                                                    onclick="removeRow(this)">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h3 class="fw-bold">ยอดรวมสุทธิ: <span id="grandTotal"
                                class="text-primary">{{ number_format($invoice->total_amount, 2) }}</span> บาท</h3>
                        <hr>
                        <a href="{{ url()->previous() }}" class="btn btn-lg btn-secondary px-5">ยกเลิก</a>
                        <button type="submit" class="btn btn-lg btn-primary px-5 shadow">บันทึกการแก้ไข</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        // index แถว
        let rowIdx = {{ $invoice->details->count() }};

        // รายการค่าใช้จ่ายทั้งหมด (master)
        const masterExpenses = @json($expenses);

        /**
         * รีเฟรช dropdown ทั้งหมด
         * - ไม่แสดงรายการที่ถูกเลือกไปแล้ว
         * - ยกเว้นรายการที่แถวตัวเองเลือกอยู่
         */
        function refreshDropdowns() {

            // 1. หา expense_id ที่ถูกเลือกไปแล้วทั้งหมด
            const selectedIds = Array.from(
                    document.querySelectorAll('.expense-select')
                )
                .map(select => select.value)
                .filter(value => value !== "");

            // 2. วนทุก dropdown
            document.querySelectorAll('.expense-select').forEach(select => {

                const currentValue = select.value;

                // reset dropdown
                select.innerHTML = '<option value="">-- เลือกรายการมาตรฐาน --</option>';

                // เติม options ใหม่
                masterExpenses.forEach(ex => {
                    const exId = ex.id.toString();

                    // แสดงเฉพาะ
                    // - รายการที่ยังไม่ถูกเลือก
                    // - หรือรายการที่แถวนี้เลือกอยู่
                    if (!selectedIds.includes(exId) || exId === currentValue) {

                        const option = document.createElement('option');
                        option.value = exId;
                        option.textContent = ex.name;
                        option.dataset.price = ex.price;

                        if (exId === currentValue) {
                            option.selected = true;
                        }

                        select.appendChild(option);
                    }
                });
            });
        }

        /**
         * เพิ่มแถว (ปรับปรุงให้มี min="0")
         */
        function addRow(type) {
            let html = '';
            if (type === 'standard') {
                html = `
            <tr class="table-primary-light">
                <td>
                    <select name="items[${rowIdx}][expense_id]" class="form-select expense-select" onchange="updatePriceFromSelect(this)" required>
                        <option value="">-- เลือกรายการมาตรฐาน --</option>
                    </select>
                    <input type="hidden" name="items[${rowIdx}][name]" value="">
                </td>
                <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control text-center qty" value="1" min="0" oninput="calculateRow(this)" required></td>
                <td><input type="number" name="items[${rowIdx}][price]" class="form-control text-end price" value="0" step="0.01" min="0" oninput="calculateRow(this)" required></td>
                <td><input type="text" class="form-control text-end border-0 bg-transparent row-subtotal fw-bold" value="0.00" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="bi bi-trash fs-5"></i></button></td>
            </tr>`;
            } else {
                html = `
            <tr>
                <td><input type="text" name="items[${rowIdx}][name]" class="form-control" placeholder="ระบุรายการอื่นๆ" required></td>
                <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control text-center qty" value="1" min="0" oninput="calculateRow(this)" required></td>
                <td><input type="number" name="items[${rowIdx}][price]" class="form-control text-end price" value="0" step="0.01" min="0" oninput="calculateRow(this)" required></td>
                <td><input type="text" class="form-control text-end border-0 bg-transparent row-subtotal fw-bold" value="0.00" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="bi bi-trash fs-5"></i></button></td>
            </tr>`;
            }
            document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', html);
            rowIdx++;
            refreshDropdowns();
        }

        /**
         * คำนวณยอดต่อแถว (เพิ่มการตรวจสอบค่าติดลบ)
         */
        function calculateRow(input) {
            const row = input.closest('tr');
            const qtyInput = row.querySelector('.qty');
            const priceInput = row.querySelector('.price');

            // ตรวจสอบถ้าค่าที่กรอกน้อยกว่า 0 ให้เซ็ตเป็น 0 ทันที
            if (qtyInput.value < 0) qtyInput.value = 0;
            if (priceInput.value < 0) priceInput.value = 0;

            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = qty * price;

            row.querySelector('.row-subtotal').value =
                subtotal.toLocaleString('th-TH', {
                    minimumFractionDigits: 2
                });

            updateGrandTotal();
        }
        /**
         * เมื่อเลือก dropdown
         */
        function updatePriceFromSelect(select) {
            const row = select.closest('tr');
            const priceInput = row.querySelector('.price');
            const nameHidden = row.querySelector('input[type="hidden"]');

            if (select.value !== "") {
                const opt = select.options[select.selectedIndex];
                priceInput.value = opt.dataset.price;
                if (nameHidden) nameHidden.value = opt.text.trim();
            }

            calculateRow(priceInput);
            refreshDropdowns();
        }

        /**
         * รวมยอดทั้งหมด
         */
        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.row-subtotal').forEach(el => {
                total += parseFloat(el.value.replace(/,/g, '')) || 0;
            });

            document.getElementById('grandTotal').innerText =
                total.toLocaleString('th-TH', {
                    minimumFractionDigits: 2
                });
        }

        /**
         * ลบแถว
         */
        function removeRow(btn) {
            Swal.fire({
                title: 'ยืนยันการลบรายการนี้?',
                text: "รายการที่ลบจะหายไปจากตารางและยอดรวมจะถูกคำนวณใหม่ทันที",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // สีแดงสำหรับการลบ
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ตกลง, ลบรายการ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true // สลับให้ปุ่มยกเลิกอยู่ซ้ายตามมาตรฐาน UI
            }).then((result) => {
                if (result.isConfirmed) {
                    // ดำเนินการลบแถวออกจากตาราง
                    btn.closest('tr').remove();

                    // เรียกฟังก์ชันอัปเดตค่าต่างๆ ที่คุณทำไว้
                    updateGrandTotal();
                    refreshDropdowns();

                    // แสดง Toast แจ้งเตือนสำเร็จสั้นๆ (Optional)
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }
        // โหลดหน้าเสร็จ
        document.addEventListener('DOMContentLoaded', refreshDropdowns);
    </script>
@endpush
