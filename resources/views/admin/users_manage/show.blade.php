@extends('admin.layout')

@section('title', 'จัดการข้อมูลผู้ดูแลระบบ')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">จัดการข้อมูลผู้ดูแลระบบ</h3>
                <p class="text-muted small">จัดการรายชื่อผู้ใช้งานระบบ กำหนดตำแหน่ง และสถานะการใช้งาน</p>
            </div>
            {{-- เฉพาะผู้บริหารเท่านั้นที่เพิ่มแอดมินใหม่ได้ --}}
            @if(Auth::guard('admin')->user()->role == 'ผู้บริหาร')
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#insertAdminModal">
                <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้ดูแลใหม่
            </button>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Username</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ตำแหน่ง</th>
                            <th>สถานะ</th>
                            <th class="text-center px-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td class="px-4 fw-bold text-dark">{{ $admin->username }}</td>
                            <td>{{ $admin->firstname }} {{ $admin->lastname }}</td>
                            <td>
                                <span class="badge {{ $admin->role == 'ผู้บริหาร' ? 'bg-info' : 'bg-primary' }}">
                                    {{ $admin->role }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $admin->status == 'ใช้งาน' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $admin->status }}
                                </span>
                            </td>
                            <td class="text-center px-4">
                                {{-- ปุ่มแก้ไข --}}
                                <button class="btn btn-outline-warning btn-sm me-1" 
                                    onclick="openEditAdminModal({{ json_encode($admin) }})">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>
                                
                                {{-- ปุ่มลบ (ห้ามลบตัวเอง) --}}
                                @if(Auth::guard('admin')->id() !== $admin->id)
                                <form action="{{ route('admin.users_manage.delete', $admin->id) }}" method="POST" class="d-inline" id="delete-admin-{{ $admin->id }}">
                                    @csrf
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteAdmin({{ $admin->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INSERT ADMIN --}}
<div class="modal fade" id="insertAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">เพิ่มผู้ดูแลใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users_manage.insert') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="ชื่อสำหรับเข้าสู่ระบบ" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="รหัสผ่านสำหรับเข้าสู่ระบบ" required minlength="6">
                        </div>
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label text-secondary small">ยืนยันรหัสผ่านอีกครั้ง</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อจริง</label>
                            <input type="text" name="firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">นามสกุล</label>
                            <input type="text" name="lastname" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">ตำแหน่ง</label>
                            <select name="role" class="form-select">
                                <option value="พนักงาน">พนักงาน</option>
                                <option value="ผู้บริหาร">ผู้บริหาร</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT ADMIN --}}
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning ">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลผู้ดูแล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdminForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อจริง</label>
                            <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">นามสกุล</label>
                            <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ตำแหน่ง</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="พนักงาน">พนักงาน</option>
                                <option value="ผู้บริหาร">ผู้บริหาร</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">สถานะ</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="ใช้งาน">ใช้งาน</option>
                                <option value="ระงับใช้งาน">ระงับใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ชื่อผู้ใช้งาน (username)</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold ">เปลี่ยนรหัสผ่านใหม่ (ถ้ามี)</label>
                            <input type="password" name="password" class="form-control" placeholder="รหัสผ่านใหม่">
                        </div>
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label text-secondary small">ยืนยันรหัสผ่านอีกครั้ง</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                            class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
                            <div class="alert alert-info py-2 small mt-2">
                                <i class="bi bi-info-circle me-1"></i> เว้นว่างรหัสผ่านหากไม่ต้องการเปลี่ยน
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning px-4 fw-bold" onclick="confirmUpdate()" id="btnUpdateUser">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditAdminModal(admin) {
        document.getElementById('edit_username').value = admin.username;
        document.getElementById('edit_firstname').value = admin.firstname;
        document.getElementById('edit_lastname').value = admin.lastname;
        document.getElementById('edit_role').value = admin.role;
        document.getElementById('edit_status').value = admin.status;

        let url = "{{ route('admin.users_manage.update', ':id') }}";
        url = url.replace(':id', admin.id);
        document.getElementById('editAdminForm').action = url;

        new bootstrap.Modal(document.getElementById('editAdminModal')).show();
    }
    function confirmUpdate() {
        Swal.fire({
            title: 'ยืนยันการแก้ไข?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'ตกลง, แก้ไขเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btnUpdateUser');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
                document.getElementById('editAdminForm').submit();
            }
        });
    }

    function confirmDeleteAdmin(id) {
        Swal.fire({
            title: 'ยืนยันการลบผู้ใช้งาน?',
            text: "ข้อมูลผู้ใช้งานจะถูกลบออกจากระบบถาวร!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ตกลง, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-admin-' + id).submit();
            }
        });
    }
</script>
@endpush