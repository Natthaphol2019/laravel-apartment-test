@extends('admin.layout')

@section('content')
    <div class="container-fluid py-4">
        <h4 class="fw-bold mb-4 text-primary"><i class="bi bi-clock-history me-2"></i>{{ $displayTitle }}</h4>
        {{-- ส่วนตัวกรองข้อมูล --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.payments.history') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">เลือกเดือน (เฉพาะเดือนที่มีการชำระ)</label>
                        <select name="filter_month" class="form-select" onchange="this.form.submit()">
                            {{-- ✅ ตัวเลือกสำหรับดูทั้งหมด --}}
                            <option value="" {{ $filterMonth == '' ? 'selected' : '' }}>แสดงประวัติทั้งหมด</option>

                            @foreach ($availableMonths as $m)
                                <option value="{{ $m->billing_month }}"
                                    {{ $filterMonth == $m->billing_month ? 'selected' : '' }}>
                                    รอบเดือน {{ $m->thai_billing_month }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-search me-1"></i>
                            ค้นหา</button>
                        <a href="{{ route('admin.payments.history') }}" class="btn btn-light border">ล้างค่า</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ตารางประวัติการชำระเงิน --}}
        <form method="GET" action="{{ route('admin.payments.history') }}" id="filterForm">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small">
                            <tr>
                                <th class="px-4 py-3">วันที่ชำระ</th>
                                <th>ห้อง</th>
                                <th class="text-end">ยอดเงิน</th>
                                <th class="text-center">ช่องทาง</th>
                                <th class="text-center"><i class="bi bi-image"></i></th>
                                <th>ผู้ชำระเงิน</th>
                                <th>ผู้รับเงิน</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                            {{-- ส่วน Filter ในตาราง --}}
                            <tr class="bg-white border-bottom">
                                <td class="px-4"></td>
                                <td><input type="text" name="filter_room"
                                        class="form-control form-control-sm border-0 bg-light" placeholder="เลขห้อง..."
                                        value="{{ $filterRoom }}" onchange="this.form.submit()"></td>
                                <td></td>
                                <td>
                                    <select name="filter_method" class="form-select form-select-sm border-0 bg-light"
                                        onchange="this.form.submit()">
                                        <option value="">ทั้งหมด</option>
                                        <option value="เงินสด" {{ $filterMethod == 'เงินสด' ? 'selected' : '' }}>เงินสด
                                        </option>
                                        <option value="โอนผ่านธนาคาร"
                                            {{ $filterMethod == 'โอนผ่านธนาคาร' ? 'selected' : '' }}>เงินโอน</option>
                                    </select>
                                </td>
                                <td></td>
                                <td><input type="text" name="filter_payer"
                                        class="form-control form-control-sm border-0 bg-light" placeholder="ชื่อผู้ชำระ..."
                                        value="{{ $filterPayer }}" onchange="this.form.submit()"></td>
                                <td><input type="text" name="filter_receiver"
                                        class="form-control form-control-sm border-0 bg-light" placeholder="ชื่อผู้รับ..."
                                        value="{{ $filterReceiver }}" onchange="this.form.submit()"></td>
                                <td>
                                    <select name="filter_status"
                                        class="form-select form-select-sm border-0 bg-light text-center"
                                        onchange="this.form.submit()">
                                        <option value="">ทั้งหมด</option>
                                        <option value="active" {{ $filterStatus == 'active' ? 'selected' : '' }}>ปกติ
                                        </option>
                                        <option value="void" {{ $filterStatus == 'void' ? 'selected' : '' }}>ยกเลิก
                                        </option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $pay)
                                <tr onclick="viewPaymentDetail({{ $pay->id }})" style="cursor: pointer;"
                                    class="{{ $pay->status === 'void' ? 'table-danger' : '' }}">
                                    <td class="px-4 small fw-bold">{{ $pay->thai_payment_date }}</td>
                                    <td><span class="badge bg-dark">{{ $pay->invoice->tenant->room->room_number }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">{{ number_format($pay->amount_paid, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($pay->payment_method == 'เงินสด')
                                            <span class="badge bg-success rounded-pill px-3">
                                                <i class="bi bi-cash me-1"></i> เงินสด
                                            </span>
                                        @else
                                            <span class="badge bg-primary rounded-pill px-3">
                                                <i class="bi bi-bank me-1"></i> {{ $pay->payment_method }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {!! $pay->slip_image
                                            ? '<i class="bi bi-check-circle-fill text-success"></i>'
                                            : '<i class="bi bi-dash text-muted"></i>' !!}
                                    </td>
                                    <td><small class="text-dark fw-bold">{{ $pay->invoice->tenant->first_name }}
                                            {{ $pay->invoice->tenant->last_name }}</small></td>
                                    <td><small class="text-muted">{{ $pay->admin->firstname ?? 'System' }}
                                            {{ $pay->admin->lastname ?? '' }}</small></td>
                                    <td class="text-center">
                                        {{-- สัญลักษณ์สถานะ --}}
                                        @if ($pay->status === 'active')
                                            <span
                                                class="badge rounded-pill bg-success-subtle text-success border border-success px-2">
                                                <i class="bi bi-check-circle-fill"></i> ปกติ
                                            </span>
                                        @else
                                            <span
                                                class="badge rounded-pill bg-danger-subtle text-danger border border-danger px-2">
                                                <i class="bi bi-x-circle-fill"></i> ยกเลิก
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            {{-- ปุ่มแก้ไข --}}
                                            <button type="button" class="btn btn-sm btn-light border text-warning"
                                                onclick="event.stopPropagation(); editPayment({{ json_encode($pay) }})"
                                                title="แก้ไขข้อมูล">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            {{-- ปุ่มยกเลิก (แสดงเฉพาะรายการที่ยัง active) --}}
                                            @if ($pay->status === 'active')
                                                <button type="button" class="btn btn-sm btn-light border text-danger"
                                                    onclick="event.stopPropagation(); confirmVoid({{ $pay->id }}, '{{ $pay->invoice->tenant->room->room_number }}')"
                                                    title="ยกเลิกรายการนี้">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-sm btn-light border text-muted disabled"
                                                    title="ยกเลิกแล้ว">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">{{ $history->appends(request()->query())->links() }}</div>
            </div>
        </form>
    </div>

    {{-- Modal รายละเอียดการชำระเงิน (Detail Modal) --}}
    <div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>รายละเอียดการรับเงิน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalContent">
                    {{-- เนื้อหาจะถูกเติมด้วย JavaScript --}}
                </div>
            </div>
        </div>
    </div>
    {{-- Modal แก้ไขข้อมูล (Edit Modal) --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" id="editForm" enctype="multipart/form-data"
                class="modal-content border-0 shadow">
                @csrf @method('PUT')
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลการรับเงิน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ช่องทางการชำระเงิน</label>
                        <select name="payment_method" id="edit_method" class="form-select">
                            <option value="เงินสด">เงินสด</option>
                            <option value="โอนผ่านธนาคาร">โอนผ่านธนาคาร</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">หลักฐานการโอน (สลิป)</label>
                        {{-- ✅ ส่วนแสดงรูปภาพเดิม --}}
                        <div id="edit_slip_preview_container" class="mb-3 text-center" style="display: none;">
                            <img id="edit_slip_img" src="" class="img-fluid rounded border shadow-sm mb-2"
                                style="max-height: 200px;">
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i><b>คำเตือน:</b> หากคุณเลือกไฟล์ใหม่
                                ระบบจะลบไฟล์เดิมทิ้งทันที
                            </div>
                        </div>
                        <input type="file" name="slip_image" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">หมายเหตุ</label>
                        <textarea name="note" id="edit_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border btn-sm px-4"
                        data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
    {{-- ฟอร์มสำหรับการส่งคำสั่ง Void (Hidden Form) --}}
    <form id="void-form" action="" method="POST" style="display: none;">
        @csrf
        @method('PUT')
    </form>
@endsection

@push('scripts')
    <script>
        function viewPaymentDetail(id) {
            const modal = new bootstrap.Modal(document.getElementById('paymentDetailModal'));
            const content = document.getElementById('modalContent');

            content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            fetch(`/admin/payments/history/getPaymentDetail/${id}`) // ตรวจสอบ Route ให้ตรง
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = `
                <div class="text-center mb-4">
                    <div class="display-6 fw-bold text-success mb-0">${data.amount} ฿</div>
                </div>
                <div class="row g-3 small">
                    <div class="col-6 text-muted">เลขห้อง:</div><div class="col-6 text-end fw-bold">${data.room}</div>
                    <div class="col-6 text-muted">วันที่ชำระ:</div><div class="col-6 text-end fw-bold">${data.date}</div>
                    <div class="col-6 text-muted">เวลาบันทึก:</div><div class="col-6 text-end fw-bold">${data.time}</div>
                    <div class="col-6 text-muted">ช่องทาง:</div><div class="col-6 text-end fw-bold text-primary">${data.method}</div>
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-6 text-muted">ชื่อผู้เช่า:</div><div class="col-6 text-end fw-bold">${data.tenant}</div>
                    <div class="col-6 text-muted">ผู้รับเงิน:</div><div class="col-6 text-end fw-bold">${data.receiver}</div>
                    <div class="col-12 mt-3 p-3 bg-light rounded">
                        <label class="text-muted d-block mb-1">หมายเหตุ:</label>
                        <p class="mb-0">${data.note}</p>
                    </div>
                    ${data.slip ? `
                                                                            <div class="col-12 mt-3 text-center">
                                                                                <label class="text-muted d-block mb-2">หลักฐานการชำระเงิน:</label>
                                                                                <img src="${data.slip}" class="img-fluid rounded shadow-sm border w-100" style="max-height: 300px; object-fit: contain;">
                                                                            </div>
                                                                        ` : ''}
                    <div class="d-flex justify-content-end mt-4">
                        <div class="text-muted italic small" style="font-size: 0.75rem;">อ้างอิงบิลเลขที่: #${data.invoice_no}</div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <button class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            `;
                });
        }

        function editPayment(pay) {
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            const form = document.getElementById('editForm');
            const previewContainer = document.getElementById('edit_slip_preview_container');
            const previewImg = document.getElementById('edit_slip_img');

            form.action = `/admin/payments/history/update/${pay.id}`;
            document.getElementById('edit_method').value = pay.payment_method;
            document.getElementById('edit_note').value = pay.note || '';

            // ✅ ตรวจสอบและแสดงรูปสลิป
            if (pay.slip_image) {
                previewContainer.style.display = 'block';
                previewImg.src = `/storage/${pay.slip_image}`;
            } else {
                previewContainer.style.display = 'none';
                previewImg.src = '';
            }

            modal.show();
        }

        function confirmVoid(id, room) {
            Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: `คุณต้องการยกเลิกรายการชำระเงินของห้อง ${room} ใช่หรือไม่? ยอดหนี้จะถูกตีกลับเป็นสถานะค้างชำระ`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i>ยืนยัน ยกเลิกรายการ',
                cancelButtonText: 'กลับไป',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง Loading ระหว่างทำงาน
                    Swal.fire({
                        title: 'กำลังดำเนินการ...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const form = document.getElementById('void-form');
                    form.action = `/admin/payments/history/void/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endpush
