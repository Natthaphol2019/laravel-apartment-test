@extends('admin.layout')

@section('title', 'แก้ไขเลขมิเตอร์น้ำ-ไฟ')

@section('content')
    <div class="container-fluid ">
        {{-- ส่วนหัวฉบับปรับปรุงใหม่ในหน้า Show --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 bg-white rounded">
                <div class="row align-items-center g-3">
                    {{-- ฝั่งซ้าย --}}
                    <div class="col-md-4">
                        <h4 class="fw-bold mb-0 text-success"><i class="bi bi-journal-check me-2"></i>ข้อมูลมิเตอร์</h4>
                        <p class="text-muted small mb-0">รอบเดือน: <span
                                class="badge bg-success bg-opacity-10 text-success px-3">{{ $thai_date }}</span></p>
                    </div>

                    {{-- ฝั่งขวา: กลุ่มควบคุม (แนวนอน) --}}
                    <div class="col-md-8 text-end">
                        <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-3">

                            {{-- ปุ่มไปหน้าจดเพิ่ม --}}
                            <a href="{{ route('admin.meter_readings.insertForm') }}"
                                class="btn btn-outline-primary fw-bold shadow-sm  px-3">
                                <i class="bi bi-plus-circle"></i> จดมิเตอร์เพิ่ม
                            </a>

                            <div class="vr text-muted opacity-25 d-none d-md-block" style="height: 30px;"></div>

                            {{-- แก้ไขวันที่จดมิเตอร์ --}}
                            <div class="input-group " style="width: 270px;">
                                <span class="input-group-text bg-light text-muted small">วันที่บันทึก:</span>
                                <input type="date" name="reading_date" form="meterForm" id="reading_date"
                                    class="form-control" value="{{ $recordedDate }}" required>
                            </div>

                            {{-- เลือกเดือน --}}
                            <form method="GET" action="{{ route('admin.meter_readings.show') }}" class="d-flex gap-2">
                                <div class="input-group ">
                                    <span class="input-group-text bg-light border-end-0 text-primary">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>
                                    <input type="month" name="billing_month" class="form-control"
                                        value="{{ $billing_month }}" onchange="this.form.submit()">
                                </div>
                                <a href="{{ route('admin.meter_readings.show') }}" class="btn btn-secondary shadow-sm px-3">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- แท็บสลับ น้ำ-ไฟ --}}
        <ul class="nav nav-tabs nav-fill mb-0 border-0" id="meterTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-white py-3 rounded-top border-0" id="water-tab"
                    data-bs-toggle="tab" data-bs-target="#water-content" style="background-color: #5bc0de;">
                    <i class="bi bi-droplet-fill me-1"></i> รายการมิเตอร์น้ำ (ที่จดแล้ว)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-white py-3 rounded-top border-0" id="electric-tab" data-bs-toggle="tab"
                    data-bs-target="#electric-content" style="background-color: #d9534f;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> รายการมิเตอร์ไฟฟ้า (ที่จดแล้ว)
                </button>
            </li>
        </ul>

        <form action="{{ route('admin.meter_readings.update') }}" method="POST" id="meterForm">
            @csrf @method('PUT')
            <input type="hidden" name="billing_month" value="{{ $billing_month }}">

            <div class="tab-content border shadow-sm bg-white p-4 rounded-bottom" id="meterTabContent">
                <div class="tab-pane fade show active" id="water-content">
                    @include('admin.meter_readings._meter_table_edit', ['type' => 'water'])
                </div>
                <div class="tab-pane fade" id="electric-content">
                    @include('admin.meter_readings._meter_table_edit', ['type' => 'electric'])
                </div>
            </div>

            {{-- ตรวจสอบว่ามีข้อมูลห้องที่จดไปแล้วหรือไม่ --}}
            @if ($rooms->count() > 0)
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-warning px-5 py-3 shadow-lg fw-bold text-dark"
                        onclick="validateAndSubmit()">
                        <i class="bi bi-save2-fill me-2"></i> บันทึกการแก้ไขทั้งหมด
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        /**
         * ฟังก์ชันคำนวณหน่วยที่ใช้ (Units Used) จากแถวที่เลือก
         */
        function calculateFromRow(element) {
            const row = element.closest('tr');
            const prevInput = row.querySelector('.prev-input');
            const currentInput = row.querySelector('.current-input');
            const display = row.querySelector('.units-used');

            const prev = parseFloat(prevInput.value) || 0;
            const current = parseFloat(currentInput.value) || 0;

            let used = current - prev;

            if (currentInput.value === "") {
                display.innerText = "0";
                display.className = "units-used fw-bold text-muted";
            } else if (used < 0) {
                // กรณีเลขใหม่น้อยกว่าเลขเก่า จะแสดงตัวหนังสือสีแดง
                display.innerText = "เลขผิด!";
                display.className = "units-used fw-bold text-danger animate__animated animate__shakeX";
            } else {
                // กรณีปกติ แสดงหน่วยเป็นสีน้ำเงิน
                display.innerText = used;
                display.className = "units-used fw-bold text-primary";
            }
        }

        function validateAndSubmit() {
            const readingDate = document.getElementById('reading_date').value;
            const waterInputs = document.querySelectorAll('#water-content .current-input');
            const electricInputs = document.querySelectorAll('#electric-content .current-input');
            const errorDisplays = document.querySelectorAll('.text-danger.units-used');

            let isComplete = true;
            waterInputs.forEach(input => {
                if (!input.value) isComplete = false;
            });
            electricInputs.forEach(input => {
                if (!input.value) isComplete = false;
            });

            if (!readingDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกวันที่',
                    text: 'ต้องระบุวันที่บันทึกข้อมูลมิเตอร์'
                });
                return;
            }

            if (!isComplete || errorDisplays.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ถูกต้อง',
                    text: 'กรุณาตรวจสอบเลขมิเตอร์และแก้ไขจุดที่ผิดพลาดก่อนบันทึก'
                });
            } else {
                const thaiDate = new Date(readingDate).toLocaleDateString('th-TH', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                Swal.fire({
                    title: 'ยืนยันการแก้ไขข้อมูล?',
                    html: `วันที่จดมิเตอร์จะเป็น: <b>${thaiDate}</b><br>ข้อมูลที่แก้ไขจะถูกอัปเดตลงในระบบทันที`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'ตกลง, แก้ไขเลย',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังอัปเดต...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        document.getElementById('meterForm').submit();
                    }
                });
            }
        }
    </script>
@endpush
