@extends('admin.layout')

@section('title', 'บันทึกเลขมิเตอร์น้ำ-ไฟ')

@section('content')
<div class="container-fluid">

    {{-- ส่วนหัวและตัวเลือกเดือน --}}
{{-- ส่วนหัวฉบับปรับปรุงใหม่ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 bg-white rounded">
        <div class="row align-items-center g-3">
            {{-- ฝั่งซ้าย: ข้อมูลรอบปัจจุบัน --}}
            <div class="col-md-3">
                <h4 class="fw-bold mb-0 text-dark">บันทึก <span class="text-danger">เลขมิเตอร์</span></h4>
                <p class="text-muted small mb-0">รอบเดือน: <span class="badge bg-danger bg-opacity-10 text-danger px-3">{{ $thai_date }}</span></p>
            </div>

            {{-- ฝั่งขวา: กลุ่มควบคุม (แนวนอน) --}}
            <div class="col-md-9">
                <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-3">
                    {{-- ส่วนของปุ่ม "แก้ไขและอ่านบิล" จัดให้อยู่ในกลุ่มเดียวกัน --}}
                    <a href="{{ route('admin.meter_readings.show') }}" class="btn btn-outline-success fw-bold shadow-sm px-4">
                        <i class="bi bi-pencil-square me-1"></i> ข้อมูลมิเตอร์และแก้ไข
                    </a>
                    {{-- เลือกวันที่จดมิเตอร์ --}}
                    <div class="input-group " style="width: 270px;">
                        <span class="input-group-text bg-light text-muted small">วันที่จดมิเตอร์:</span>
                        {{-- ใช้ฟอร์มภายนอกร่วมกับ attribute 'form' --}}
                        <input type="date" name="reading_date" form="meterForm" id="reading_date" 
                               class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="vr text-muted opacity-25 d-none d-md-block" style="height: 30px;"></div>

                    {{-- ฟอร์มเลือกเดือน --}}
                    <form method="GET" action="{{ route('admin.meter_readings.insert') }}" class="d-flex gap-2">
                        <div class="input-group" >
                            <span class="input-group-text bg-light border-end-0 text-primary">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <input type="month" name="billing_month" class="form-control border-start-0 " 
                                value="{{ $billing_month }}" onchange="this.form.submit()">
                        </div>
                        <a href="{{ route('admin.meter_readings.insert') }}" class="btn btn-secondary shadow-sm px-3">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- แท็บสลับ น้ำ-ไฟ สีตามภาพตัวอย่าง --}}
    <ul class="nav nav-tabs nav-fill mb-0 border-0" id="meterTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold text-white py-3 rounded-top border-0" 
                    id="water-tab" data-bs-toggle="tab" data-bs-target="#water-content"
                    style="background-color: #5bc0de;">
                <i class="bi bi-droplet-fill me-1"></i> จดมิเตอร์ค่าน้ำ
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-white py-3 rounded-top border-0" 
                    id="electric-tab" data-bs-toggle="tab" data-bs-target="#electric-content"
                    style="background-color: #d9534f;">
                <i class="bi bi-lightning-charge-fill me-1"></i> จดมิเตอร์ค่าไฟฟ้า
            </button>
        </li>
    </ul>

    <form action="{{ route('admin.meter_readings.insert') }}" method="POST" id="meterForm">
        @csrf
        <input type="hidden" name="billing_month" value="{{ $billing_month }}">
        
        <div class="tab-content border shadow-sm bg-white p-4 rounded-bottom" id="meterTabContent">
            <div class="tab-pane fade show active" id="water-content">
                @include('admin.meter_readings._meter_table', ['type' => 'water'])
            </div>
            <div class="tab-pane fade" id="electric-content">
                @include('admin.meter_readings._meter_table', ['type' => 'electric'])
            </div>
        </div>
       {{-- ตรวจสอบว่ามีข้อมูลห้องที่จดไปแล้วหรือไม่ --}}
        @if($rooms->count() > 0)
            <div class="text-end mt-4">
                <button type="button" class="btn btn-dark px-5 py-3 shadow-lg fw-bold" onclick="validateAndSubmit()">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> บันทึกข้อมูลทั้งหมด
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
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
            display.innerText = "เลขผิด!";
            display.className = "units-used fw-bold text-danger";
        } else {
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
        waterInputs.forEach(input => { if (!input.value) isComplete = false; });
        electricInputs.forEach(input => { if (!input.value) isComplete = false; });

        if (!readingDate) {
            Swal.fire({ icon: 'warning', title: 'ยังไม่ได้เลือกวันที่', text: 'กรุณาระบุวันที่จดมิเตอร์ก่อนบันทึก' });
            return;
        }

        if (!isComplete || errorDisplays.length > 0) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบหรือเลขผิด', text: 'กรุณากรอกเลขมิเตอร์ให้ครบและถูกต้องทุกช่อง' });
        } else {
            const thaiDate = new Date(readingDate).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });

            Swal.fire({
                title: 'ยืนยันการบันทึกข้อมูล?',
                html: `วันที่จดมิเตอร์: <b>${thaiDate}</b><br>ข้อมูลจะถูกบันทึกและเตรียมออกบิลในขั้นตอนถัดไป`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'ตกลง, บันทึกเลย',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    document.getElementById('meterForm').submit();
                }
            });
        }
    }

</script>
@endpush