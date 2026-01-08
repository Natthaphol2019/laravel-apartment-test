@extends('auth.layout')

@section('title', 'Tenant Login')

@section('content')
<style>
    /* ปรับแต่งสีพื้นหลังเฉพาะหน้านี้ให้ต่างจาก Admin */
    body {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
    }
    .btn-tenant {
        background-color: #11998e;
        color: white;
        border-radius: 10px;
        padding: 12px;
        transition: 0.3s;
    }
    .btn-tenant:hover {
        background-color: #0d7c73;
        color: white;
        transform: translateY(-2px);
    }
    .card-tenant {
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
</style>

<div class="container">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="card card-tenant border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-3">
                        <div class="bg-light d-inline-block p-3 rounded-circle mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#11998e" class="bi bi-house-heart-fill" viewBox="0 0 16 16">
                                <path d="M8 6.982C9.664 5.309 13.825 8.236 8 12 2.175 8.236 6.336 5.31 8 6.982Z"/>
                                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z"/>
                            </svg>
                        </div>
                        <h4 class="fw-bold text-dark">ยินดีต้อนรับผู้เช่า</h4>
                        <p class="text-muted small">อาทิตย์ อพาร์ทเม้นท์</p>
                    </div>

                    <form action="{{ route('tenant.login') }}" method="POST" class="text-start">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small text-secondary">หมายเลขห้อง</label>
                            <input type="text" name="room_number" class="form-control" placeholder="ระบุเลขห้องพัก" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-secondary">เลขบัตรประชาชน</label>
                            <input type="password" name="password" class="form-control" placeholder="ระบุรหัสผ่าน" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-tenant fw-bold">เข้าสู่ระบบห้องพัก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection