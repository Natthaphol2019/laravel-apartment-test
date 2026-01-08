@extends('auth.layout')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg" style="border-radius: 15px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary">อาทิตย์ อพาร์ทเม้นท์</h3>
                        <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อจัดการข้อมูล</p>
                    </div>

                    <form action="{{ route('admin.login') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="username" class="form-label text-secondary">ชื่อผู้ใช้งาน (Username)</label>
                            <input type="text" name="username" id="username" 
                                   class="form-control form-control-lg @error('username') is-invalid @enderror" 
                                   placeholder="ระบุชื่อผู้ใช้งาน" required style="border-radius: 10px; font-size: 0.9rem;">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary">รหัสผ่าน (Password)</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   placeholder="••••••••" required style="border-radius: 10px; font-size: 0.9rem;">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" 
                                    style="border-radius: 10px; font-weight: 500; transition: 0.3s;">
                                เข้าสู่ระบบ
                            </button>
                        </div>
                    </form>

                    <hr class="my-4 text-muted">

                    <div class="text-center">
                        <p class="small text-secondary mb-0">ยังไม่มีบัญชี? <a href="{{ route('admin.registerForm') }}" class="text-primary fw-bold text-decoration-none">สร้างบัญชีที่นี่</a></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 text-muted small">
                &copy; {{ date('Y') }} อาทิตย์ อพาร์ทเม้นท์. All Rights Reserved.
            </div>
        </div>
    </div>
</div>

<style>
    /* เพิ่มลูกเล่น Gradient พื้นหลังเฉพาะหน้า Login */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .btn-primary {
        background-color: #4e73df;
        border: none;
    }
    .btn-primary:hover {
        background-color: #224abe;
        transform: translateY(-2px);
    }
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
</style>
@endsection