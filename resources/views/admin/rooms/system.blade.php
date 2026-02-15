@extends('admin.layout')

@section('content')
    <div class="container-fluid">

        <h2 class="mb-4 text-primary"><i class="fas fa-th-large"></i> ระบบจัดการห้องพัก (Smart Room System)</h2>

        {{-- 1. ส่วนค้นหาและกรอง (Filters) --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body bg-light">
                <form action="{{ route('admin.rooms.system') }}" method="GET" class="row g-3 align-items-end">

                    {{-- เลือกตึก --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold">🏢 ตึก</label>
                        <select name="building_id" class="form-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($buildings as $b)
                                <option value="{{ $b->id }}"
                                    {{ request('building_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- เลือกชั้น (ตัวอย่าง input ใส่เลขชั้น) --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold">📶 ชั้น</label>
                        <input type="number" name="floor" class="form-control" placeholder="เช่น 3"
                            value="{{ request('floor') }}">
                    </div>

                    {{-- เลือกสถานะ --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">📊 สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>🟢 ห้องว่าง
                                (Available)</option>
                            <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>🔴 มีคนเช่า
                                (Occupied)</option>
                            <option value="repair" {{ request('status') == 'repair' ? 'selected' : '' }}>🟡 ซ่อมแซม (Repair)
                            </option>
                        </select>
                    </div>

                    {{-- ค้นหาเลขห้อง --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">🔍 ค้นหา</label>
                        <input type="text" name="search" class="form-control" placeholder="ระบุเลขห้อง..."
                            value="{{ request('search') }}">
                    </div>

                    {{-- ปุ่มค้นหา --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. แสดงผลแบบ Grid Cards --}}
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 mb-4">
            @forelse($rooms as $room)
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 position-relative room-card status-{{ $room->status }}">
                        {{-- แถบสีสถานะด้านบน --}}
                        <div class="card-header text-white text-center fw-bold py-1 status-header-{{ $room->status }}">
                            @if ($room->status == 'available')
                                ว่าง
                            @elseif($room->status == 'occupied')
                                ไม่ว่าง
                            @else
                                ซ่อมแซม
                            @endif
                        </div>

                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            {{-- เลขห้อง --}}
                            <h3 class="card-title fw-bold text-dark mb-1">ห้อง {{ $room->room_number }}</h3>
                            <small class="text-muted">
                                {{ $room->building_name }} | {{ $room->room_type_name }}
                            </small>

                            {{-- ไอคอนประกอบ --}}
                            <div class="my-3 icon-status">
                                @if ($room->status == 'available')
                                    <i class="fas fa-door-open fa-3x text-success"></i>
                                @elseif($room->status == 'occupied')
                                    <i class="fas fa-user-check fa-3x text-danger"></i>
                                @else
                                    <i class="fas fa-tools fa-3x text-warning"></i>
                                @endif
                            </div>
                        </div>

                        {{-- ปุ่มกด (Action Buttons) --}}
                        <div class="card-footer bg-white border-0 text-center pb-3">
                            @if ($room->status == 'available')
                                {{-- ปุ่มเพิ่มผู้เช่า --}}
                                <a href="{{ route('admin.tenants.insert', ['room_id' => $room->id]) }}"
                                    class="btn btn-outline-success w-100 btn-sm">
                                    <i class="fas fa-plus-circle"></i> เพิ่มผู้เช่า
                                </a>
                            @elseif($room->status == 'occupied')
                                {{-- ปุ่มดูรายละเอียด --}}
                                {{-- สมมติว่ามี Tenant --}}
                                <button class="btn btn-outline-primary w-100 btn-sm"
                                    onclick="alert('ผู้เช่า: สมชาย (ตัวอย่าง)\nสัญญาหมด: 30/12/2026')">
                                    <i class="fas fa-info-circle"></i> ดูรายละเอียด
                                </button>
                            @else
                                <button class="btn btn-outline-warning w-100 btn-sm">
                                    <i class="fas fa-wrench"></i> แจ้งซ่อมเสร็จ
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-search fa-2x mb-3"></i><br>ไม่พบห้องตามเงื่อนไขที่ระบุ
                </div>
            @endforelse
        </div>

        {{-- 3. Pagination (ตัวเปลี่ยนหน้า) --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $rooms->links('pagination::bootstrap-4') }}
        </div>

    </div>

    {{-- CSS ตกแต่งเพิ่มเติม --}}
    <style>
        .room-card {
            transition: transform 0.2s;
            border-radius: 10px;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
        }

        /* สี Header ตามสถานะ */
        .status-header-available {
            background-color: #28a745;
        }

        .status-header-occupied {
            background-color: #dc3545;
        }

        .status-header-repair {
            background-color: #ffc107;
            color: #333 !important;
        }

        /* พื้นหลังการ์ดจางๆ */
        .status-available {
            background-color: #f0fff4;
        }

        .status-occupied {
            background-color: #fff5f5;
        }

        .status-repair {
            background-color: #fff9db;
        }
    </style>
@endsection
