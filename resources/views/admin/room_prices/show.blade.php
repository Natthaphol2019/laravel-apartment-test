@extends('admin.layout')

@section('title', 'จัดการราคาห้องพัก')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">จัดการราคาห้องพัก</h3>
                <p class="text-muted small">กำหนดราคาตามประเภทห้องและอาคาร</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#insertPriceModal">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มราคาห้อง
            </button>
        </div>
    </div>
    {{-- ตัวกรองการค้นหา --}}
    <form method="GET" action="{{ route('admin.room_prices.show') }}">
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">อาคาร</label>
                <select name="building_id" class="form-select">
                    <option value="">ทั้งหมด</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}" {{ request('building_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">ประเภทห้อง</label>
                <select name="room_type_id" class="form-select">
                    <option value="">ทั้งหมด</option>
                    @foreach($room_types as $rt)
                        <option value="{{ $rt->id }}" {{ request('room_type_id') == $rt->id ? 'selected' : '' }}>
                            {{ $rt->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">ชั้น</label>
                <input type="number" name="floor_num" class="form-control"
                    value="{{ request('floor_num') }}" placeholder="เช่น 1" max="5" min="1">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> ค้นหา
                </button>

                <a href="{{ route('admin.room_prices.show') }}" class="btn btn-secondary">
                    ล้างค่า
                </a>
            </div>
        </div>
    </form>

    {{-- ตารางแสดงผล --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">รูปภาพ</th>
                            <th>อาคาร </th>
                            <th>ประเภทห้อง</th>
                            <th>ชั้น</th>
                            <th class="text-end">ราคา</th>
                            <th class="text-center px-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($room_prices as $item)
                        <tr>
                            <td class="px-4">
                                @if(!empty($item->color_code))
                                    <img
                                        src="{{ asset('storage/' . $item->color_code) }}"
                                        class="rounded shadow-sm"
                                        style="width:60px;height:40px;object-fit:cover;"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                    >
                                    <div class="bg-light rounded text-center"
                                        style="width:60px;height:40px;line-height:40px;font-size:10px;display:none;">
                                        ไม่มีรูป
                                    </div>
                                @else
                                    <div class="bg-light rounded text-center"
                                        style="width:60px;height:40px;line-height:40px;font-size:10px;">
                                        ไม่มีรูป
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->building->name }}</div>
                            </td>
                            <td>
                                <div>{{ $item->roomType->name }}</div>
                            </td>
                            <td>{{ $item->floor_num }}</td>
                            <td class="fw-bold text-primary text-end">{{ number_format($item->price, 2) }}</td>
                            <td class="text-center px-4">
                                {{-- ปุ่มแก้ไข --}}
                                <button class="btn btn-outline-warning btn-sm me-1" 
                                        onclick="openEditModal({{ $item->id }}, {{ $item->building_id }}, {{ $item->room_type_id }}, {{ $item->floor_num }}, {{ $item->price }})">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>
                                {{-- ปรับปุ่มลบให้เรียกใช้ฟังก์ชัน confirmDelete --}}
                                <form action="{{ route('admin.room_prices.delete', $item->id) }}" method="POST" class="d-inline" id="delete-form-{{ $item->id }}">
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
                {{ $room_prices->links() }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL INSERT --}}
<div class="modal fade" id="insertPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">กำหนดราคาห้องพักใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            {{-- สำคัญ: ต้องมี enctype="multipart/form-data" เพื่อส่งรูปภาพ --}}
            <form action="{{ route('admin.room_prices.insert') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลือกอาคาร</label>
                            <select name="building_id" class="form-select" required>
                                <option value="">-- เลือกตึก --</option>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลือกประเภทห้อง</label>
                            <select name="room_type_id" class="form-select" required>
                                <option value="">-- เลือกประเภท --</option>
                                @foreach($room_types as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชั้นที่ (Floor)</label>
                            <input type="number" name="floor_num" min="1" max="5" class="form-control" placeholder="ระบุชั้น">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ราคา (Price)</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">อัปโหลดรูปภาพสีประจำประเภท</label>
                            <input type="file" name="color_code" class="form-control" accept="image/*" required>
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
{{-- MODAL EDIT --}}
<div class="modal fade" id="editPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">แก้ไขราคาห้องพัก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPriceForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลือกอาคาร</label>
                            <select name="building_id" id="edit_building_id" class="form-select" required>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เลือกประเภทห้อง</label>
                            <select name="room_type_id" id="edit_room_type_id" class="form-select" required>
                                @foreach($room_types as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชั้นที่ (Floor)</label>
                            <input type="number" name="floor_num" id="edit_floor_num" min="1" max="5" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ราคา (Price)</label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">เปลี่ยนรูปภาพสีประจำประเภท (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                            <input type="file" name="color_code" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning px-4 fw-bold" onclick="confirmUpdate()" id="btnUpdate">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function openEditModal(id, building_id, room_type_id, floor_num, price) {
        // 1. เติมข้อมูลลงในฟิลด์ต่างๆ
        document.getElementById('edit_building_id').value = building_id;
        document.getElementById('edit_room_type_id').value = room_type_id;
        document.getElementById('edit_floor_num').value = floor_num;
        document.getElementById('edit_price').value = price;

        // 2. ตั้งค่า Action URL ให้ตรงกับ ID
        let url = "{{ route('admin.room_prices.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editPriceForm').action = url;

        // 3. เปิด Modal
        var editModal = new bootstrap.Modal(document.getElementById('editPriceModal'));
        editModal.show();
    }

    function confirmUpdate() {
        Swal.fire({
            title: 'ยืนยันการแก้ไข?',
            text: "ข้อมูลราคาห้องพักจะถูกเปลี่ยนแปลง",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ตกลง, แก้ไขเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                    const btn = document.getElementById('btnUpdate');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...';
                document.getElementById('editPriceForm').submit();
            }
        })
    }

        // SweetAlert ยืนยันการลบ
        function confirmDelete(id) {
            Swal.fire({
                title: 'ยืนยันการลบข้อมูล?',
                text: "หากลบแล้วข้อมูลราคาประเภทห้องนี้จะไม่สามารถกู้คืนได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // ส่งฟอร์มลบตาม ID ที่กำหนด
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
</script>
@endpush