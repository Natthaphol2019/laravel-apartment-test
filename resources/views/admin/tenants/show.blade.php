@extends('admin.layout')

@section('title', 'จัดการข้อมูลผู้เช่า')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">จัดการข้อมูลผู้เช่า</h3>
                <p class="text-muted small">บันทึกและตรวจสอบรายละเอียดผู้พักอาศัย</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#insertTenantModal">
                <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้เช่าใหม่
            </button>
        </div>
    </div>

    {{-- ตัวกรองการค้นหา --}}
    <form method="GET" action="{{ route('admin.tenants.show') }}">
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">ค้นหา</label>
                <input type="text" name="search" class="form-control" 
                    value="{{ request('search') }}" 
                    placeholder="ชื่อ, นามสกุล, เลขบัตร หรือ หมายเลขห้อง...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">สถานะ</label>
                <select name="status" class="form-select">
                    <option value="">ทั้งหมด</option>
                    <option value="กำลังใช้งาน" {{ request('status') == 'กำลังใช้งาน' ? 'selected' : '' }}>กำลังใช้งาน</option>
                    <option value="สิ้นสุดสัญญา" {{ request('status') == 'สิ้นสุดสัญญา' ? 'selected' : '' }}>สิ้นสุดสัญญา</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                <a href="{{ route('admin.tenants.show') }}" class="btn btn-secondary">ล้างค่า</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">ห้อง</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>วันที่เริ่มเช่า</th>
                            <th>ที่จอดรถ</th>
                            <th>สถานะ</th>
                            <th class="text-center px-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $item)
                        <tr>
                            <td class="px-4 fw-bold text-primary">{{ $item->room->room_number }}</td>
                            <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ date('d/m/Y', strtotime($item->start_date)) }}</td>
                            <td>
                                <i class="bi {{ $item->has_parking ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }}"></i>
                            </td>
                            <td>
                                <span class="badge {{ $item->status == 'กำลังใช้งาน' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="text-center px-4">
                                @if($item->status == 'กำลังใช้งาน')
                                    {{-- ปุ่มสิ้นสุดสัญญา (เฉพาะสถานะกำลังใช้งาน) --}}
                                    <form action="{{ route('admin.tenants.updateStatusTenant', $item->id) }}" method="POST" class="d-inline" id="terminate-form-{{ $item->id }}">
                                        @csrf @method('PUT')
                                        <button type="button" class="btn btn-outline-dark btn-sm me-1" onclick="confirmTerminate({{ $item->id }}, '{{ $item->room->room_number }}')">
                                            <i class="bi bi-door-closed"></i> สิ้นสุดสัญญา
                                        </button>
                                    </form>

                                    {{-- ปุ่มแก้ไข --}}
                                    <button class="btn btn-outline-warning btn-sm me-1" onclick="openEditModal({{ json_encode($item) }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    {{-- ปุ่มลบ --}}
                                    <form action="{{ route('admin.tenants.delete', $item->id) }}" method="POST" class="d-inline" id="delete-form-{{ $item->id }}">
                                        @csrf 
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete({{ $item->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- กรณีสิ้นสุดสัญญาแล้ว แสดงข้อความหรือไอคอนล็อก --}}
                                    <span class="text-muted small"><i class="bi bi-lock-fill"></i> ปิดรายการแล้ว</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-3">{{ $tenants->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INSERT --}}
<div class="modal fade" id="insertTenantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">ลงทะเบียนผู้เช่าใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tenants.insert') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2">ข้อมูลเบื้องต้น</h6></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">เลือกห้องพัก *</label>
                            <select name="room_id" class="form-select" required>
                                <option value="">-- เลือกห้องที่ว่าง --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_number }} ({{ $room->roomPrice->building->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลขบัตรประชาชน (ใช้เป็นรหัสผ่าน) *</label>
                            <input type="text" name="id_card" class="form-control" maxlength="13" placeholder="เลข 13 หลัก" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อ *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">นามสกุล *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">จำนวนผู้อยู่อาศัย</label>
                            <input type="number" name="resident_count" class="form-control" value="1" min="1">
                        </div>

                        {{-- เพิ่มส่วนข้อมูลที่อยู่ตาม Schema --}}
                        <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">ข้อมูลที่อยู่ (ตามบัตรประชาชน/ทะเบียนบ้าน)</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">เลขที่</label>
                            <input type="text" name="address_no" class="form-control" placeholder="เช่น 123/4">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">หมู่ที่</label>
                            <input type="text" name="moo" class="form-control" maxlength="3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">ตำบล/แขวง</label>
                            <input type="text" name="sub_district" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">อำเภอ/เขต</label>
                            <input type="text" name="district" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">จังหวัด</label>
                            <input type="text" name="province" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" class="form-control" maxlength="5">
                        </div>

                        <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">เงื่อนไขและสัญญา</h6></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">วันที่เริ่มเช่า *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ไฟล์สัญญาเช่า (.pdf, .jpg) *</label>
                            <input type="file" name="rental_contract" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="has_parking" value="1" id="parkingCheck">
                                <label class="form-check-label fw-bold" for="parkingCheck">ใช้บริการที่จอดรถ (มีค่าบริการเพิ่มเติม)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">ยืนยันการลงทะเบียน</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editTenantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลผู้เช่า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTenantForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12"><h6 class="fw-bold text-dark border-bottom pb-2">ข้อมูลพื้นฐาน</h6></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลขบัตรประชาชน </label>
                            <input type="text" name="id_card" id="edit_id_card" class="form-control" maxlength="13" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">แก้ไขรหัสผ่าน (เว้นว่างไว้หากไม่เปลี่ยนรหัส)</label>
                            <input type="text" name="password" id="edit_password" class="form-control" minlength="6" placeholder="เว้นว่างไว้หากไม่เปลี่ยนรหัส">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อ</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">นามสกุล</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">จำนวนผู้อยู่อาศัย</label>
                            <input type="number" name="resident_count" id="edit_resident_count" class="form-control" min="1">
                        </div>

                        <div class="col-12 mt-4"><h6 class="fw-bold text-dark border-bottom pb-2">ข้อมูลที่อยู่ (ตามทะเบียนบ้าน)</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">เลขที่</label>
                            <input type="text" name="address_no" id="edit_address_no" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">หมู่ที่</label>
                            <input type="text" name="moo" id="edit_moo" class="form-control" maxlength="3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">ตำบล/แขวง</label>
                            <input type="text" name="sub_district" id="edit_sub_district" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">อำเภอ/เขต</label>
                            <input type="text" name="district" id="edit_district" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">จังหวัด</label>
                            <input type="text" name="province" id="edit_province" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" id="edit_postal_code" class="form-control" maxlength="5">
                        </div>

                        <div class="col-12 mt-4"><h6 class="fw-bold text-dark border-bottom pb-2">การติดต่อและเอกสาร</h6></div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">เปลี่ยนไฟล์สัญญา (PDF/รูป)</label>
                            <input type="file" name="rental_contract" class="form-control" accept=".pdf,.jpg,.jpeg,.png" placeholder="อัพโหลดไฟล์ใหม่เพื่อเปลี่ยนสัญญา">
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="has_parking" value="1" id="edit_has_parking">
                                <label class="form-check-label fw-bold" for="edit_has_parking">ใช้บริการที่จอดรถ</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning px-4 fw-bold shadow-sm" onclick="confirmUpdate()" id="btnUpdateTenant">
                        <i class="bi bi-save me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(tenant) {
        document.getElementById('edit_first_name').value = tenant.first_name;
        document.getElementById('edit_last_name').value = tenant.last_name;
        document.getElementById('edit_id_card').value = tenant.id_card;
        document.getElementById('edit_resident_count').value = tenant.resident_count;
        
        
        // เติมข้อมูลที่อยู่
        document.getElementById('edit_address_no').value = tenant.address_no || '';
        document.getElementById('edit_moo').value = tenant.moo || '';
        document.getElementById('edit_sub_district').value = tenant.sub_district || '';
        document.getElementById('edit_district').value = tenant.district || '';
        document.getElementById('edit_province').value = tenant.province || '';
        document.getElementById('edit_postal_code').value = tenant.postal_code || '';
        document.getElementById('edit_phone').value = tenant.phone || '';

        // จัดการสถานะ Checkbox ที่จอดรถ
        document.getElementById('edit_has_parking').checked = tenant.has_parking == 1;

        // เปลี่ยน Action URL
        let url = "{{ route('admin.tenants.update', ':id') }}";
        url = url.replace(':id', tenant.id);
        document.getElementById('editTenantForm').action = url;

        new bootstrap.Modal(document.getElementById('editTenantModal')).show();
    }

    function confirmUpdate() {
        Swal.fire({
            title: 'ยืนยันการแก้ไขข้อมูล?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editTenantForm').submit();
            }
        });
    }
    function confirmTerminate(id, roomNumber) {
        Swal.fire({
            title: 'ยืนยันการสิ้นสุดสัญญา?',
            text: "ห้อง " + roomNumber + " จะถูกเปลี่ยนสถานะเป็นว่าง และผู้เช่ารายนี้จะไม่สามารถแก้ไขข้อมูลได้อีก!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, สิ้นสุดสัญญา!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // แสดง Loading ระหว่างทำงาน
                Swal.fire({
                    title: 'กำลังดำเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('terminate-form-' + id).submit();
            }
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'ลบข้อมูลผู้เช่า?',
            text: "ข้อมูลนี้จะหายไปจากระบบทันที!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบเลย'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

</script>
@endpush