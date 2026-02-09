@extends('admin.layout')

@section('content')
    <div class="container py-4">
        <h4 class="fw-bold mb-4 text-dark">รายการค้างชำระแยกตามห้อง</h4>

        {{-- ฟอร์มค้นหา --}}
        <form method="GET" action="{{ route('admin.payments.pendingInvoicesShow') }}" class="row g-2 mb-4">
            <div class="col-md-4">
                <label class="small text-muted mb-1">ค้นหาเลขห้อง</label>
                <input type="text" name="search_room" class="form-control shadow-sm" placeholder="เช่น 101..."
                    value="{{ $searchRoom }}">
            </div>
            
            {{-- เพิ่มช่องเลือกเดือน --}}
            <div class="col-md-4">
                <label class="small text-muted mb-1">รอบเดือนที่ค้างชำระ</label>
                <select name="filter_month" class="form-select shadow-sm">
                    <option value="">ทั้งหมด</option>
                    @foreach ($availableMonths as $m)
                        <option value="{{ $m->billing_month }}" {{ $filterMonth == $m->billing_month ? 'selected' : '' }}>
                            รอบเดือน {{ $m->thai_billing_month }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100 shadow-sm"><i class="bi bi-search me-1"></i> ค้นหา</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('admin.payments.pendingInvoicesShow') }}" class="btn btn-secondary w-100">
                    ล้างค่า
                </a>
            </div>
        </form>

        {{-- วนลูปกลุ่มห้อง (Group by Room Number) --}}
        @foreach ($pendingInvoices->groupBy('tenant.room.room_number') as $roomNumber => $invoices)
            <div class="card border-0 shadow-sm mb-4">
                {{-- ส่วนหัวห้องพัก --}}
                <div class="card-header bg-light border-0 py-3 d-flex align-items-center">
                    <h5 class="mb-0 fw-bold me-2"> เลขห้อง {{ $roomNumber }}</h5>
                    <span class="badge bg-warning text-dark small">รายละเอียด</span>
                </div>

                <div class="card-body p-3">
                    @foreach ($invoices as $inv)
                        <div class="card mb-3 border-light shadow-none" style="background-color: #f8f9fa;">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-x-circle-fill text-danger fs-5 me-2"></i>
                                            <h6 class="mb-0 fw-bold">ครบกำหนดชำระ : {{ $inv->thai_due_date }}</h6>
                                            {{-- เพิ่ม Badge แสดงสถานะชำระบางส่วน --}}
                                            @if ($inv->status == 'ชำระบางส่วน')
                                                <span class="badge bg-info text-dark mx-2 small">จ่ายแล้วบางส่วน</span>
                                            @endif
                                            <span class="badge bg-warning text-dark ms-2 small" style="cursor: pointer;"
                                                onclick="window.location='{{ route('admin.invoices.details', $inv->id) }}'">
                                                อ่านรายละเอียดบิล
                                            </span>
                                        </div>
                                        @if ($inv->status == 'ชำระบางส่วน')
                                            <p class="mb-1 text-muted small">
                                                ยอดเต็ม: {{ number_format($inv->total_amount, 2) }} |
                                                ชำระแล้ว: <span
                                                    class="text-success">{{ number_format($inv->total_paid, 2) }}</span>
                                            </p>
                                            <p class="mb-0 text-dark fw-bold">
                                                ยอดคงเหลือที่ต้องจ่าย: <span
                                                    class="text-danger fs-5">{{ number_format($inv->remaining_balance, 2) }}
                                                    บาท</span>
                                            </p>
                                        @else
                                            <p class="mb-0 text-muted">
                                                ยอดค้างชำระ: <span
                                                    class="fw-bold text-danger">{{ number_format($inv->total_amount, 2) }}
                                                    บาท</span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button class="btn btn-success px-4 shadow-sm"
                                            onclick="openPaymentModal({{ json_encode($inv) }})">
                                            <i class="bi bi-cash me-1"></i> จ่ายค่าห้อง <i
                                                class="bi bi-caret-down-fill small"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($pendingInvoices->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="mt-2 text-muted">ไม่พบรายการค้างชำระ</p>
            </div>
        @endif

    </div>

    {{-- MODAL PAYMENT --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.payments.insert') }}" method="POST" enctype="multipart/form-data"
                id="paymentForm">
                @csrf
                <div class="modal-content border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">บันทึกการชำระเงิน - ห้อง <span id="room_label"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="invoice_id" id="modal_invoice_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">ยอดเงินที่จ่ายจริง (บาท)</label>
                            <input type="number" name="amount_paid" id="modal_amount_paid"
                                class="form-control form-control-lg text-primary fw-bold" step="0.01" min="0"
                                oninput="validateAmount(this)"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">วันที่ชำระ</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ช่องทางการจ่าย</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="โอนผ่านธนาคาร">โอนผ่านธนาคาร</option>
                                <option value="เงินสด">เงินสด</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">แนบสลิป (ถ้ามี)</label>
                            <input type="file" name="slip_image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">หมายเหตุ</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-primary px-4"
                            onclick="confirmPayment()">ยืนยันการรับชำระ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function openPaymentModal(invoice) {
            document.getElementById('modal_invoice_id').value = invoice.id;
            document.getElementById('room_label').innerText = invoice.tenant.room.room_number;
            // ถ้ายอดคงเหลือไม่มี (บิลใหม่) ให้ใช้ยอดเต็ม total_amount แทน
            const debt = invoice.remaining_balance !== undefined ? invoice.remaining_balance : invoice.total_amount;
            // เพื่อป้องกันไม่ให้กรอกตัวเลขเกินยอดหนี้ที่มีอยู่จริง
            const amountInput = document.getElementById('modal_amount_paid');
            amountInput.value = Number(debt).toFixed(2);
            amountInput.max = debt; // กำหนดค่าสูงสุดที่ยอมรับได้
            var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            myModal.show();
        }
        // ป้องกันไม่ให้กรอกเกินยอดหนี้
        function validateAmount(input) {
            // ดึงค่าที่กรอกและค่าสูงสุดที่ยอมรับได้ (max) มาเปรียบเทียบ
            const val = parseFloat(input.value);
            const max = parseFloat(input.max);

            if (val > max) {
                // หากกรอกเกิน ให้ตั้งค่ากลับไปที่ยอดสูงสุดทันที
                input.value = max.toFixed(2);
                
                // แสดงการแจ้งเตือนสั้นๆ (Optional)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'warning',
                    title: 'ไม่สามารถรับชำระเกินยอดหนี้ได้'
                });
            }
        }
        function confirmPayment() {
            const room = document.getElementById('room_label').innerText;
            const amount = document.getElementById('modal_amount_paid').value;

            Swal.fire({
                title: 'ยืนยันการรับชำระเงิน?',
                html: `ห้อง: <b>${room}</b><br>ยอดเงิน: <b>${parseFloat(amount).toLocaleString()} บาท</b>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังบันทึก...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('paymentForm').submit();
                }
            });
        }
    </script>
@endpush
