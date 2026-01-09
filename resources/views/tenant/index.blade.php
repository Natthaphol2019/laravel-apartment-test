@extends('tenant.layout')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary">{{ $apartment->name ?? 'ระบบจัดการหอพัก' }}</h2>
            <p class="text-muted mb-0">ยินดีต้อนรับ, คุณ {{ $tenant->first_name }} {{ $tenant->last_name }}</p>
        </div>
        <div>
            <span class="badge bg-success rounded-pill px-3 py-2">สถานะ: ปกติ</span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title"><i class="bi bi-house-door me-2"></i>ข้อมูลห้องพักของคุณ</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary">หมายเลขห้อง</span>
                        <span class="h3 text-primary mb-0">{{ $tenant->room->room_number ?? '-' }}</span>
                    </div>
                    
                    <hr class="my-2 text-muted opacity-25">

                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <small class="text-muted d-block">ชั้น</small>
                            <strong>{{ $tenant->room->floor ?? '-' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">ประเภทห้อง</small>
                            <strong>{{ $tenant->room->roomPrice->roomType->name ?? '-' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">ค่าเช่าพื้นฐาน</small>
                            <strong class="text-success">{{ number_format($tenant->room->roomPrice->price ?? 0) }}</strong> บาท/เดือน
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">วันที่เริ่มสัญญา</small>
                            <strong>{{ \Carbon\Carbon::parse($tenant->created_at)->format('d/m/Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title"><i class="bi bi-grid me-2"></i>บริการด่วน</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary text-start" type="button">
                            <i class="bi bi-receipt me-2"></i> ดูยอดค้างชำระ / ใบแจ้งหนี้
                        </button>
                        <button class="btn btn-outline-warning text-start" type="button">
                            <i class="bi bi-tools me-2"></i> แจ้งซ่อมอุปกรณ์
                        </button>
                        <form action="{{ route('tenant.logout') }}" method="post" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection