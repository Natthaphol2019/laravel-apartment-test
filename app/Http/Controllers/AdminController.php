<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // เรียกใช้งาน Auth Facade
use Barryvdh\DomPDF\Facade\Pdf; // ใช้งานสำหรับ pdf

use App\Models\RoomPrices;
use App\Models\RoomType;
use App\Models\Building;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantExpense;
use App\Models\MeterReading;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Payment;
use App\Models\AccountingTransaction;
use App\Models\AccountingCategory;
use App\Models\AccountingType;
use App\Models\User;
class AdminController extends Controller
{
    // ไปหน้า Dashboard ผู้ดูแลระบบ
    public function adminDashboard()
    {
        return view('admin.dashboard');
    }
    // ไปหน้า ตั้งค่าอพาร์ทเม้นท์ settingApartment
    // ---------------------------------------------
    public function apartmentShow()
    {
        $apartment = DB::table('apartment')->first();
        return view('admin.apartment.show', compact('apartment'));
    }

    public function editApartment($id)
    {
        $apartment = DB::table('apartment')->where('id', $id)->first();
        return view('admin.apartment.edit', compact('apartment'));
    }

    public function updateApartment(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'address_no' => 'nullable|string|max:100',
                'moo' => 'nullable|string|max:3',
                'sub_district' => 'nullable|string|max:100',
                'district' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:5',
                'phone' => 'nullable|string|max:10',
            ]);
            $data = [
                'name' => $request->name,
                'address_no' => $request->address_no,
                'moo' => $request->moo,
                'sub_district' => $request->sub_district,
                'district' => $request->district,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone,
                'updated_at' => now(),
            ];
            DB::table('apartment')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.apartment.show', $id)->with('success', 'ข้อมูลอพาร์ทเม้นท์ถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ---------------------------------------------

    // ไปหน้า จัดการประเภทตึก Building ตึก 2 4 5 ชั้น

    public function buildingShow()
    {
        $buildings = DB::table('buildings')->get();
        return view('admin.building.show', compact('buildings'));
    }

    public function updateBuilding(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
            $data = [
                'name' => $request->name,
                'updated_at' => now(),
            ];
            DB::table('buildings')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.building.show')->with('success', 'ข้อมูลอาคารถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    // ---------------------------------------------

    // จัดการประเภทห้อง Room Type

    public function roomTypeShow()
    {
        $room_types = DB::table('room_types')->get();
        return view('admin.room_types.show', compact('room_types'));
    }

    public function insertRoomType(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255|unique:room_types,name',
            ]);
            DB::table('room_types')->insert([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_types.show')->with('success', 'เพิ่มประเภทห้องพักสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateRoomType(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255|unique:room_types,name,',
            ]);
            $data = [
                'name' => $request->name,
                'updated_at' => now(),
            ];
            DB::table('room_types')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_types.show')->with('success', 'ข้อมูลประเภทห้องถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    public function deleteRoomType($id)
    {
        try {
            DB::beginTransaction();
            DB::table('room_types')->where('id', $id)->delete();
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_types.show')->with('success', 'ลบประเภทห้องสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }

    // ---------------------------------------------

    // จัดการราคาห้อง Room_price

    public function roomPriceShow(Request $request)
    {
        $room_prices = RoomPrices::with(['building', 'roomType'])
            ->when($request->building_id, function ($q) use ($request) {
                $q->where('building_id', $request->building_id);
            })
            ->when($request->room_type_id, function ($q) use ($request) {
                $q->where('room_type_id', $request->room_type_id);
            })
            ->when($request->floor_num, function ($q) use ($request) {
                $q->where('floor_num', $request->floor_num);
            })
            ->orderBy('building_id', 'asc')
            ->orderBy('floor_num', 'asc')
            ->paginate(10)      // จำกัด 10 แถว
            ->withQueryString();         // จำค่า filter
        $room_types = RoomType::all();
        $buildings = Building::all();
        return view('admin.room_prices.show', compact('room_prices', 'room_types', 'buildings'));
    }

    public function insertRoomPrice(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'room_type_id' => 'required|exists:room_types,id',
                'floor_num' => 'required|integer|min:1|max:5',
                'price' => 'required|numeric|min:0',
                'color_code' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            if (!$request->hasFile('color_code')) {
                return redirect()->back()->withErrors(['error' => 'กรุณาอัปโหลดรูปภาพ']);
            }
            // สร้างชื่อไฟล์ใหม่
            $filename = time() . '_' . $request->color_code->getClientOriginalName();
            // บันทึกรูป
            $path = $request->file('color_code')->storeAs(
                'room_prices',
                $filename,
                'public'
            );
            DB::table('room_prices')->insert([
                'building_id' => $request->building_id,
                'room_type_id' => $request->room_type_id,
                'floor_num' => $request->floor_num,
                'price' => $request->price,
                'color_code' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_prices.show')->with('success', 'เพิ่มราคาห้องพักสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateRoomPrice(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'room_type_id' => 'required|exists:room_types,id',
                'floor_num' => 'required|integer|min:1|max:5',
                'price' => 'required|numeric|min:0',
                'color_code' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $data = [
                'building_id' => $request->building_id,
                'room_type_id' => $request->room_type_id,
                'floor_num' => $request->floor_num,
                'price' => $request->price,
                'updated_at' => now(),
            ];
            $roomPrice = DB::table('room_prices')->where('id', $id)->first();
            if ($request->hasFile('color_code')) {
                // เช็คว่ามีชื่อไฟล์เดิมใน DB และไฟล์นั้นมีอยู่จริงใน Disk หรือไม่
                if (!empty($roomPrice->color_code) && Storage::disk('public')->exists($roomPrice->color_code)) {
                    Storage::disk('public')->delete($roomPrice->color_code);
                }
                // สร้างชื่อไฟล์ใหม่
                $filename = time() . '_' . $request->color_code->getClientOriginalName();
                // บันทึกรูป
                $path = $request->file('color_code')->storeAs(
                    'room_prices',
                    $filename,
                    'public'
                );
                $data['color_code'] = $path;
            }
            DB::table('room_prices')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_prices.show')->with('success', 'ข้อมูลราคาห้องถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }

    public function deleteRoomPrice($id)
    {
        try {
            DB::beginTransaction();
            $roomPrice = DB::table('room_prices')->where('id', $id)->first();
            // ลบรูปภาพถ้ามี
            if (!empty($roomPrice->color_code) && Storage::disk('public')->exists($roomPrice->color_code)) {
                Storage::disk('public')->delete($roomPrice->color_code);
            }
            DB::table('room_prices')->where('id', $id)->delete();
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.room_prices.show')->with('success', 'ลบราคาห้องสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    // ---------------------------------------------

    // จัดการห้อง Rooms

    public function roomShow(Request $request)
    {
        // 1. ดึงข้อมูล Rooms พร้อมความสัมพันธ์ (Room -> RoomPrice -> Building & RoomType)
        $rooms = Room::with(['roomPrice.building', 'roomPrice.roomType'])
            // แก้ไข: กรองตามอาคาร ต้องใช้ whereHas เพื่อมุดเข้าไปในตาราง roomPrice
            ->when($request->building_id, function ($q) use ($request) {
                $q->whereHas('roomPrice', function ($subQ) use ($request) {
                    $subQ->where('building_id', $request->building_id);
                });
            })
            // แก้ไข: กรองตามชั้น ก็ต้องใช้ whereHas เช่นกัน
            ->when($request->floor_num, function ($q) use ($request) {
                $q->whereHas('roomPrice', function ($subQ) use ($request) {
                    $subQ->where('floor_num', $request->floor_num);
                });
            })
            // กรองตามสถานะห้องพัก (อันนี้เขียนถูกแล้ว เพราะ status อยู่ในตาราง rooms)
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            // เรียงลำดับตามเลขห้อง
            ->orderBy('room_number', 'asc')
            ->paginate(15)
            ->withQueryString();

        // 2. ดึงข้อมูลสำหรับ Dropdown ใน Filter และ Modal
        $buildings = Building::all();

        // ดึง room_prices เพื่อใช้เลือกตอน insert/edit ห้องใหม่
        // แนะนำให้ดึงความสัมพันธ์มาด้วยเพื่อให้แสดงชื่อตึกและประเภทห้องใน Dropdown ได้ชัดเจน
        $room_prices = RoomPrices::with(['building', 'roomType'])
            ->orderBy('building_id', 'asc')
            ->orderBy('floor_num', 'asc')
            ->get();

        return view('admin.rooms.show', compact('rooms', 'buildings', 'room_prices'));
    }

    public function insertRoom(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'room_number' => 'required|string|max:4|unique:rooms,room_number',
                'room_price_id' => 'required|exists:room_prices,id',
                'status' => 'required',
            ]);
            DB::table('rooms')->insert([
                'room_number' => $request->room_number,
                'room_price_id' => $request->room_price_id,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.rooms.show')->with('success', 'เพิ่มห้องพักสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateRoom(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'room_number' => 'required|string|max:4|unique:rooms,room_number,' . $id,
                'room_price_id' => 'required|exists:room_prices,id',
                'status' => 'required',
            ]);
            $data = [
                'room_number' => $request->room_number,
                'room_price_id' => $request->room_price_id,
                'status' => $request->status,
                'updated_at' => now(),
            ];
            DB::table('rooms')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return back()->with('success', 'ข้อมูลห้องพักถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }

    public function deleteRoom($id)
    {
        try {
            DB::beginTransaction();
            DB::table('rooms')->where('id', $id)->delete();
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.rooms.show')->with('success', 'ลบห้องพักสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    //15 ก.พ. 69 จัดการผังห้อง
    public function floorPlan()
    {
        // ดึงห้องทั้งหมดมาแสดง
        $rooms = Room::all();

        // กำหนดขนาดพื้นที่วาด (SVG Canvas)
        $svgWidth = 1000;
        $svgHeight = 600;

        return view('admin.floorplan', compact('rooms', 'svgWidth', 'svgHeight'));
    }
    // 2. เพิ่มฟังก์ชันบันทึกตำแหน่ง (รับ JSON จาก JS)
    public function saveLayout(Request $request)
    {
        $positions = $request->input('positions'); // รับข้อมูล Array: [{id:1, x:10, y:20}, ...]

        if ($positions) {
            foreach ($positions as $pos) {
                Room::where('id', $pos['id'])->update([
                    'pos_x' => $pos['x'],
                    'pos_y' => $pos['y']
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'บันทึกตำแหน่งเรียบร้อย!']);
    }
    //ส่วนสองเพิ่มมา
    public function roomSystem(Request $request)
{
    // 1. รับค่า Filter
    $buildingId = $request->input('building_id');
    $status = $request->input('status');
    $search = $request->input('search');
    $floor = $request->input('floor');

    // 2. สร้าง Query แบบ Join ตาราง เพื่อให้รู้จัก building_id
    $query = Room::query()
        ->join('room_prices', 'rooms.room_price_id', '=', 'room_prices.id')
        ->join('buildings', 'room_prices.building_id', '=', 'buildings.id')
        ->join('room_types', 'room_prices.room_type_id', '=', 'room_types.id') // Join เพื่อเอาชื่อประเภทห้องด้วย
        ->select(
            'rooms.*', 
            'buildings.name as building_name', 
            'buildings.id as building_id',
            'room_types.name as room_type_name'
        );

    // 3. ใส่ Filter ต่างๆ
    if ($buildingId) {
        $query->where('buildings.id', $buildingId); // ตอนนี้ใช้ได้แล้ว
    }

    if ($status) {
        $query->where('rooms.status', $status);
    }

    if ($search) {
        $query->where('rooms.room_number', 'like', "%{$search}%");
    }
    
    if ($floor) {
        $query->where('rooms.room_number', 'like', $floor . '%');
    }

    // 4. จัดเรียง (เรียงตามตึก -> ชั้น -> เลขห้อง)
    $rooms = $query->orderBy('buildings.id', 'asc')
                   ->orderBy('rooms.room_number', 'asc')
                   ->paginate(15)
                   ->withQueryString();

    // ดึงข้อมูลตึกสำหรับ Dropdown
    $buildings = \App\Models\Building::all();

    return view('admin.rooms.system', compact('rooms', 'buildings'));
}

    // ---------------------------------------------

    // จัดการผู้เช่า Tenant
    // 1. แสดงรายการผู้เช่าพร้อมตัวกรอง

    public function tenantShow(Request $request)
    {
        $tenants = Tenant::with('room.roomPrice.building')
            ->when($request->search, function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%")
                    ->orWhere('id_card', 'like', "%{$request->search}%")
                    // ค้นหาข้ามไปยังตาราง rooms ผ่าน room_number
                    ->orWhereHas('room', function ($roomQuery) use ($request) {
                        $roomQuery->where('room_number', 'like', "%{$request->search}%");
                    });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // ดึงเฉพาะห้องที่สถานะ 'ว่าง' เพื่อนำไปใช้ในหน้าลงทะเบียนใหม่
        $rooms = Room::with('roomPrice.building')->where('status', 'ว่าง')->get();
        $buildings = Building::all();

        return view('admin.tenants.show', compact('tenants', 'rooms', 'buildings'));
    }
    // 2. ลงทะเบียนผู้เช่าใหม่พร้อมอัปโหลดสัญญาเช่า
    public function insertTenant(Request $request)
    {
        DB::beginTransaction();
        try {
            // 2. Validation บังคับไฟล์ PDF หรือ รูปภาพ
            $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'id_card' => 'required|digits:13',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address_no' => 'required|string|max:255',
                'moo' => 'required|string|max:3',
                'sub_district' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'required|string|max:5',
                'phone' => 'required|digits:10',
                'start_date' => 'required|date',
                'resident_count' => 'required|integer|min:1',
                'deposit_amount' => 'required|numeric|min:1',
                // ตรวจสอบนามสกุลไฟล์ให้เป็น pdf, jpg, jpeg, png เท่านั้น
                'rental_contract' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            ]);

            // 2. จัดการไฟล์สัญญาเช่าลงโฟลเดอร์ tenants
            $contractPath = null;
            if ($request->hasFile('rental_contract')) {
                $id_card = $request->id_card;
                $extension = $request->file('rental_contract')->extension();
                $filename = time() . $id_card . '_tenant_.' . $extension;

                // เก็บลงใน storage/app/public/tenants
                $contractPath = $request->file('rental_contract')->storeAs('tenants', $filename, 'public');
            }

            // 3. บันทึกข้อมูลผู้เช่าครบทุกฟิลด์ตาม Schema
            $tenant = Tenant::create([
                'room_id' => $request->room_id,
                'id_card' => $request->id_card,
                'password' => Hash::make($request->id_card),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address_no' => $request->address_no,
                'moo' => $request->moo,
                'sub_district' => $request->sub_district,
                'district' => $request->district,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'has_parking' => $request->has('has_parking'),
                'resident_count' => $request->resident_count,
                'deposit_amount' => $request->deposit_amount,
                'rental_contract' => $contractPath, // เก็บ Path tenants/filename.ext
                'status' => 'กำลังใช้งาน',
            ]);
            // 4. เพิ่มค่ามัดจำ
            //ดึงข้อมูล room เอาเลขห้อง
            $room = DB::table('rooms')->where('id', $request->room_id)->first();
            //บันทึก ดึงเอาชื่อ ธุรกรรม ID = 2 ชื่อ ประเภทเงินมัดจำ
            $category = AccountingCategory::findOrFail(2);
            // 5. อัปเดตสถานะห้องพักเป็น 'มีผู้เช่า'
            AccountingTransaction::create([
                'category_id' => $category->id,
                'payment_id' => null,
                'tenant_id' => $tenant->id,
                'user_id' => Auth::id(),
                'title' => $category->name . " (ห้อง " . $room->room_number . ")",
                'amount' => $request->deposit_amount,
                'entry_date' => $request->start_date,
                'description' => "รับเงินมัดจำจาก " . $tenant->first_name . " " . $tenant->last_name,
            ]);
            DB::table('rooms')->where('id', $request->room_id)->update([
                'status' => 'มีผู้เช่า',
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'ลงทะเบียนผู้เช่าและบันทึกข้อมูลบัญชีเงินมัดจำเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage()])->withInput();
        }
    }

    // 3. แก้ไขข้อมูลผู้เช่า พร้อมจัดการไฟล์สัญญาเช่า
    public function updateTenant(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // 1. ตรวจสอบสถานะห้องพักก่อนว่ายัง "ว่าง" อยู่จริงไหม

            $tenant = Tenant::findOrFail($id);

            // 1. Validation ครบทุกฟิลด์ตาม Schema
            $request->validate([
                'id_card' => 'required|digits:13',
                'password' => 'nullable|string|min:6',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address_no' => 'required|string|max:255',
                'moo' => 'required|string|max:3',
                'sub_district' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'required|string|max:5',
                'phone' => 'required|digits:10',
                'start_date' => 'required|date',
                'end_date' => 'date',
                'resident_count' => 'required|integer|min:1',
                'deposit_amount' => 'required|numeric|min:1',
                'rental_contract' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            ]);

            // 2. เตรียมข้อมูลพื้นฐาน (ยกเว้น room_id ตามที่คุณต้องการ)
            $data = [
                'id_card' => $request->id_card,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address_no' => $request->address_no,
                'moo' => $request->moo,
                'sub_district' => $request->sub_district,
                'district' => $request->district,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'resident_count' => $request->resident_count,
                'deposit_amount' => $request->deposit_amount,
                'has_parking' => $request->has('has_parking'), // เก็บเป็น boolean
                'updated_at' => now(),
            ];

            // 3. จัดการรหัสผ่าน (password) ถ้ามีการกรอกใหม่ให้ Hash
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // 4. จัดการไฟล์สัญญาเช่า (บันทึกลงโฟลเดอร์ public/tenants)
            if ($request->hasFile('rental_contract')) {
                // ลบไฟล์เก่าทิ้งเพื่อประหยัดพื้นที่
                if ($tenant->rental_contract && Storage::disk('public')->exists($tenant->rental_contract)) {
                    Storage::disk('public')->delete($tenant->rental_contract);
                }

                $extension = $request->file('rental_contract')->extension();
                $filename = time() . '_tenant_.' . $extension;

                $data['rental_contract'] = $request->file('rental_contract')->storeAs('tenants', $filename, 'public');
            }

            // 6. บันทึกการเปลี่ยนแปลงทั้งหมด
            $tenant->update($data);
            // 7. อัปเดตข้อมูลในระบบบัญชี (AccountingTransaction)
            // ค้นหารายการเงินมัดจำของห้องนี้ (Category ID 2)
            $accounting = AccountingTransaction::where('tenant_id', $tenant->id)
                ->where('category_id', 2)
                ->first();

            if ($accounting) {
                // ถ้าพบรายการเดิม ให้ทำการอัปเดตยอดเงิน และชื่อหัวข้อ (Title) ให้เป็นปัจจุบัน
                $accounting->update([
                    'amount' => $request->deposit_amount,
                    'updated_at' => now()
                ]);
            }
            DB::commit();
            return back()->with('success', 'อัปเดตข้อมูลผู้เช่าครบถ้วนเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'การอัปเดตล้มเหลว: ' . $e->getMessage()]);
        }
    }
    public function updateStatusTenant(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::findOrFail($id);

            if ($tenant->status == 'สิ้นสุดสัญญา') {
                return redirect()->back()->withErrors(['error' => 'สัญญานี้ได้สิ้นสุดลงก่อนหน้านี้แล้ว']);
            }
            // ค้นหาบิลที่สถานะเป็น 'ค้างชำระ' หรือ 'ชำระบางส่วน' ของผู้เช่ารายนี้
            $unpaidInvoices = Invoice::where('tenant_id', $id)
                ->whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
                ->get();

            if ($unpaidInvoices->isNotEmpty()) {
                // แปลงรอบเดือนแต่ละใบเป็นภาษาไทย และรวมเป็นข้อความเดียว
                $months = $unpaidInvoices->map(function ($inv) {
                    return $this->toThaiDate($inv->billing_month, false); // false เพื่อเอาเฉพาะเดือน/ปี
                })->unique()->implode(', '); // unique() เพื่อป้องกันกรณีมีหลายบิลในเดือนเดียวกัน

                return back()->withErrors([
                    'error' => "ไม่สามารถสิ้นสุดสัญญาได้ เนื่องจากยังมีรายการค้างชำระของรอบเดือน: {$months} กรุณาจัดการยอดค้างให้เรียบร้อยก่อน"
                ])->withInput();
            }
            // 1. จัดการวันที่สิ้นสุดสัญญา (ถ้าไม่ระบุให้ใช้ now())
            $endDate = $request->end_date ?: now();

            // 2. อัปเดตสถานะผู้เช่าและคืนห้องพัก
            $tenant->update([
                'status' => 'สิ้นสุดสัญญา',
                'end_date' => $endDate,
                'updated_at' => now()
            ]);

            DB::table('rooms')->where('id', $tenant->room_id)->update([
                'status' => 'ว่าง',
                'updated_at' => now()
            ]);

            // 3. เงื่อนไขการคืนเงินมัดจำ (ถ้ามากกว่า 0 ถึงจะ Insert รายจ่าย)
            $refundAmount = (float) $request->refund_amount;
            if ($refundAmount > 0) {
                AccountingTransaction::create([
                    'category_id' => 20, // หมวดหมู่รายจ่ายคืนเงินมัดจำ
                    'tenant_id' => $tenant->id,
                    'user_id' => Auth::id(),
                    'title' => "คืนเงินมัดจำ (สิ้นสุดสัญญา) ห้อง " . $tenant->room->room_number,
                    'amount' => $refundAmount,
                    'entry_date' => $endDate,
                    'description' => "คืนเงินมัดจำผู้เช่า: {$tenant->first_name} {$tenant->last_name}",
                    'status' => 'active'
                ]);
            }

            DB::commit();
            return back()->with('success', 'สิ้นสุดสัญญาเรียบร้อยแล้ว' . ($refundAmount > 0 ? ' และบันทึกรายการคืนเงินมัดจำแล้ว' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    // 4. ลบข้อมูลผู้เช่า
    public function deleteTenant($id)
    {
        try {
            DB::beginTransaction();
            $tenant = Tenant::findOrFail($id);

            // ลบไฟล์สัญญา
            if ($tenant->rental_contract) {
                Storage::disk('public')->delete($tenant->rental_contract);
            }

            // คืนสถานะห้องให้ว่างก่อนลบผู้เช่า
            Room::where('id', $tenant->room_id)->update(['status' => 'ว่าง']);

            $tenant->delete();
            DB::commit();
            return redirect()->back()->with('success', 'ลบข้อมูลผู้เช่าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    // ---------------------------------------------
    // จัดการค่าใช้จ่ายกับผู้เช่า Tenant Expenses

    public function tenantExpensesShow()
    {
        $expenses = TenantExpense::with('category')->paginate(10);
        // ดึงเฉพาะหมวดหมู่ที่เป็น "รายรับ" (type_id = 1) เพื่อให้ Admin เลือกจับคู่
        $categories = AccountingCategory::where('type_id', 1)->get();
        return view('admin.tenant_expenses.show', compact('expenses', 'categories'));
    }

    public function insertTenantExpense(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:50|unique:tenant_expenses,name',
                'price' => 'required|numeric|min:0',
                'accounting_category_id' => 'required|exists:accounting_categories,id', // ต้องเลือกหมวดหมู่
            ]);
            DB::table('tenant_expenses')->insert([
                'name' => $request->name,
                'price' => $request->price,
                'accounting_category_id' => $request->accounting_category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.tenant_expenses.show')->with('success', 'เพิ่มรายการค่าใช้จ่ายสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateTenantExpense(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:50|unique:tenant_expenses,name,' . $id,
                'price' => 'required|numeric|min:0',
                'accounting_category_id' => 'required|exists:accounting_categories,id', // ต้องเลือกหมวดหมู่
            ]);
            $data = [
                'name' => $request->name,
                'price' => $request->price,
                'accounting_category_id' => $request->accounting_category_id,
                'updated_at' => now(),
            ];
            DB::table('tenant_expenses')->where('id', $id)->update($data);
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.tenant_expenses.show')->with('success', 'ข้อมูลรายการค่าใช้จ่ายถูกอัปเดตแล้ว');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }

    public function deleteTenantExpense($id)
    {
        try {
            DB::beginTransaction();
            DB::table('tenant_expenses')->where('id', $id)->delete();
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->route('admin.tenant_expenses.show')->with('success', 'ลบรายการค่าใช้จ่ายสำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    // ---------------------------------------------

    // จดมิเตอร์น้ำไฟ Meter Readings

    public function meterReadingsInsertForm(Request $request)
    {

        $billing_month = $request->billing_month ?? date('Y-m');

        // 1. หา ID ของห้องที่จดมิเตอร์ในเดือนนี้ไปแล้ว ทั้งน้ำและไฟ
        $completedRoomIds = MeterReading::where('billing_month', $billing_month)
            ->groupBy('room_id')
            ->havingRaw('COUNT(DISTINCT meter_type) >= 2') // จดครบทั้งน้ำและไฟ
            ->pluck('room_id');

        // 2. ดึงเฉพาะห้องที่มีผู้เช่า และ "ไม่อยู่ในรายชื่อที่จดเสร็จแล้ว"
        $rooms = Room::with([
            'tenants' => function ($q) {
                $q->where('status', 'กำลังใช้งาน');
            }
        ])
            ->where('status', 'มีผู้เช่า')
            ->whereNotIn('id', $completedRoomIds) // กรองห้องที่จดเสร็จแล้วออก
            ->orderBy('room_number', 'asc')
            ->get();

        $existingReadings = MeterReading::where('billing_month', $billing_month)->get();

        foreach ($rooms as $room) {
            foreach (['water', 'electric'] as $type) {
                $lastReading = MeterReading::where('room_id', $room->id)
                    ->where('meter_type', $type)
                    ->where('billing_month', '<', $billing_month)
                    ->orderBy('billing_month', 'desc')
                    ->first();

                // ถ้าไม่มีข้อมูลเดือนก่อน ให้ส่งค่า null หรือ 0 ไป
                $room->{"prev_{$type}"} = $lastReading ? $lastReading->current_value : null;
            }
        }
        // สร้างตัวแปรใหม่สำหรับแสดงผลภาษาไทย
        $dateObj = \Carbon\Carbon::parse($billing_month);
        $thai_date = $dateObj->locale('th')->getTranslatedMonthName() . ' ' . ($dateObj->year + 543);
        return view('admin.meter_readings.insert', compact('rooms', 'billing_month', 'existingReadings', 'thai_date'));
    }
    public function insertMeterReading(Request $request)
    {
        $request->validate([
            'billing_month' => 'required',
            'reading_date' => 'required|date',
            'data' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->data as $roomId => $types) {
                foreach ($types as $type => $values) {
                    // ข้ามหากไม่ได้กรอกเลขมิเตอร์ปัจจุบัน
                    if (is_null($values['current_value']))
                        continue;

                    $prev = (float) $values['previous_value'];
                    $current = (float) $values['current_value'];

                    // บันทึกลงตาราง meter_readings โดยตรง
                    MeterReading::create([
                        'room_id' => $roomId,
                        'tenant_id' => $values['tenant_id'],
                        'meter_type' => $type,
                        'previous_value' => $prev,
                        'current_value' => $current,
                        'units_used' => $current - $prev,
                        'billing_month' => $request->billing_month,
                        'reading_date' => $request->reading_date,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'บันทึกข้อมูลและออกรายการเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    // หน้าแก้ไขมิเตอร์ (เฉพาะรายการที่จดแล้ว)
    public function readMeterReading(Request $request)
    {
        $billing_month = $request->billing_month ?? date('Y-m');
        // ดึงวันที่ที่เคยจดไว้ (ดึงจากรายการแรกที่เจอในเดือนนั้น)
        $recordedDate = MeterReading::where('billing_month', $billing_month)->value('reading_date') ?? date('Y-m-d');

        // 1. หา ID ของห้องที่จดมิเตอร์ในเดือนนี้ไปแล้ว (อย่างน้อย 1 อย่าง)
        $recordedRoomIds = MeterReading::where('billing_month', $billing_month)
            ->pluck('room_id')
            ->unique();

        // 2. ดึงข้อมูลห้องพักและผู้เช่าเฉพาะห้องที่มีการจดบันทึกไปแล้ว
        $rooms = Room::with([
            'tenants' => function ($q) {
                $q->where('status', 'กำลังใช้งาน');
            }
        ])
            ->whereIn('id', $recordedRoomIds)
            ->orderBy('room_number', 'asc')
            ->get();

        $existingReadings = MeterReading::where('billing_month', $billing_month)->get();

        // ดึงค่า Previous เหมือนหน้า Show เพื่อใช้ในการคำนวณใหม่หากมีการแก้ไข
        foreach ($rooms as $room) {
            foreach (['water', 'electric'] as $type) {
                $lastReading = MeterReading::where('room_id', $room->id)
                    ->where('meter_type', $type)
                    ->where('billing_month', '<', $billing_month)
                    ->orderBy('billing_month', 'desc')
                    ->first();
                $room->{"prev_{$type}"} = $lastReading ? $lastReading->current_value : null;
            }
        }
        // สร้างตัวแปรใหม่สำหรับแสดงผลภาษาไทย
        $dateObj = \Carbon\Carbon::parse($billing_month);
        $thai_date = $dateObj->locale('th')->getTranslatedMonthName() . ' ' . ($dateObj->year + 543);
        return view('admin.meter_readings.show', compact('rooms', 'billing_month', 'existingReadings', 'thai_date', 'recordedDate'));
    }

    // ฟังก์ชันสำหรับ Update ข้อมูล (ใช้ updateOrCreate เพื่อความปลอดภัย)
    public function updateMeterReading(Request $request)
    {
        $request->validate([
            'billing_month' => 'required',
            'reading_date' => 'required|date',
            'data' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->data as $roomId => $types) {
                foreach ($types as $type => $values) {
                    if (is_null($values['current_value']))
                        continue;

                    $prev = (float) $values['previous_value'];
                    $current = (float) $values['current_value'];

                    MeterReading::updateOrCreate(
                        [
                            'room_id' => $roomId,
                            'meter_type' => $type,
                            'billing_month' => $request->billing_month,
                        ],
                        [
                            'tenant_id' => $values['tenant_id'],
                            'previous_value' => $prev,
                            'current_value' => $current,
                            'units_used' => $current - $prev,
                            'reading_date' => $request->reading_date,
                        ]
                    );
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'แก้ไขข้อมูลมิเตอร์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    // ---------------------------------------------

    // จัดการระบบ Invoice

    public function invoiceShow(Request $request)
    {
        $billing_month = $request->billing_month ?? date('Y-m');
        $dateObj = \Carbon\Carbon::parse($billing_month);
        $endOfMonth = $dateObj->endOfMonth()->format('Y-m-d');

        // ดึงห้องที่มีสถานะ "มีผู้เช่า"
        $rooms = Room::with([
            'tenants' => function ($q) use ($endOfMonth) {
                $q->where('start_date', '<=', $endOfMonth)
                    ->where('status', 'กำลังใช้งาน');
            },
            'roomPrice'
        ])
            ->where('status', 'มีผู้เช่า')
            ->get();

        $rooms = $rooms->filter(fn($room) => $room->tenants->isNotEmpty());

        $rooms->each(function ($room) use ($billing_month) {
            $tenant = $room->tenants->first();

            // 1. ดึงเลขมิเตอร์ยกมา (Previous Values)
            $prevMonth = \Carbon\Carbon::parse($billing_month)->subMonth()->format('Y-m');
            foreach (['water', 'electric'] as $type) {
                $lastReading = MeterReading::where('room_id', $room->id)
                    ->where('meter_type', $type)
                    ->where('billing_month', $prevMonth)
                    ->first();
                $attrName = "prev_{$type}";
                $room->$attrName = $lastReading ? $lastReading->current_value : 0;
            }

            // 2. ตรวจสอบสถานะมิเตอร์และบิล
            $readings = MeterReading::where('room_id', $room->id)->where('billing_month', $billing_month)->get();
            $room->can_create_invoice = $readings->where('meter_type', 'water')->isNotEmpty() && $readings->where('meter_type', 'electric')->isNotEmpty();
            $room->meter_status = $room->can_create_invoice ? 'จดมิเตอร์ครบแล้ว' : 'ยังไม่ได้จดมิเตอร์';
            $room->meter_color = $room->can_create_invoice ? 'success' : 'danger';

            $invoice = Invoice::where('room_id', $room->id)->where('billing_month', $billing_month)->first();

            if (!$invoice) {
                $room->invoice_status = 'ยังไม่ได้สร้างบิล';
                $room->invoice_color = 'secondary';
                $room->invoice_id = null;
            } else {
                $room->invoice_status = $invoice->status;
                $room->invoice_color = ($invoice->status == 'ชำระแล้ว') ? 'success' : 'warning';
                $room->invoice_id = $invoice->id;
                $room->invoice_total = $invoice->total_amount;

                // เรียกใช้ฟังก์ชันใหม่เพื่อแปลงวันที่ออกบิล
                $room->thai_issue_date = $this->toThaiDate($invoice->issue_date);
                $room->thai_due_date = $this->toThaiDate($invoice->due_date);
            }

            $room->tenant_id = $tenant->id;
        });

        // แปลงรอบเดือนเรียกเก็บ (ไม่เอา "วัน" จึงใส่ false)
        $thai_billing_month = $this->toThaiDate($billing_month, false);

        return view('admin.invoices.show', compact('rooms', 'billing_month', 'thai_billing_month'));
    }

    public function invoiceCollectionReport(Request $request)
    {
        $billing_month = $request->billing_month ?? date('Y-m');
        $status_filter = $request->input('status_filter', ['มีผู้เช่า', 'ว่าง']);

        // 1. ดึงค่าใช้จ่ายจากตาราง และเพิ่ม "ค่าเช่าห้อง" เข้าไปเป็นตัวเลือกแรก
        $allExpenseSettings = TenantExpense::select('name')->get();

        // ตรวจสอบว่ามี "ค่าเช่าห้อง" ในตารางหรือยัง ถ้าไม่มีให้เพิ่มเข้าไปใน Collection ชั่วคราว
        if (!$allExpenseSettings->contains('name', 'ค่าเช่าห้อง')) {
            $allExpenseSettings->prepend((object) ['name' => 'ค่าเช่าห้อง']);
        }

        // กำหนดคอลัมน์ที่จะโชว์ (ถ้าเป็นครั้งแรกให้เลือกทั้งหมด)
        $show_columns = $request->input('show_columns', $allExpenseSettings->pluck('name')->toArray());

        // 2. Query ห้องพัก และผู้เช่า (ใช้สถานะ "กำลังใช้งาน" ตามตาราง tenants)
        $rooms = Room::with([
            'tenants' => function ($q) {
                $q->where('status', 'กำลังใช้งาน');
            }
        ])
            ->whereIn('status', $status_filter)
            ->get();

        // 3. ดึง Invoice และจัดกลุ่มรายละเอียด
        $invoices = Invoice::with(['details', 'payments'])
            ->where('billing_month', $billing_month)
            ->get()
            ->keyBy('room_id');

        $rooms->each(function ($room) use ($invoices, $allExpenseSettings) {
            $invoice = $invoices->get($room->id);
            $room->current_invoice = $invoice;

            $detailsMap = collect();

            if ($invoice) {
                foreach ($invoice->details as $detail) {
                    // ค้นหาว่าชื่อในรายละเอียดบิล ตรงกับหมวดหมู่ไหนใน Setting
                    $matchedCategory = $allExpenseSettings->first(function ($setting) use ($detail) {
                        // ตรวจสอบว่าชื่อในบิล (เช่น 'คนมาอาศัยเพิ่ม...') มีคำว่า 'คนมาอาศัยเพิ่ม' อยู่ข้างในไหม
                        return str_contains($detail->name, $setting->name);
                    });

                    // ถ้าเจอหมวดที่ตรงกัน ให้ใช้ชื่อหมวดหมู่เป็น Key เพื่อให้ตรงกับหัวตาราง
                    $key = $matchedCategory ? $matchedCategory->name : $detail->name;

                    $detailsMap[$key] = ($detailsMap[$key] ?? 0) + $detail->subtotal;
                }
            }

            $room->expense_details = $detailsMap;

            $lastPayment = $invoice
                ? $invoice->payments->where('status', 'active')->sortByDesc('payment_date')->first()
                : null;
            $room->payment_date_display = $lastPayment ? $this->toThaiDate($lastPayment->payment_date, true, true) : '-';
        });

        $thai_month = $this->toThaiDate($billing_month, false);

        return view('admin.invoices.collection_report', compact(
            'rooms',
            'billing_month',
            'thai_month',
            'status_filter',
            'allExpenseSettings',
            'show_columns'
        ));
    }

    public function printCollectionReportPdf(Request $request)
    {
        $billing_month = $request->billing_month ?? date('Y-m');
        $status_filter = $request->input('status_filter', ['มีผู้เช่า', 'ว่าง']);

        // 1. ดึงข้อมูลค่าใช้จ่ายแบบไดนามิก
        $allExpenseSettings = TenantExpense::select('name')->get();
        if (!$allExpenseSettings->contains('name', 'ค่าเช่าห้อง')) {
            $allExpenseSettings->prepend((object) ['name' => 'ค่าเช่าห้อง']);
        }
        $show_columns = $request->input('show_columns', $allExpenseSettings->pluck('name')->toArray());

        // 2. Query ข้อมูลห้องพักและผู้เช่า
        $rooms = Room::with([
            'tenants' => function ($q) {
                $q->where('status', 'กำลังใช้งาน');
            }
        ])
            ->whereIn('status', $status_filter)
            ->get();

        // 3. ดึง Invoice และจัดกลุ่มยอดเงิน
        $invoices = Invoice::with(['details', 'payments'])
            ->where('billing_month', $billing_month)
            ->get()
            ->keyBy('room_id');

        $rooms->each(function ($room) use ($invoices) {
            $invoice = $invoices->get($room->id);
            $room->current_invoice = $invoice;
            $room->expense_details = $invoice ? $invoice->details->groupBy('name')->map->sum('subtotal') : collect();
            $lastPayment = $invoice ? $invoice->payments->where('status', 'active')->sortByDesc('payment_date')->first() : null;
            $room->payment_date_display = $lastPayment ? $this->toThaiDate($lastPayment->payment_date, true, true) : '-';
        });

        $thai_month = $this->toThaiDate($billing_month, false);
        $apartment = DB::table('apartment')->first();

        // 4. โหลด View สำหรับ PDF และตั้งค่าแนวนอน (Landscape)
        $pdf = Pdf::loadView('admin.invoices.print_collection_report_pdf', compact(
            'rooms',
            'billing_month',
            'thai_month',
            'status_filter',
            'show_columns',
            'apartment'
        ))->setPaper('a4', 'landscape'); // แนวนอนเพื่อให้เห็นครบทุกคอลัมน์

        return $pdf->stream('collection_report_' . $billing_month . '.pdf');
    }

    // แปลงเป็นวันที่ไทย
    private function toThaiDate($date, $showDay = true, $shortMonth = false)
    {
        if (!$date)
            return '-';

        $carbon = \Carbon\Carbon::parse($date)->locale('th');
        $thaiYear = $carbon->year + 543; // แปลงเป็น พ.ศ.

        // 💡 กำหนด Format: 'MMM' คือเดือนย่อ (ม.ค.), 'MMMM' คือเดือนเต็ม (มกราคม)
        $monthFormat = $shortMonth ? 'MMM' : 'MMMM';

        if ($showDay) {
            // ผลลัพธ์ตัวอย่าง: 13 ม.ค. 2569
            return $carbon->isoFormat("D $monthFormat") . ' ' . $thaiYear;
        } else {
            // ผลลัพธ์ตัวอย่าง: ม.ค. 2569
            return $carbon->isoFormat($monthFormat) . ' ' . $thaiYear;
        }
    }
    public function insertInvoiceOne(Request $request)
    {
        // 1. Validation: ตรวจสอบข้อมูลนำเข้าเบื้องต้น
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'billing_month' => 'required|string|max:7',
            'issue_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            // 2. ป้องกันการสร้างบิลซ้ำในเดือนเดียวกัน
            $exists = Invoice::where('room_id', $request->room_id)
                ->where('billing_month', $request->billing_month)
                ->exists();
            if ($exists) {
                return redirect()->back()->withErrors(['error' => 'ห้องนี้มีการสร้างบิลสำหรับเดือนนี้ไปแล้ว']);
            }

            // 3. ดึงข้อมูล Room, RoomPrice และ Tenant
            $room = Room::with([
                'roomPrice',
                'tenants' => function ($q) {
                    $q->where('status', 'กำลังใช้งาน');
                }
            ])->findOrFail($request->room_id);

            $tenant = $room->tenants->first();

            // 4. ตรวจสอบความพร้อมของข้อมูลผู้เช่า
            if (!$tenant) {
                throw new \Exception("ไม่พบผู้เช่าที่กำลังใช้งานในห้องนี้ ไม่สามารถสร้างบิลได้");
            }

            // 5. คำนวณเลขที่บิลและวันครบกำหนด (วันที่ 5 ของเดือนถัดไป)
            $invoiceNumber = 'INV' . str_replace('-', '', $request->billing_month) . '-' . $room->room_number;
            $dueDate = \Carbon\Carbon::parse($request->billing_month)->addMonth()->startOfMonth()->addDays(4);

            // 6. สร้าง Invoice หลัก
            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'user_id' => Auth::id(), // ระบุตัวตน Admin 
                'invoice_number' => $invoiceNumber,
                'billing_month' => $request->billing_month,
                'issue_date' => $request->issue_date,
                'total_amount' => 0, // รออัปเดตหลังจากคำนวณรายการย่อย
                'status' => 'กรุณาส่งบิล',
                'due_date' => $dueDate,
            ]);

            $totalAmount = 0;
            $expenses = TenantExpense::all(); // โหลดรายการค่าใช้จ่ายทั้งหมด

            // 7. รายการที่ 1: ค่าเช่าห้อง
            $roomPrice = $room->roomPrice->price ?? 0;
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'name' => 'ค่าเช่าห้อง',
                'quantity' => 1,
                'price_per_unit' => $roomPrice,
                'subtotal' => $roomPrice
            ]);
            $totalAmount += $roomPrice;

            // 8. รายการที่ 2: ค่าน้ำ/ค่าไฟ (คำนวณตามหน่วยที่ใช้จริง)
            $meterReadings = MeterReading::where('room_id', $room->id)
                ->where('billing_month', $request->billing_month)
                ->get();

            foreach ($meterReadings as $reading) {
                $type = $reading->meter_type == 'water' ? 'ค่าน้ำ' : 'ค่าไฟ';
                $tenant_expense = $expenses->where('name', $type)->first();
                $rate = $tenant_expense->price ?? 0;
                $sub = $reading->units_used * $rate;

                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'tenant_expense_id' => $tenant_expense->id ?? null,
                    'meter_reading_id' => $reading->id,
                    'name' => $tenant_expense->name ?? $type,
                    'previous_unit' => $reading->previous_value,
                    'current_unit' => $reading->current_value,
                    'quantity' => $reading->units_used,
                    'price_per_unit' => $rate,
                    'subtotal' => $sub
                ]);
                $totalAmount += $sub;
            }

            // 9. รายการที่ 3: ค่าคนมาอาศัยเพิ่ม (เช็คจาก ID 5)
            if ($tenant->resident_count > 2) {
                $extraPeople = $tenant->resident_count - 2;
                $extraExpense = $expenses->where('id', 5)->first();
                $pricePerPerson = $extraExpense->price ?? 400.00;

                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'tenant_expense_id' => $extraExpense->id ?? null,
                    'name' => ($extraExpense->name ?? 'คนมาอาศัยเพิ่ม') . ' (ส่วนเกิน ' . $extraPeople . ' คน)',
                    'quantity' => $extraPeople,
                    'price_per_unit' => $pricePerPerson,
                    'subtotal' => $extraPeople * $pricePerPerson
                ]);
                $totalAmount += ($extraPeople * $pricePerPerson);
            }

            // 10. รายการที่ 4: ค่าที่จอดรถ (เช็คจาก ID 3)
            if ($tenant->has_parking) {
                $parking = $expenses->where('id', 3)->first();
                if ($parking) {
                    InvoiceDetail::create([
                        'invoice_id' => $invoice->id,
                        'tenant_expense_id' => $parking->id,
                        'name' => $parking->name,
                        'quantity' => 1,
                        'price_per_unit' => $parking->price,
                        'subtotal' => $parking->price
                    ]);
                    $totalAmount += $parking->price;
                }
            }

            // 11. อัปเดตยอดรวมเงินสุทธิใน Invoice หลัก
            $invoice->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->back()->with('success', 'สร้างบิลค่าเช่าห้อง ' . $room->room_number . ' สำเร็จ')->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    // **************************************
    // create ใบทั้งหมดในเดือนนั้นๆ
    public function insertInvoicesAll(Request $request)
    {
        // 1. Validation ข้อมูลนำเข้า
        $request->validate([
            'billing_month' => 'required|string|max:7', // เช่น 2026-01
            'issue_date' => 'required|date'
        ]);

        $billing_month = $request->billing_month;
        $issue_date = $request->issue_date;

        try {
            DB::beginTransaction();

            // ดึงเฉพาะห้องที่มีสถานะ "มีผู้เช่า"
            $rooms = Room::where('status', 'มีผู้เช่า')->get();
            $count = 0;

            foreach ($rooms as $room) {
                // เรียกใช้ Logic การสร้างบิลทีละห้อง
                $result = $this->generateInvoiceLogic($room->id, $billing_month, $issue_date);
                if ($result)
                    $count++;
            }

            DB::commit();

            if ($count > 0) {
                return redirect()->back()->with('success', "สร้างบิลสำเร็จจำนวน $count ห้อง (เฉพาะห้องที่จดมิเตอร์ครบแล้ว)")->withInput();
            } else {
                return redirect()->back()->withErrors(['error' => "ไม่มีห้องใดที่ตรงตามเงื่อนไข (อาจจดมิเตอร์ไม่ครบ หรือมีบิลอยู่แล้ว)"])->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    /**
     * Logic หลักในการสร้างใบแจ้งหนี้
     */
    private function generateInvoiceLogic($roomId, $billingMonth, $issueDate)
    {
        // 1. ป้องกันการสร้างบิลซ้ำในเดือนเดียวกัน
        $exists = Invoice::where('room_id', $roomId)
            ->where('billing_month', $billingMonth)
            ->exists();
        if ($exists)
            return null;

        // 2. ตรวจสอบความพร้อมของข้อมูลมิเตอร์ (ต้องมีทั้งค่าน้ำและค่าไฟ)
        $meterReadings = MeterReading::where('room_id', $roomId)
            ->where('billing_month', $billingMonth)
            ->get();

        if (
            $meterReadings->where('meter_type', 'water')->isEmpty() ||
            $meterReadings->where('meter_type', 'electric')->isEmpty()
        ) {
            return null; // ข้ามห้องที่ยังจดมิเตอร์ไม่ครบ
        }

        // 3. ดึงข้อมูลห้อง ผู้เช่า และราคามาตรฐาน
        $room = Room::with([
            'roomPrice',
            'tenants' => function ($q) {
                $q->where('status', 'กำลังใช้งาน');
            }
        ])->findOrFail($roomId);

        $tenant = $room->tenants->first();
        if (!$tenant)
            return null;

        $expenses = TenantExpense::all(); // โหลดรายการค่าใช้จ่ายมาตรฐาน

        // 4. คำนวณข้อมูลบิลเบื้องต้น
        $invoiceNumber = 'INV' . str_replace('-', '', $billingMonth) . '-' . $room->room_number;
        // กำหนดชำระเป็นวันที่ 5 ของเดือนถัดไป
        $dueDate = \Carbon\Carbon::parse($billingMonth)->addMonth()->startOfMonth()->addDays(4);

        // 5. สร้าง Invoice หลัก
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'user_id' => Auth::id(), // ระบุตัวตน Admin 
            'invoice_number' => $invoiceNumber,
            'billing_month' => $billingMonth,
            'issue_date' => $issueDate, // วันที่เลือกมาจากแอดมิน
            'total_amount' => 0, // รออัปเดตท้ายสุด
            'status' => 'กรุณาส่งบิล',
            'due_date' => $dueDate,
        ]);

        $totalAmount = 0;

        // 6. เพิ่มรายการย่อย (InvoiceDetails) -----------------------------------

        // รายการที่ 1: ค่าเช่าห้อง
        $roomPrice = $room->roomPrice->price ?? 0;
        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'name' => 'ค่าเช่าห้อง',
            'quantity' => 1,
            'price_per_unit' => $roomPrice,
            'subtotal' => $roomPrice
        ]);
        $totalAmount += $roomPrice;

        // รายการที่ 2: ค่าน้ำ/ค่าไฟ (ตามหน่วยที่ใช้จริง)
        foreach ($meterReadings as $reading) {
            $type = $reading->meter_type == 'water' ? 'ค่าน้ำ' : 'ค่าไฟ';
            $expense = $expenses->where('name', $type)->first();
            $rate = $expense->price ?? 0;
            $sub = $reading->units_used * $rate;

            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'tenant_expense_id' => $expense->id ?? null,
                'meter_reading_id' => $reading->id,
                'name' => $expense->name ?? $type,
                'previous_unit' => $reading->previous_value,
                'current_unit' => $reading->current_value,
                'quantity' => $reading->units_used,
                'price_per_unit' => $rate,
                'subtotal' => $sub
            ]);
            $totalAmount += $sub;
        }

        // รายการที่ 3: ค่าคนมาอาศัยเพิ่ม (ส่วนเกินจาก 2 คน)
        if ($tenant->resident_count > 2) {
            $extraPeople = $tenant->resident_count - 2;
            $extraExpense = $expenses->where('id', 5)->first(); // สมมติ ID 5 คือค่าคนเพิ่ม
            $pricePerPerson = $extraExpense->price ?? 400.00;

            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'tenant_expense_id' => $extraExpense->id ?? null,
                'name' => ($extraExpense->name ?? 'คนมาอาศัยเพิ่ม') . " (ส่วนเกิน $extraPeople คน)",
                'quantity' => $extraPeople,
                'price_per_unit' => $pricePerPerson,
                'subtotal' => $extraPeople * $pricePerPerson
            ]);
            $totalAmount += ($extraPeople * $pricePerPerson);
        }

        // รายการที่ 4: ค่าที่จอดรถ
        if ($tenant->has_parking) {
            $parking = $expenses->where('id', 3)->first(); // สมมติ ID 3 คือค่าจอดรถ
            if ($parking) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'tenant_expense_id' => $parking->id,
                    'name' => $parking->name,
                    'quantity' => 1,
                    'price_per_unit' => $parking->price,
                    'subtotal' => $parking->price
                ]);
                $totalAmount += $parking->price;
            }
        }

        // 7. อัปเดตยอดรวมเงินสุทธิใน Invoice
        $invoice->update(['total_amount' => $totalAmount]);

        return $invoice;
    }

    // **************************************
    public function readInvoiceDetails($id)
    {
        // 1. ดึงข้อมูลใบแจ้งหนี้พร้อมความสัมพันธ์
        $invoice = Invoice::with([
            'details',
            'tenant',
            'tenant.room',
            'tenant.room.roomPrice',
            'details.meterReading'
        ])->findOrFail($id);

        // 2. จัดการวันที่ภาษาไทยโดยเรียกใช้ฟังก์ชัน toThaiDate

        // แปลง billing_month (ประจำเดือน) -> มกราคม 2569 (ไม่เอาวัน)
        $invoice->thai_billing_month = $this->toThaiDate($invoice->billing_month, false);

        // แปลง issue_date (วันที่ออกบิล) -> 12 มกราคม 2569
        $invoice->thai_issue_date = $this->toThaiDate($invoice->issue_date);

        // แปลง due_date (กำหนดชำระ) -> 05 กุมภาพันธ์ 2569
        $invoice->thai_due_date = $this->toThaiDate($invoice->due_date);

        // หาวันที่จดมิเตอร์จากรายการแรกที่มีข้อมูล
        $firstReading = $invoice->details->whereNotNull('meter_reading_id')->first();
        $invoice->thai_reading_date = ($firstReading && $firstReading->meterReading)
            ? $this->toThaiDate($firstReading->meterReading->reading_date)
            : '-';

        // 3. แปลงยอดรวมเป็นตัวอักษรภาษาไทย
        $invoice->total_amount_thai = $this->bahtText($invoice->total_amount);

        // 4. ดึงข้อมูลอพาร์ทเม้นท์
        $apartment = DB::table('apartment')->first();

        return view('admin.invoices.invoice_details', compact('invoice', 'apartment'));
    }
    public function printInvoiceDetails($id)
    {
        // 1. ดึงข้อมูล (ใช้ Logic เดียวกับ readInvoiceDetails)
        $invoice = Invoice::with(['details', 'tenant', 'tenant.room', 'details.meterReading'])->findOrFail($id);
        $invoice->thai_billing_month = $this->toThaiDate($invoice->billing_month, false);
        $invoice->thai_issue_date = $this->toThaiDate($invoice->issue_date);
        $invoice->thai_due_date = $this->toThaiDate($invoice->due_date);

        $firstReading = $invoice->details->whereNotNull('meter_reading_id')->first();
        $invoice->thai_reading_date = ($firstReading && $firstReading->meterReading)
            ? $this->toThaiDate($firstReading->meterReading->reading_date) : '-';

        $invoice->total_amount_thai = $this->bahtText($invoice->total_amount);
        $apartment = DB::table('apartment')->first();

        // 2. โหลด View และตั้งค่ากระดาษ
        $pdf = Pdf::loadView('admin.invoices.print_pdf_invoiceDetails', compact('invoice', 'apartment'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // 3. ส่งไฟล์ให้ Browser แสดงผล (Stream) หรือดาวน์โหลด (Download)
        return $pdf->stream('invoice_' . $invoice->invoice_number . '.pdf');
    }
    /**
     * ฟังก์ชันสำหรับแปลงตัวเลขเป็นตัวอักษรภาษาไทย (Baht Text)
     */
    private function bahtText($number)
    {
        $number = number_format($number, 2, '.', '');
        $number_parts = explode('.', $number);
        $baht = $number_parts[0];
        $satang = $number_parts[1];

        $result = $this->convertText($baht) . 'บาท';

        if ($satang == '00') {
            $result .= 'ถ้วน';
        } else {
            $result .= $this->convertText($satang) . 'สตางค์';
        }

        return $result;
    }

    private function convertText($number)
    {
        $txtnum_th = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $txtunit_th = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
        $result = "";
        $len = strlen($number);

        for ($i = 0; $i < $len; $i++) {
            $digit = substr($number, $i, 1);
            if ($digit != '0') {
                if ($i == ($len - 1) && $digit == '1' && $len > 1) {
                    $result .= 'เอ็ด';
                } elseif ($i == ($len - 2) && $digit == '2') {
                    $result .= 'ยี่สิบ';
                } elseif ($i == ($len - 2) && $digit == '1') {
                    $result .= 'สิบ';
                } else {
                    $result .= $txtnum_th[$digit] . $txtunit_th[$len - $i - 1];
                }
            }
        }
        return $result;
    }
    // create มิเตอร์ 1 อัน ต่อ 1 เดือน
    public function insertInvoiceMeterReadingOne(Request $request)
    {
        $request->validate([
            'billing_month' => 'required',
            'room_id' => 'required',
            'tenant_id' => 'required',
            'water_current' => 'required|numeric|min:0',
            'electric_current' => 'required|numeric|min:0',
            'reading_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $roomId = $request->room_id;
            $tenantId = $request->tenant_id;
            $month = $request->billing_month;

            // ข้อมูลมิเตอร์จาก Modal
            $meters = [
                'water' => [
                    'prev' => (float) $request->water_prev,
                    'current' => (float) $request->water_current,
                ],
                'electric' => [
                    'prev' => (float) $request->electric_prev,
                    'current' => (float) $request->electric_current,
                ],
            ];

            foreach ($meters as $type => $values) {
                // ใช้ updateOrCreate เพื่อป้องกันข้อมูลซ้ำในเดือนเดียวกัน
                MeterReading::updateOrCreate(
                    [
                        'room_id' => $roomId,
                        'meter_type' => $type,
                        'billing_month' => $month,
                    ],
                    [
                        'tenant_id' => $tenantId,
                        'previous_value' => $values['prev'],
                        'current_value' => $values['current'],
                        'units_used' => $values['current'] - $values['prev'],
                        'reading_date' => $request->reading_date,
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'บันทึกเลขมิเตอร์ห้อง ' . $request->room_number . ' สำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    // ดึงข้อมูลสำหรับหน้าแก้ไข

    public function editInvoiceDetails($id)
    {
        $invoice = Invoice::with(['details', 'tenant.room'])->findOrFail($id);
        $apartment = DB::table('apartment')->first();

        // ดึงรายการค่าใช้จ่ายทั้งหมดเพื่อทำ Dropdown 
        // WhereNotin ยกเว้นไม่เลือก id นั้นๆ
        $expenses = TenantExpense::whereNotIn('id', [1, 2])->orderBy('id', 'asc')->get();
        $thai_billing_month = $this->toThaiDate($invoice->billing_month, false);
        return view('admin.invoices.edit_details', compact('invoice', 'apartment', 'expenses', 'thai_billing_month'));
    }

    // บันทึกการแก้ไข
    public function updateInvoiceDetails(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $invoice = Invoice::findOrFail($id);
            $invoice->details()->delete(); // ลบรายการเดิม

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $subtotal = (float) $item['quantity'] * (float) $item['price'];

                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'tenant_expense_id' => $item['expense_id'] ?? null,
                    'meter_reading_id' => $item['meter_reading_id'] ?? null, // รักษา ID มิเตอร์ไว้
                    'name' => $item['name'],
                    'previous_unit' => $item['previous_unit'] ?? null,
                    'current_unit' => $item['current_unit'] ?? null,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }

            $invoice->update([
                'total_amount' => $totalAmount, // ยอดรวม
                'issue_date' => $request->issue_date // บันทึกเวลาใหม่
            ]);
            DB::commit();
            return redirect()->route('admin.invoices.details', $id)->with('success', 'แก้ไขข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function sendInvoiceOne(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required'
        ]);
        try {
            DB::beginTransaction();
            Invoice::where('id', $request->invoice_id)
                ->update([
                    'status' => 'ค้างชำระ'
                ]);
            DB::commit();
            return redirect()->back()->with('success', 'ส่งบิลสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด' . $e->getMessage()]);
        }
    }

    public function sendInvoiceAll(Request $request)
    {
        $request->validate([
            'billing_month' => 'required|string|max:7'
        ]);
        try {
            DB::beginTransaction();
            // ค้นหา update ใบ invoice เงื่อนไข ใน "billing_month" รอบเดือนนั้นๆ และ มีสถานะ "กรุณาส่งบิล"
            $affectedRows = Invoice::where('billing_month', $request->billing_month)
                ->where('status', 'กรุณาส่งบิล')
                ->update(['status' => 'ค้างชำระ']);

            if ($affectedRows > 0) {
                DB::commit();
                return redirect()->back()->with('success', "ส่งบิลสำเร็จทั้งหมด $affectedRows ห้อง เรียบร้อยแล้ว");
            } else {
                // กรณีไม่มีการ update
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'ไม่พบบิลที่พร้อมส่งในรอบเดือนนี้ หรือบิลถูกส่งไปหมดแล้ว']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด' . $e->getMessage()]);
        }
    }

    public function deleteInvoiceOne($id)
    {
        try {
            DB::beginTransaction();
            DB::table('invoices')->where('id', $id)->delete();
            // บันทึกการเปลี่ยนแปลง
            DB::commit();
            return redirect()->back()->with('success', 'ลบใบแจ้งหนี้สำเร็จ');
        } catch (\Exception $e) {
            // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);

        }
    }
    // ---------------------------------------------

    // ระบบจัดการ accounting_category

    public function accountingCategoryShow()
    {
        // ดึงหมวดหมู่รายรับ (type_id = 1)
        $income_categories = AccountingCategory::where('type_id', 1)->get();

        // ดึงหมวดหมู่รายจ่าย (type_id = 2)
        $expense_categories = AccountingCategory::where('type_id', 2)->get();

        return view('admin.accounting_categories.show', compact('income_categories', 'expense_categories'));
    }

    public function insertAccountingCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|in:1,2'
        ]);
        try {
            DB::beginTransaction();
            AccountingCategory::create([
                'type_id' => $request->type_id,
                'name' => $request->name,
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'เพิ่มหมวดหมู่สำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด' . $e->getMessage()]);
        }
    }

    public function updateAccountingCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|in:1,2'
        ]);
        try {
            DB::beginTransaction();
            $category = AccountingCategory::findOrFail($id);
            $category->update($request->only(['name', 'type_id']));
            DB::commit();
            return redirect()->back()->with('success', 'แก้ไขข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด' . $e->getMessage()]);
        }
    }

    // ---------------------------------------------

    // จัดการ payment การชำระค่าเช่าของ admin ให้ admin จัดการจ่ายค่าเช่าลงระบบ

    public function pendingInvoicesShow(Request $request)
    {
        $searchRoom = $request->input('search_room');
        $filterMonth = $request->input('filter_month'); // เพิ่มการรับค่าเดือน

        $query = Invoice::with(['tenant.room', 'tenant', 'payments', 'details'])
            ->whereIn('invoices.status', ['ค้างชำระ', 'ชำระบางส่วน']);

        // 1. ค้นหาตามเลขห้อง
        if ($searchRoom) {
            $query->whereHas('tenant.room', function ($q) use ($searchRoom) {
                $q->where('room_number', 'like', "%{$searchRoom}%");
            });
        }

        // 2. กรองตามรอบเดือน (YYYY-MM)
        if ($filterMonth) {
            $query->where('billing_month', $filterMonth);
        }

        $pendingInvoices = $query->join('rooms', 'invoices.room_id', '=', 'rooms.id')
            ->select('invoices.*')
            ->orderBy('rooms.room_number', 'asc')
            ->orderBy('invoices.due_date', 'desc')
            ->get();

        foreach ($pendingInvoices as $inv) {
            $inv->thai_due_date = $this->toThaiDate($inv->due_date);
        }

        // 3. ดึงรายการเดือนที่มี "บิลค้างชำระ" อยู่จริงเพื่อแสดงในตัวเลือก
        $availableMonths = Invoice::whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
            ->select('billing_month')
            ->distinct()
            ->orderBy('billing_month', 'desc')
            ->get();

        foreach ($availableMonths as $m) {
            $m->thai_billing_month = $this->toThaiDate($m->billing_month, false);
        }

        $arrearsCount = $pendingInvoices->groupBy('room_id')->map->count();

        return view('admin.payments.pendingInvoices', compact('pendingInvoices', 'arrearsCount', 'searchRoom', 'filterMonth', 'availableMonths'));
    }

    public function insertPayment_and_AccountingTransaction_of_Tenant(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'slip_image' => 'nullable|image|max:2048',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::with(['details', 'tenant.room', 'payments'])->findOrFail($request->invoice_id);

            $paidAmount = (float) $request->amount_paid;
            $currentRemaining = (float) $invoice->remaining_balance;

            // 1. บันทึกข้อมูลลงตาราง payments
            $path = $request->hasFile('slip_image') ? $request->file('slip_image')->store('slips', 'public') : null;
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'amount_paid' => $paidAmount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'slip_image' => $path,
                'note' => $request->note,
            ]);

            // 2. กระจายยอดเงินลงหมวดบัญชี (Accounting Transactions)
            $paidRemaining = $paidAmount;

            foreach ($invoice->details as $detail) {
                if ($paidRemaining <= 0)
                    break;

                // หาหมวดหมู่บัญชี
                $expenseSetting = TenantExpense::find($detail->tenant_expense_id);
                $categoryId = $expenseSetting->accounting_category_id ?? 1;

                // --- 💡 ส่วนที่แก้ไข: คำนวณยอดที่เคยจ่ายไปแล้วสำหรับรายการนี้ ---
                // ดึงยอดรวมจาก accounting_transactions ที่เคยบันทึกไว้ภายใต้บิลนี้และหมวดหมู่นี้
                $alreadyPaidForItem = DB::table('accounting_transactions')
                    ->join('payments', 'accounting_transactions.payment_id', '=', 'payments.id')
                    ->where('payments.invoice_id', $invoice->id)
                    ->where('accounting_transactions.category_id', $categoryId)
                    ->sum('accounting_transactions.amount');

                // คำนวณยอดคงเหลือของรายการนี้ (ยอดเต็ม - ที่เคยจ่ายไปแล้ว)
                $itemBalance = $detail->subtotal - $alreadyPaidForItem;

                if ($itemBalance > 0) {
                    // ปันส่วนเงินรอบใหม่เข้ารายการนี้ตามยอดที่ยังขาดอยู่
                    $allocation = min($paidRemaining, $itemBalance);

                    AccountingTransaction::create([
                        'category_id' => $categoryId,
                        'payment_id' => $payment->id,
                        'tenant_id' => $invoice->tenant_id,
                        'user_id' => Auth::id(),
                        'title' => $detail->name . " (ห้อง " . $invoice->tenant->room->room_number . ")",
                        'amount' => $allocation,
                        'entry_date' => $request->payment_date,
                        'description' => "ชำระเงิน (บางส่วน/เต็ม) ช่องทาง: " . $payment->payment_method,
                        'status' => 'active',
                    ]);

                    $paidRemaining -= $allocation;
                }
            }

            // 3. อัปเดตสถานะบิล
            if ($paidAmount >= $currentRemaining) {
                $invoice->status = 'ชำระแล้ว';
            } else {
                $invoice->status = 'ชำระบางส่วน';
            }
            $invoice->save();

            DB::commit();
            return redirect()->route('admin.payments.pendingInvoicesShow')->with('success', 'บันทึกการชำระเงินสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function paymentHistory(Request $request)
    {
        // รับค่าตัวกรอง
        $filterRoom = $request->input('filter_room');
        $filterMethod = $request->input('filter_method');
        $filterMonth = $request->input('filter_month');
        $filterPayer = $request->input('filter_payer');      // ค้นหาผู้ชำระ
        $filterReceiver = $request->input('filter_receiver'); // ค้นหาผู้รับเงิน
        $filterStatus = $request->input('filter_status');

        $query = Payment::with(['invoice.tenant.room', 'invoice.admin', 'admin']);

        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        // กรองตามเลขห้อง
        if ($filterRoom) {
            $query->whereHas('invoice.tenant.room', fn($q) => $q->where('room_number', 'like', "%{$filterRoom}%"));
        }

        // ค้นหาชื่อ-นามสกุล ผู้ชำระ (Tenant)
        if ($filterPayer) {
            $query->whereHas('invoice.tenant', function ($q) use ($filterPayer) {
                $q->where('first_name', 'like', "%{$filterPayer}%")
                    ->orWhere('last_name', 'like', "%{$filterPayer}%");
            });
        }

        // ค้นหาชื่อ-นามสกุล ผู้รับเงิน (Admin)
        if ($filterReceiver) {
            $query->whereHas('admin', function ($q) use ($filterReceiver) {
                $q->where('firstname', 'like', "%{$filterReceiver}%")
                    ->orWhere('lastname', 'like', "%{$filterReceiver}%");
            });
        }

        // ตัวกรองอื่นๆ
        if ($filterMethod)
            $query->where('payment_method', $filterMethod);
        if ($filterMonth)
            $query->whereHas('invoice', fn($q) => $q->where('billing_month', $filterMonth));

        $history = $query->orderBy('payment_date', 'desc')->orderBy('created_at', 'desc')->paginate(20);
        $displayTitle = $filterMonth ? "ประวัติการชำระเงินรอบเดือน " . $this->toThaiDate($filterMonth, false) : "ประวัติการชำระเงินทั้งหมด";
        // ส่วนการแปลงวันที่ไทย
        foreach ($history as $pay) {
            $pay->thai_payment_date = $this->toThaiDate($pay->payment_date);
        }

        $availableMonths = Invoice::whereHas('payments')->select('billing_month')->distinct()->orderBy('billing_month', 'desc')->get();
        foreach ($availableMonths as $m) {
            $m->thai_billing_month = $this->toThaiDate($m->billing_month, false);
        }

        return view('admin.payments.history', compact('history', 'availableMonths', 'filterRoom', 'filterMethod', 'filterPayer', 'filterReceiver', 'filterMonth', 'filterStatus', 'displayTitle'));
    }
    //เพิ่มฟังก์ชันดึงรายละเอียดการชำระเงินผ่าน AJAX
    public function getPaymentDetail($id)
    {
        $pay = Payment::with(['invoice.tenant.room', 'invoice.admin', 'admin'])->findOrFail($id);
        return response()->json([
            'room' => $pay->invoice->tenant->room->room_number,
            'date' => $this->toThaiDate($pay->payment_date),
            'time' => $pay->created_at->format('H:i') . ' น.',
            'amount' => number_format($pay->amount_paid, 2),
            'method' => $pay->payment_method,
            'tenant' => ($pay->invoice->tenant->first_name ?? 'N/A') . ' ' . ($pay->invoice->tenant->last_name ?? ''),
            'receiver' => ($pay->admin->firstname ?? 'System') . ' ' . ($pay->admin->lastname ?? ''),
            'note' => $pay->note ?? '-',
            'slip' => $pay->slip_image ? asset('storage/' . $pay->slip_image) : null,
            'invoice_no' => $pay->invoice->invoice_number
        ]);
    }
    public function updatePayment(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $pay = Payment::findOrFail($id);

            $pay->payment_method = $request->payment_method;
            $pay->note = $request->note;

            if ($request->hasFile('slip_image')) {
                // ลบรูปเก่าถ้ามี และบันทึกรูปใหม่
                if ($pay->slip_image)
                    \Storage::disk('public')->delete($pay->slip_image);
                $pay->slip_image = $request->file('slip_image')->store('slips', 'public');
            }
            $pay->save();
            DB::commit();
            return back()->with('success', 'อัปเดตข้อมูลการชำระเงินเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function voidPayment($id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::with('invoice.payments')->findOrFail($id);

            if ($payment->status === 'void') {
                return back()->with('error', 'รายการนี้ถูกยกเลิกไปก่อนหน้านี้แล้ว');
            }

            // 1. เปลี่ยนสถานะ Payment เป็น void
            $payment->status = 'void';
            $payment->save();

            // 2. ยกเลิกรายการบัญชีที่เกี่ยวข้อง
            AccountingTransaction::where('payment_id', $payment->id)->update(['status' => 'void']);

            // 3. ปรับปรุงสถานะ Invoice
            $invoice = $payment->invoice;
            $totalPaidActive = $invoice->payments()->where('status', 'active')->sum('amount_paid');

            if ($totalPaidActive <= 0) {
                $invoice->status = 'ค้างชำระ';
            } elseif ($totalPaidActive < $invoice->total_amount) {
                $invoice->status = 'ชำระบางส่วน';
            } else {
                $invoice->status = 'ชำระแล้ว';
            }
            // แก้ไข: ลบบรรทัด $invoice->remaining_balance = ... ออก 
            // เพราะมันคือ virtual field ที่คำนวณสดจาก getTotalPaidAttribute
            $invoice->save();

            DB::commit();
            return back()->with('success', 'ยกเลิกรายการชำระเงินเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    // ---------------------------------------------

    // จัดการ รายรับรายจ่าย accounting_transaction

    public function accountingTransactionShow(Request $request)
    {
        // รับค่าจากฟอร์มและแถว Filter ในตาราง
        $startDate = $request->input('date_start');
        $endDate = $request->input('date_end');
        $typeId = $request->input('type_id');
        $categoryId = $request->input('category_id');
        $searchRoom = $request->input('search_room');
        $searchDetail = $request->input('search_detail'); // เพิ่มฟิลเตอร์รายละเอียด
        $filterAdmin = $request->input('filter_admin');   // ค้นหาผู้รับเงิน/ผู้บันทึก
        $filterStatus = $request->input('filter_status');

        $query = AccountingTransaction::with(['category.type', 'tenant.room', 'admin']);

        // --- Logic การกรองข้อมูล (Filter) ---
        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }
        if ($typeId) {
            $query->whereHas('category', function ($q) use ($typeId) {
                $q->where('type_id', $typeId);
            });
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($searchRoom) {
            $query->whereHas('tenant.room', function ($q) use ($searchRoom) {
                $q->where('room_number', 'like', "%{$searchRoom}%");
            });
        }
        if ($searchDetail) {
            $query->where('title', 'like', "%{$searchDetail}%");
        }
        // --- เพิ่มการกรองผู้บันทึก (Admin) ---
        if ($filterAdmin) {
            $query->whereHas('admin', function ($q) use ($filterAdmin) {
                $q->where('firstname', 'like', "%{$filterAdmin}%")
                    ->orWhere('lastname', 'like', "%{$filterAdmin}%");
            });
        }

        // --- เพิ่มการกรองสถานะ (active / void) ---
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        $transactions = $query->orderBy('entry_date', 'desc')->paginate(20);

        // --- ย้ายการคำนวณวันที่มาไว้ที่นี่ ---
        $carbonDate = $startDate ?? now()->locale('th');
        $displayDate = $this->toThaiDate($carbonDate, false);

        $categories = AccountingCategory::with('type')
            ->get()
            ->groupBy(function ($item) {
                return $item->type->name; // จัดกลุ่มตามชื่อประเภท เช่น "รายรับ", "รายจ่าย"
            });

        foreach ($transactions as $t) {
            $t->thai_entry_date = $this->toThaiDate($t->entry_date);
        }

        return view('admin.accounting_transactions.statementShow', compact(
            'transactions',
            'categories',
            'startDate',
            'endDate',
            'typeId',
            'categoryId',
            'searchRoom',
            'searchDetail',
            'filterAdmin',
            'filterStatus',
            'displayDate'
        ));
    }
    public function getTransactionDetail($id)
    {
        $transaction = AccountingTransaction::with(['category.type', 'tenant.room', 'admin', 'payment'])
            ->findOrFail($id);
        // ส่งค่ากลับเป็น JSON พร้อมแปลงวันที่ไทยให้พร้อมใช้
        return response()->json([
            'title' => $transaction->title,
            'amount' => number_format($transaction->amount, 2),
            'type' => $transaction->category->type->name,
            'category' => $transaction->category->name,
            'date' => $this->toThaiDate($transaction->entry_date),
            'room' => $transaction->tenant->room->room_number ?? '-',
            'admin' => ($transaction->admin->firstname ?? 'System') . ' ' . ($transaction->admin->lastname ?? ''),
            'description' => $transaction->description ?? '-',
            'payment_ref' => $transaction->payment_id ? "ชำระจากใบแจ้งหนี้ #" . $transaction->payment->invoice->invoice_number : 'บันทึกด้วยตนเอง'
        ]);
    }
    public function accountingTransactionCreate(Request $request)
    {
        $typeId = $request->query('type_id', 1); // รับค่าจากปุ่ม ถ้าไม่มีให้ default เป็น 1 (รายรับ)
        $typeName = ($typeId == 1) ? 'รายรับ' : 'รายจ่าย';

        // ดึงเฉพาะหมวดหมู่ที่ตรงกับประเภทที่เลือก
        $categories = AccountingCategory::where('type_id', $typeId)->get();

        // ดึงรายชื่อผู้เช่าเพื่อผูกกับเลขห้อง
        $tenants = Tenant::with('room')->where('status', 'พักอาศัย')->get();

        return view('admin.accounting_transactions.create', compact('categories', 'tenants', 'typeId', 'typeName'));
    }
    public function accountingTransactionStore(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'items' => 'required|array',
            'items.*.title' => 'required|string',
            'items.*.category_id' => 'required|exists:accounting_categories,id',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                AccountingTransaction::create([
                    'category_id' => $item['category_id'],
                    'tenant_id' => $item['tenant_id'] ?? null,
                    'user_id' => Auth::id(), // แอดมินผู้บันทึก
                    'title' => $item['title'],
                    'amount' => (float) $item['amount'],
                    'entry_date' => $request->entry_date,
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.accounting_transactions.show')->with('success', 'บันทึกรายการสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function voidTransaction($id)
    {
        DB::beginTransaction();
        try {
            $transaction = AccountingTransaction::findOrFail($id);

            if ($transaction->status === 'void') {
                return back()->with('error', 'รายการนี้ถูกยกเลิกไปแล้ว');
            }

            // ⚠️ ตรวจสอบว่ามาจากระบบจ่ายบิลหรือไม่
            if ($transaction->payment_id) {
                return back()->with('error', 'ไม่สามารถยกเลิกรายการนี้โดยตรงได้ เนื่องจากผูกกับใบแจ้งหนี้ กรุณายกเลิกผ่านหน้า "ประวัติการชำระเงิน"');
            }

            // เปลี่ยนสถานะเป็น void สำหรับรายการที่บันทึกเอง
            $transaction->status = 'void';
            $transaction->save();

            DB::commit();
            return back()->with('success', 'ยกเลิกรายการธุรกรรมเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    // ระบบรายงานทางการเงิน รายรับรายจ่าย

    public function reportSummary(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลธุรกรรมทั้งหมดในช่วงเวลา
        $transactions = AccountingTransaction::with(['category.type', 'tenant.room.roomPrice.building'])
            ->where('status', 'active')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        // 2. แยกรายรับที่ต้องจัดกลุ่มตามตึก (เฉพาะ ค่าเช่า และ ค่าไฟ)
        $buildingIncome = $transactions->where('category.type_id', 1)
            ->filter(function ($t) {
                return str_contains($t->title, 'ค่าเช่าห้อง') || str_contains($t->title, 'ค่าไฟ');
            })
            ->groupBy(function ($t) {
                return $t->tenant->room->roomPrice->building->name ?? 'ตึกทั่วไป';
            });

        // 3. รายรับอื่นๆ (ที่ไม่ใช่ค่าเช่า/ค่าไฟของตึก เช่น ค่าน้ำ, ที่จอดรถ, มัดจำ) [cite: 6]
        $otherIncome = $transactions->where('category.type_id', 1)
            ->filter(function ($t) {
                return !str_contains($t->title, 'ค่าเช่าห้อง') && !str_contains($t->title, 'ค่าไฟ');
            })
            ->groupBy('category.name')->map->sum('amount');

        // 4. รายจ่ายทั้งหมด
        $expenseByCats = $transactions->where('category.type_id', 2)
            ->groupBy('category.name')->map->sum('amount');

        // 5. คำนวณยอดค้างรับจากใบแจ้งหนี้
        $outstandingAmount = Invoice::whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get()
            ->sum('remaining_balance');

        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);

        // dd($buildingIncome, $otherIncome, $expenseByCats, $outstandingAmount);
        return view('admin.accounting_transactions.summary', compact(
            'buildingIncome',
            'otherIncome',
            'expenseByCats',
            'outstandingAmount',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate'
        ));
    }
    // ajax ดึงรายละเอียด summary
    public function getSummaryDetails(Request $request)
    {
        $startDate = $request->date_start;
        $endDate = $request->date_end;
        $target = $request->target;
        $name = $request->name;

        // 📝 กรณีที่ 1: ยอดค้างรับ (ดึงจาก Invoice และเชื่อม InvoiceDetails)
        if ($target === 'unpaid') {
            // ค้นหา Invoice ที่มีสถานะค้างชำระ ในช่วงวันที่เลือก และเรียงวันที่จากน้อยไปมาก
            $data = Invoice::with(['tenant.room', 'details'])
                ->whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
                ->whereBetween('issue_date', [$startDate, $endDate])
                ->orderBy('issue_date', 'asc') // ✅ เรียงจากน้อยไปมาก
                ->get()
                ->map(function ($inv) {
                    // ✅ แก้ไข: ใช้ Carbon::parse เพื่อป้องกัน Error กรณี issue_date เป็น String
    
                    $formattedDate = $this->toThaiDate($inv->issue_date, true, true);
                    return [
                        'date' => $formattedDate,
                        'title' => "ใบแจ้งหนี้ " . $inv->tenant->first_name . " " . $inv->tenant->last_name,
                        'description' => "-",
                        'room' => $inv->tenant->room->room_number ?? '-',
                        'amount' => number_format($inv->remaining_balance, 2), // ใช้ฟิลด์เสมือนคำนวณยอดคงเหลือ
                        'class' => 'text-danger'
                    ];
                });

            return response()->json(['title' => 'รายชื่อห้องที่ค้างชำระ (เรียงตามวันที่)', 'items' => $data]);
        }

        // 💸 กรณีที่ 2: ธุรกรรมรับ-จ่าย (เรียงจากน้อยไปมาก)
        $query = AccountingTransaction::with(['tenant.room', 'category'])
            ->where('status', 'active')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'asc'); // ✅ เรียงจากน้อยไปมาก

        if ($target === 'building_rent') {
            $query->where('title', 'like', '%ค่าเช่าห้อง%')
                ->whereHas('tenant.room.roomPrice.building', fn($q) => $q->where('name', $name));
            $modalTitle = "รายการค่าเช่า: $name";
        } elseif ($target === 'building_electric') {
            $query->where('title', 'like', '%ค่าไฟ%')
                ->whereHas('tenant.room.roomPrice.building', fn($q) => $q->where('name', $name));
            $modalTitle = "รายการค่าไฟ: $name";
        } elseif ($target === 'other_income') {
            $query->whereHas('category', fn($q) => $q->where('name', $name));
            $modalTitle = "รายการรายรับ: $name";
        } elseif ($target === 'expense') {
            $query->whereHas('category', fn($q) => $q->where('name', $name));
            $modalTitle = "รายการรายจ่าย: $name";
        }

        $items = $query->get()->map(function ($t) {
            $formattedDate = $this->toThaiDate($t->entry_date, true, true);
            return [
                'date' => $formattedDate,
                'title' => $t->title,
                'description' => $t->description ?? '-',
                'room' => $t->tenant->room->room_number ?? '-',
                'amount' => number_format($t->amount, 2),
                'class' => $t->category->type_id == 1 ? 'text-success' : 'text-danger'
            ];
        });

        return response()->json(['title' => $modalTitle ?? 'รายละเอียด', 'items' => $items]);
    }
    // print summary
    public function printSummaryPdf(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลธุรกรรม
        $transactions = AccountingTransaction::with(['category.type', 'tenant.room.roomPrice.building'])
            ->where('status', 'active')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        // 2. จัดกลุ่มรายรับตึก
        $buildingIncome = $transactions->where('category.type_id', 1)
            ->filter(fn($t) => str_contains($t->title, 'ค่าเช่าห้อง') || str_contains($t->title, 'ค่าไฟ'))
            ->groupBy(fn($t) => $t->tenant->room->roomPrice->building->name ?? 'ตึกทั่วไป');

        // 3. รายรับอื่นๆ
        $otherIncome = $transactions->where('category.type_id', 1)
            ->filter(fn($t) => !str_contains($t->title, 'ค่าเช่าห้อง') && !str_contains($t->title, 'ค่าไฟ'))
            ->groupBy('category.name')->map->sum('amount');

        // 4. รายจ่าย
        $expenseByCats = $transactions->where('category.type_id', 2)
            ->groupBy('category.name')->map->sum('amount');

        // 5. ยอดค้างรับ
        $outstandingAmount = Invoice::whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get()
            ->sum('remaining_balance');

        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);
        $apartment = DB::table('apartment')->first();

        $pdf = Pdf::loadView('admin.accounting_transactions.pdf.print_summary_pdf', compact(
            'buildingIncome',
            'otherIncome',
            'expenseByCats',
            'outstandingAmount',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate',
            'apartment'
        ))->setPaper('a4', 'portrait'); // งบสรุปแนะนำแนวตั้ง (Portrait)

        return $pdf->stream('Accounting_Summary_' . $startDate . '.pdf');
    }
    public function reportIncome(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลรายรับทั้งหมด (type_id = 1) และโหลดความสัมพันธ์ตึก
        $incomeTransactions = AccountingTransaction::with(['category', 'tenant.room.roomPrice.building'])
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type_id', 1))
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        // 2. จัดกลุ่มรายรับตามหมวดหมู่ใหญ่ และจัดกลุ่มย่อยตามตึก
        $incomeByGroup = $incomeTransactions->groupBy('category.name')->map(function ($items) {
            return $items->groupBy(function ($item) {
                return $item->tenant->room->roomPrice->building->name ?? '';
            });
        });

        // 3. คำนวณยอดค้างรับจาก Invoice (ค้างชำระ/ชำระบางส่วน)
        $outstandingDetails = Invoice::with('tenant.room')
            ->whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get();

        $outstandingAmount = $outstandingDetails->sum('remaining_balance');

        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);

        return view('admin.accounting_transactions.income', compact(
            'incomeByGroup',
            'outstandingAmount',
            'outstandingDetails',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate'
        ));
    }
    public function printIncomePdf(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลรายรับ (Logic เดียวกับ reportIncome)
        $incomeTransactions = AccountingTransaction::with(['category', 'tenant.room.roomPrice.building'])
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type_id', 1))
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        $incomeByGroup = $incomeTransactions->groupBy('category.name')->map(function ($items) {
            return $items->groupBy(function ($item) {
                return $item->tenant->room->roomPrice->building->name ?? '';
            });
        });

        // 2. ข้อมูลยอดค้างรับ
        $outstandingDetails = Invoice::with(['tenant.room', 'details'])
            ->whereIn('status', ['ค้างชำระ', 'ชำระบางส่วน'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get();

        $outstandingAmount = $outstandingDetails->sum('remaining_balance');
        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);
        $apartment = DB::table('apartment')->first();

        // 3. สร้าง PDF
        $pdf = Pdf::loadView('admin.accounting_transactions.pdf.print_income_pdf', compact(
            'incomeByGroup',
            'outstandingAmount',
            'outstandingDetails',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate',
            'apartment'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Income_Report_' . $startDate . '.pdf');
    }
    public function reportExpense(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลรายจ่ายทั้งหมด (type_id = 2)
        $expenseTransactions = AccountingTransaction::with(['category', 'admin'])
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type_id', 2))
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        // 2. จัดกลุ่มรายจ่ายตามชื่อหมวดหมู่
        $expenseByGroup = $expenseTransactions->groupBy('category.name');

        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);

        return view('admin.accounting_transactions.expense', compact(
            'expenseByGroup',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate'
        ));
    }
    public function printExpensePdf(Request $request)
    {
        $startDate = $request->input('date_start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->input('date_end') ?? now()->endOfMonth()->format('Y-m-d');

        // 1. ดึงข้อมูลรายจ่าย (Logic เดียวกับ reportExpense)
        $expenseTransactions = AccountingTransaction::with(['category', 'admin'])
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('type_id', 2))
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        // 2. จัดกลุ่มรายจ่ายตามชื่อหมวดหมู่
        $expenseByGroup = $expenseTransactions->groupBy('category.name');

        $displayDate = $this->toThaiDate($startDate, false);
        $thai_startDate = $this->toThaiDate($startDate);
        $thai_endDate = $this->toThaiDate($endDate);
        $apartment = DB::table('apartment')->first();

        // 3. สร้าง PDF ในแนวตั้ง (Portrait)
        $pdf = Pdf::loadView('admin.accounting_transactions.pdf.print_expense_pdf', compact(
            'expenseByGroup',
            'displayDate',
            'startDate',
            'endDate',
            'thai_startDate',
            'thai_endDate',
            'apartment'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Expense_Report_' . $startDate . '.pdf');
    }
    // ---------------------------------------------

    // จัดการผู้ดูแลระบบ Admin

    public function usersManageShow()
    {
        $admins = User::all();
        return view('admin.users_manage.show', compact('admins'));
    }

    public function insertUserManage(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'username' => 'required|string|max:50',
                'password' => 'required|string|min:6|confirmed',
                'firstname' => 'required|string|max:50',
                'lastname' => 'required|string|max:50',
                'role' => 'required|string|max:20',
            ]);
            User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'role' => $request->role,
                'status' => 'ใช้งาน',
            ]);
            DB::commit();
            return redirect()->route('admin.users_manage.show')->with('success', 'เพิ่มผู้ดูแลระบบสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateUserManage(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'username' => 'required|string|max:50',
                'password' => 'nullable|string|min:6|confirmed',
                'firstname' => 'required|string|max:50',
                'lastname' => 'required|string|max:50',
                'role' => 'required|string|max:20',
                'status' => 'required',
            ]);
            $data = [
                'username' => $request->username,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'role' => $request->role,
                'status' => $request->status,
            ];
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            User::where('id', $id)->update($data);
            DB::commit();
            return redirect()->route('admin.users_manage.show')->with('success', 'อัปเดตข้อมูลผู้ดูแลระบบสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteUserManage($id)
    {
        try {
            DB::beginTransaction();
            User::where('id', $id)->delete();
            DB::commit();
            return redirect()->route('admin.users_manage.show')->with('success', 'ลบผู้ดูแลระบบสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    // ---------------------------------------------
}
