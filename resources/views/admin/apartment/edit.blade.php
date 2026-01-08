@extends('admin.layout')

@section('title', 'แก้ไขข้อมูลอพาร์ทเม้นท์')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลอพาร์ทเม้นท์</h5>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('admin.apartment.update', $apartment->id) }}" method="POST" id="editApartmentForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">ชื่ออพาร์ทเม้นท์</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $apartment->name) }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">เลขที่</label>
                                <input type="text" name="address_no" class="form-control" value="{{ old('address_no', $apartment->address_no) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">หมู่ที่</label>
                                <input type="text" name="moo" class="form-control" value="{{ old('moo', $apartment->moo) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">รหัสไปรษณีย์</label>
                                <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $apartment->postal_code) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">ตำบล/แขวง</label>
                                <input type="text" name="sub_district" class="form-control" value="{{ old('sub_district', $apartment->sub_district) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">อำเภอ/เขต</label>
                                <input type="text" name="district" class="form-control" value="{{ old('district', $apartment->district) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">จังหวัด</label>
                                <input type="text" name="province" class="form-control" value="{{ old('province', $apartment->province) }}">
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-primary">เบอร์โทรศัพท์ติดต่อ</label>
                                <input type="text" name="phone" class="form-control " value="{{ old('phone', $apartment->phone) }}">
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.apartment.show') }}" class="btn btn-light px-4">ยกเลิก</a>
                            <button type="button" id="btnSubmitUpdate" class="btn btn-primary px-5" onclick="confirmUpdate()">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        function confirmUpdate() {
            Swal.fire({
                title: 'ยืนยันการแก้ไข?',
                text: "คุณต้องการบันทึกการเปลี่ยนแปลงข้อมูลอพาร์ทเม้นท์ใช่หรือไม่",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, บันทึกเลย!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true // ให้ปุ่มยกเลิกอยู่ซ้าย ปุ่มยืนยันอยู่ขวา
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = document.getElementById('btnSubmitUpdate');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...';
                    // ถ้ากดยืนยัน ให้ส่งฟอร์ม
                    document.getElementById('editApartmentForm').submit();
                }
            })
        }
    </script>
@endpush