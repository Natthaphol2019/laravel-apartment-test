@extends('admin.layout')

@section('title', 'จัดการข้อมูลห้องพัก')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">จัดการข้อมูลห้องพัก</h3>
                <p class="text-muted small">รายการเลขห้องและสถานะห้องพักทั้งหมด</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#insertRoomModal">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มห้องพักใหม่
            </button>
        </div>
    </div>

    {{-- ตัวกรองการค้นหา --}}
    <form method="GET" action="{{ route('admin.rooms.show') }}">
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">อาคาร</label>
                <select name="building_id" class="form-select">
                    <option value="">ทั้งหมด</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}" {{ request('building_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">ชั้น</label>
                <input type="number" name="floor_num" class="form-control"
                    value="{{ request('floor_num') }}" placeholder="เช่น 1" max="5" min="1">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">สถานะ</label>
                <select name="status" class="form-select">
                    <option value="">ทั้งหมด</option>
                    <option value="ว่าง" {{ request('status') == 'ว่าง' ? 'selected' : '' }}>ว่าง</option>
                    <option value="มีผู้เช่า" {{ request('status') == 'มีผู้เช่า' ? 'selected' : '' }}>มีผู้เช่า</option>
                    <option value="ซ่อมบำรุง" {{ request('status') == 'ซ่อมบำรุง' ? 'selected' : '' }}>ซ่อมบำรุง</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                <a href="{{ route('admin.rooms.show') }}" class="btn btn-secondary">ล้างค่า</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">เลขห้อง</th>
                            <th>อาคาร</th>
                            <th>ประเภท / ราคา</th>
                            <th>ชั้น</th>
                            <th>สถานะ</th>
                            <th class="text-center px-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $item)
                        <tr>
                            <td class="px-4 fw-bold text-dark">{{ $item->room_number }}</td>
                            <td>{{ $item->roomPrice->building->name }}</td>
                            <td>
                                <div class="small fw-bold">{{ $item->roomPrice->roomType->name }}</div>
                                <div class="text-primary small">{{ number_format($item->roomPrice->price, 2) }} บาท</div>
                            </td>
                            <td>{{ $item->roomPrice->floor_num }}</td>
                            <td>
                                <span class="badge {{ $item->status == 'ว่าง' ? 'bg-success' : ($item->status == 'มีผู้เช่า' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="text-center px-4">
                                <button class="btn btn-outline-warning btn-sm me-1" 
                                        onclick="openEditModal({{ $item->id }}, '{{ $item->room_number }}', {{ $item->room_price_id }}, '{{ $item->status }}')">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>
                                <form action="{{ route('admin.rooms.delete', $item->id) }}" method="POST" class="d-inline" id="delete-form-{{ $item->id }}">
                                    @csrf
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3" onclick="confirmDelete({{ $item->id }})">
                                        <i class="bi bi-trash"></i> ลบ
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-3">{{ $rooms->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INSERT --}}
<div class="modal fade" id="insertRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">เพิ่มห้องพักใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.rooms.insert') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">หมายเลขห้อง (Room Number)</label>
                        <input type="text" name="room_number" class="form-control" placeholder="เช่น 1101" maxlength="4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกประเภทและราคา (อ้างอิงจากตึกและชั้น)</label>
                        <select name="room_price_id" class="form-select" required>
                            <option value="">-- เลือกตึก/ประเภท/ราคา --</option>
                            @foreach($room_prices as $rp)
                                <option value="{{ $rp->id }}">
                                    {{ $rp->building->name }} - {{ $rp->roomType->name }} (ชั้น {{ $rp->floor_num }}) - {{ number_format($rp->price) }} บาท
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">สถานะเริ่มต้น</label>
                        <select name="status" class="form-select">
                            <option value="ว่าง">ว่าง</option>
                            <option value="มีผู้เช่า">มีผู้เช่า</option>
                            <option value="ซ่อมบำรุง">ซ่อมบำรุง</option>
                        </select>
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

{{-- MODAL EDIT --}}
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลห้องพัก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRoomForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">หมายเลขห้อง</label>
                        <input type="text" name="room_number" id="edit_room_number" class="form-control" maxlength="4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ประเภทและราคา</label>
                        <select name="room_price_id" id="edit_room_price_id" class="form-select" required>
                            @foreach($room_prices as $rp)
                                <option value="{{ $rp->id }}">
                                    {{ $rp->building->name }} - {{ $rp->roomType->name }} (ชั้น {{ $rp->floor_num }}) - {{ number_format($rp->price) }} บาท
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">สถานะ</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="ว่าง">ว่าง</option>
                            <option value="มีผู้เช่า">มีผู้เช่า</option>
                            <option value="ซ่อมบำรุง">ซ่อมบำรุง</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning px-4 fw-bold" onclick="confirmUpdate()" id="btnUpdateRoom">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(id, room_number, room_price_id, status) {
        document.getElementById('edit_room_number').value = room_number;
        document.getElementById('edit_room_price_id').value = room_price_id;
        document.getElementById('edit_status').value = status;

        let url = "{{ route('admin.rooms.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editRoomForm').action = url;

        new bootstrap.Modal(document.getElementById('editRoomModal')).show();
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
                const btn = document.getElementById('btnUpdateRoom');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
                document.getElementById('editRoomForm').submit();
            }
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบห้องพัก?',
            text: "ข้อมูลห้องพักและประวัติที่เกี่ยวข้องจะหายไป!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush