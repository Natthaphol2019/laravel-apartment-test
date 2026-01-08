<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin อาทิตย์ อพาร์ทเม้นท์</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --content-bg: #f8f9fa;
            --primary-color: #3498db;
            --text-muted: #95a5a6;
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--content-bg);
            margin: 0;
            display: flex;
            transition: all 0.3s;
        }

        /* Sidebar Base */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Sidebar Collapsed State */
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-header h5,
        .sidebar.collapsed .sidebar-header p,
        .sidebar.collapsed .menu-category,
        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
            padding: 20px 0;
        }

        .menu-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 15px 20px 5px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .nav-link {
            color: #bdc3c7;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: 0.2s;
            border-left: 4px solid transparent;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 15px 0;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link i {
            font-size: 1.2rem;
            min-width: 30px;
            text-align: center;
        }

        .sidebar:not(.collapsed) .nav-link i {
            margin-right: 10px;
        }

        /* Main Content Adjustment */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .main-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .topbar {
            background: white;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .content-body { padding: 30px; }

        @media (max-width: 768px) {
            .sidebar { margin-left: calc(var(--sidebar-width) * -1); }
            .main-wrapper { margin-left: 0 !important; }
            .sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div>
                <h5 class="mb-0 fw-bold">Admin Panel</h5>
                <p class="small mb-0 opacity-50">อาทิตย์ อพาร์ทเม้นท์</p>
            </div>
            <button class="btn btn-sm text-white d-none d-md-block" id="btnCollapse">
                <i class="bi bi-chevron-left" id="collapseIcon"></i>
            </button>
        </div>

        <div class="flex-grow-1 overflow-auto">
            <div class="menu-category">ภาพรวม</div>
            {{-- เช็คถ้าเป็น route แผงควบคุม ให้ใส่คลาส active --}}
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span class="nav-text">แผงควบคุม</span>
                </a>

                <div class="menu-category">จัดการอาคาร</div>
                
                {{-- ตั้งค่าอพาร์ทเม้นท์ --}}
                <a href="{{ route('admin.apartment.show') }}" class="nav-link {{ request()->routeIs('admin.apartment.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span class="nav-text">ตั้งค่าอพาร์ทเม้นท์</span>
                </a>

                {{-- ข้อมูลตึก --}}
                <a href="{{ route('admin.building.show') }}" class="nav-link {{ request()->routeIs('admin.building.*') ? 'active' : '' }}">
                    <i class="bi bi-buildings-fill"></i>
                    <span class="nav-text">ข้อมูลตึก</span>
                </a>

                {{-- ประเภทห้องพัก --}}
                <a href="{{ route('admin.room_types.show') }}" class="nav-link {{ request()->routeIs('admin.room_types.*') ? 'active' : '' }}">
                    <i class="bi bi-tag-fill"></i>
                    <span class="nav-text">ประเภทห้องพัก</span>
                </a>
                {{-- ราคาห้องต่อประเภทแต่ละตึก --}}
                <a href="{{ route('admin.room_prices.show') }}" class="nav-link {{ request()->routeIs('admin.room_prices.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span class="nav-text">ราคาห้องแต่ละตึก</span>
                </a>
                {{-- ข้อมูลห้องพัก --}}
                <a href="{{ route('admin.rooms.show') }}" class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
                    <i class="bi bi-door-closed-fill"></i>
                    <span class="nav-text">ข้อมูลห้องพัก</span>
                </a>
                {{-- จัดการผู้เช่า --}}
                <a href="{{ route('admin.tenants.show') }}" class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span class="nav-text">ข้อมูลผู้เช่า</span>
                </a>

            <div class="menu-category">การเงิน & บัญชี</div>
            <a href="#" class="nav-link"><i class="bi bi-receipt-cutoff"></i><span class="nav-text">ตั้งค่าเก็บค่าใช้จ่ายกับผู้เช่า <br> (ยังไม่ทำ)</span></a>
            <a href="#" class="nav-link"><i class="bi bi-receipt-cutoff"></i><span class="nav-text">ออกใบแจ้งหนี้ (ยังไม่ทำ)</span></a>
            <a href="#" class="nav-link"><i class="bi bi-cash-stack"></i><span class="nav-text">บันทึกรายรับ (ยังไม่ทำ)</span></a>
            @if (Auth::guard('admin')->user()->role === 'ผู้บริหาร')            
                <div class="menu-category">จัดการผู้ดูแลระบบงาน</div>
                <a href="{{ route('admin.users_manage.show') }}" class="nav-link"><i class="bi bi-person-fill"></i><span class="nav-text">ข้อมูลผู้ดูแลระบบงาน</span></a>
            @endif
        </div>

        <div class="p-3 border-top border-secondary">
            <a href="#" class="nav-link text-danger p-0 border-0" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i>
                <span class="nav-text ms-2">ออกจากระบบ</span>
            </a>
        </div>
    </aside>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="btn d-md-none p-0" id="toggleSidebar">
                <i class="bi bi-list fs-3"></i>
            </button>
            
            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3">
                    <div class="fw-bold small text-primary">{{ Auth::user()->firstname ?? 'ผู้ดูแลระบบ' }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">แอดมินระบบ</div>
                </div>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </header>

        <div class="content-body">
            @yield('content')
        </div>

        <footer class="mt-auto py-3 text-center text-muted small border-top bg-white">
            &copy; {{ date('Y') }} อาทิตย์ อพาร์ทเม้นท์. 199 หมู่ 4 ต.บ้านสร้าง.
        </footer>
    </div>

    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        const btnCollapse = document.getElementById('btnCollapse');
        const collapseIcon = document.getElementById('collapseIcon');
        const toggleSidebar = document.getElementById('toggleSidebar');

        // ฟังก์ชันย่อ-ขยาย Sidebar (Desktop)
        btnCollapse?.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainWrapper.classList.toggle('expanded');
            
            // เปลี่ยนไอคอนลูกศร
            if(sidebar.classList.contains('collapsed')) {
                collapseIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
            } else {
                collapseIcon.classList.replace('bi-chevron-right', 'bi-chevron-left');
            }
        });

        // Toggle Sidebar (Mobile)
        toggleSidebar?.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    </script>

    <script>
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
        @endif
        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด!', html: "{!! implode('<br>', $errors->all()) !!}", confirmButtonColor: '#4e73df' });
        @endif
    </script>
    
    @stack('scripts')
</body>
</html>