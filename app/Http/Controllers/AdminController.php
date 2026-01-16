<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

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
            try{
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
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);
            }
        }

    // ---------------------------------------------

    // ไปหน้า จัดการประเภทตึก Building ตึก 2 4 5 ชั้น
        public function buildingShow()
        {
            $buildings = DB::table('buildings')->get();
            return view('admin.building.show', compact('buildings'));
        }

        public function updateBuilding(Request $request, $id){
            try{
                DB::beginTransaction();
                $request->validate([
                    'name' => 'required|string|max:255',
                ]);
                $data = [
                    'name' => $request->name,
                    'updated_at' => now(),
                ];
                DB::table('buildings')->where('id',$id)->update($data);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.building.show')->with('success','ข้อมูลอาคารถูกอัปเดตแล้ว');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }
    // ---------------------------------------------

    // จัดการประเภทห้อง Room Type
        public function roomTypeShow(){
            $room_types = DB::table('room_types')->get();
            return view('admin.room_types.show', compact('room_types'));
        }

        public function insertRoomType(Request $request){
            try{
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
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);
            }
        }

        public function updateRoomType(Request $request, $id){
            try{
                DB::beginTransaction();
                $request->validate([
                    'name' => 'required|string|max:255|unique:room_types,name,',
                ]);
                $data = [
                    'name' => $request->name,
                    'updated_at' => now(),
                ];
                DB::table('room_types')->where('id',$id)->update($data);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.room_types.show')->with('success','ข้อมูลประเภทห้องถูกอัปเดตแล้ว');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }
        public function deleteRoomType($id){
            try{
                DB::beginTransaction();
                DB::table('room_types')->where('id',$id)->delete();
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.room_types.show')->with('success','ลบประเภทห้องสำเร็จ');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }

    // ---------------------------------------------

    // จัดการราคาห้อง Room_price

        public function roomPriceShow(Request $request){
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

        public function insertRoomPrice(Request $request){
            try{
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
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);
            }
        }

        public function updateRoomPrice(Request $request, $id){
            try{
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
                $roomPrice = DB::table('room_prices')->where('id',$id)->first();
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
                DB::table('room_prices')->where('id',$id)->update($data);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.room_prices.show')->with('success','ข้อมูลราคาห้องถูกอัปเดตแล้ว');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }

        public function deleteRoomPrice($id){
            try{
                DB::beginTransaction();
                $roomPrice = DB::table('room_prices')->where('id',$id)->first();
                // ลบรูปภาพถ้ามี
                if (!empty($roomPrice->color_code) && Storage::disk('public')->exists($roomPrice->color_code)) {
                    Storage::disk('public')->delete($roomPrice->color_code);
                }
                DB::table('room_prices')->where('id',$id)->delete();
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.room_prices.show')->with('success','ลบราคาห้องสำเร็จ');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }
    // ---------------------------------------------

    // จัดการห้อง Rooms
        public function roomShow(Request $request){
            // 1. ดึงข้อมูล Rooms พร้อมความสัมพันธ์ (Room -> RoomPrice -> Building & RoomType)
            $rooms = Room::with(['roomPrice.building', 'roomPrice.roomType'])
                // แก้ไข: กรองตามอาคาร ต้องใช้ whereHas เพื่อมุดเข้าไปในตาราง roomPrice
                ->when($request->building_id, function ($q) use ($request) {
                    $q->whereHas('roomPrice', function ($subQ) use ($request) {
                        $subQ->where('building_id', $request->building_id);
                    });
                })
                // แก้ไข: กรองตามชั้น ก็ต้องใช้ whereHas เช่นกัน
                ->when($request->floor_num, function ($q) use ($request){
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

        public function insertRoom(Request $request){
            try{
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
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);
            }
        }

        public function updateRoom(Request $request, $id){
            try{
                DB::beginTransaction();
                $request->validate([
                    'room_number' => 'required|string|max:4|unique:rooms,room_number,'.$id,
                    'room_price_id' => 'required|exists:room_prices,id',
                    'status' => 'required',
                ]);
                $data = [
                    'room_number' => $request->room_number,
                    'room_price_id' => $request->room_price_id,
                    'status' => $request->status,
                    'updated_at' => now(),
                ];
                DB::table('rooms')->where('id',$id)->update($data);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.rooms.show')->with('success','ข้อมูลห้องพักถูกอัปเดตแล้ว');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }

        public function deleteRoom($id){
            try{
                DB::beginTransaction();
                DB::table('rooms')->where('id',$id)->delete();
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.rooms.show')->with('success','ลบห้องพักสำเร็จ');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
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
                    'room_id'         => 'required|exists:rooms,id',
                    'id_card'         => 'required|digits:13',
                    'first_name'      => 'required|string|max:255',
                    'last_name'       => 'required|string|max:255',
                    'address_no'      => 'required|string|max:255',
                    'moo'             => 'required|string|max:3',
                    'sub_district'    => 'required|string|max:255',
                    'district'        => 'required|string|max:255',
                    'province'        => 'required|string|max:255',
                    'postal_code'     => 'required|string|max:5',
                    'phone'           => 'required|digits:10',
                    'start_date'      => 'required|date',
                    'resident_count'  => 'required|integer|min:1',
                    // ตรวจสอบนามสกุลไฟล์ให้เป็น pdf, jpg, jpeg, png เท่านั้น
                    'rental_contract' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
                ]);

                // 2. จัดการไฟล์สัญญาเช่าลงโฟลเดอร์ tenants
                $contractPath = null;
                if ($request->hasFile('rental_contract')) {
                    $extension = $request->file('rental_contract')->extension();
                    $filename = time() . '_tenant_.' . $extension;
                    
                    // เก็บลงใน storage/app/public/tenants
                    $contractPath = $request->file('rental_contract')->storeAs('tenants', $filename, 'public');
                }

                // 3. บันทึกข้อมูลผู้เช่าครบทุกฟิลด์ตาม Schema
                Tenant::create([
                    'room_id'         => $request->room_id,
                    'id_card'         => $request->id_card,
                    'password'       => Hash::make($request->id_card),
                    'first_name'      => $request->first_name,
                    'last_name'       => $request->last_name,
                    'address_no'      => $request->address_no,
                    'moo'             => $request->moo,
                    'sub_district'    => $request->sub_district,
                    'district'        => $request->district,
                    'province'        => $request->province,
                    'postal_code'     => $request->postal_code,
                    'phone'           => $request->phone,
                    'start_date'      => $request->start_date,
                    'has_parking'     => $request->has('has_parking'),
                    'resident_count'  => $request->resident_count,
                    'rental_contract' => $contractPath, // เก็บ Path tenants/filename.ext
                    'status'          => 'กำลังใช้งาน',
                ]);

                // 4. อัปเดตสถานะห้องพักเป็น 'มีผู้เช่า'
                DB::table('rooms')->where('id', $request->room_id)->update([
                    'status' => 'มีผู้เช่า',
                    'updated_at' => now()
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'ลงทะเบียนผู้เช่าและบันทึกสัญญาเช่าเรียบร้อยแล้ว');

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
                    'id_card'         => 'required|digits:13',
                    'password'       => 'nullable|string|min:6',
                    'first_name'      => 'required|string|max:255',
                    'last_name'       => 'required|string|max:255',
                    'address_no'      => 'required|string|max:255',
                    'moo'             => 'required|string|max:3',
                    'sub_district'    => 'required|string|max:255',
                    'district'        => 'required|string|max:255',
                    'province'        => 'required|string|max:255',
                    'postal_code'     => 'required|string|max:5',
                    'phone'           => 'required|digits:10',
                    'resident_count'  => 'required|integer|min:1',
                    'rental_contract' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
                ]);

                // 2. เตรียมข้อมูลพื้นฐาน (ยกเว้น room_id ตามที่คุณต้องการ)
                $data = [
                    'id_card'        => $request->id_card,
                    'first_name'     => $request->first_name,
                    'last_name'      => $request->last_name,
                    'address_no'     => $request->address_no,
                    'moo'            => $request->moo,
                    'sub_district'   => $request->sub_district,
                    'district'       => $request->district,
                    'province'       => $request->province,
                    'postal_code'    => $request->postal_code,
                    'phone'          => $request->phone,
                    'resident_count' => $request->resident_count,
                    'has_parking'    => $request->has('has_parking'), // เก็บเป็น boolean
                    'updated_at'     => now(),
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

                DB::commit();
                return redirect()->back()->with('success', 'อัปเดตข้อมูลผู้เช่าครบถ้วนเรียบร้อยแล้ว');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'การอัปเดตล้มเหลว: ' . $e->getMessage()]);
            }
        }
        public function updateStatusTenant($id){
            DB::beginTransaction();
            try {
                $tenant = Tenant::findOrFail($id);

                // ตรวจสอบสถานะป้องกันการยิง Request ซ้ำ
                if ($tenant->status == 'สิ้นสุดสัญญา') {
                    return redirect()->back()->withErrors(['error' => 'สัญญานี้ได้สิ้นสุดลงก่อนหน้านี้แล้ว']);
                }

                // 1. อัปเดตสถานะผู้เช่า และบันทึกวันที่สิ้นสุด
                $tenant->update([
                    'status' => 'สิ้นสุดสัญญา',
                    'end_date' => now(),
                    'updated_at' => now()
                ]);

                // 2. คืนสถานะห้องพักให้ว่าง
                DB::table('rooms')->where('id', $tenant->room_id)->update([
                    'status' => 'ว่าง',
                    'updated_at' => now()
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'สิ้นสุดสัญญาและคืนห้องพักเรียบร้อยแล้ว');

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
            $expenses = TenantExpense::paginate(10);
            return view('admin.tenant_expenses.show', compact('expenses'));
        }

        public function insertTenantExpense(Request $request){
            try{
                DB::beginTransaction();
                $request->validate([
                    'name' => 'required|string|max:50|unique:tenant_expenses,name',
                    'price' => 'required|numeric|min:0',
                ]);
                DB::table('tenant_expenses')->insert([
                    'name' => $request->name,
                    'price' => $request->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.tenant_expenses.show')->with('success', 'เพิ่มรายการค่าใช้จ่ายสำเร็จ');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);
            }
        }

        public function updateTenantExpense(Request $request, $id){
            try{
                DB::beginTransaction();
                $request->validate([
                    'name' => 'required|string|max:50|unique:tenant_expenses,name,'.$id,
                    'price' => 'required|numeric|min:0',
                ]);
                $data = [
                    'name' => $request->name,
                    'price' => $request->price,
                    'updated_at' => now(),
                ];
                DB::table('tenant_expenses')->where('id',$id)->update($data);
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.tenant_expenses.show')->with('success','ข้อมูลรายการค่าใช้จ่ายถูกอัปเดตแล้ว');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }

        public function deleteTenantExpense($id){
            try{
                DB::beginTransaction();
                DB::table('tenant_expenses')->where('id',$id)->delete();
                // บันทึกการเปลี่ยนแปลง
                DB::commit();
                return redirect()->route('admin.tenant_expenses.show')->with('success','ลบรายการค่าใช้จ่ายสำเร็จ');
            }catch(\Exception $e){
                // ยกเลิกการบันทึกข้อมูลถ้ามีข้อผิดพลาด
                DB::rollBack();
                return redirect()->back()->withErrors([ 'error' => $e->getMessage()]);

            }
        }
    // ---------------------------------------------

    // จดมิเตอร์น้ำไฟ Meter Readings

        public function meterReadingsInsertForm(Request $request){

            $billing_month = $request->billing_month ?? date('Y-m');

            // 1. หา ID ของห้องที่จดมิเตอร์ในเดือนนี้ไปแล้ว ทั้งน้ำและไฟ
            $completedRoomIds = MeterReading::where('billing_month', $billing_month)
                ->groupBy('room_id')
                ->havingRaw('COUNT(DISTINCT meter_type) >= 2') // จดครบทั้งน้ำและไฟ
                ->pluck('room_id');

            // 2. ดึงเฉพาะห้องที่มีผู้เช่า และ "ไม่อยู่ในรายชื่อที่จดเสร็จแล้ว"
            $rooms = Room::with(['tenants' => function($q) {
                    $q->where('status', 'กำลังใช้งาน');
                }])
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
                'reading_date'=> 'required|date',
                'data' => 'required|array'
            ]);

            try {
                DB::beginTransaction();

                foreach ($request->data as $roomId => $types) {
                    foreach ($types as $type => $values) {
                        // ข้ามหากไม่ได้กรอกเลขมิเตอร์ปัจจุบัน
                        if (is_null($values['current_value'])) continue;

                        $prev = (float)$values['previous_value'];
                        $current = (float)$values['current_value'];

                        // บันทึกลงตาราง meter_readings โดยตรง
                        MeterReading::create([
                            'room_id'       => $roomId,
                            'tenant_id'     => $values['tenant_id'],
                            'meter_type'    => $type,
                            'previous_value'=> $prev,
                            'current_value' => $current,
                            'units_used'    => $current - $prev,
                            'billing_month' => $request->billing_month,
                            'reading_date'  => $request->reading_date,
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
            $rooms = Room::with(['tenants' => function($q) {
                    $q->where('status', 'กำลังใช้งาน');
                }])
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
            return view('admin.meter_readings.show', compact('rooms', 'billing_month', 'existingReadings','thai_date','recordedDate'));
        }

        // ฟังก์ชันสำหรับ Update ข้อมูล (ใช้ updateOrCreate เพื่อความปลอดภัย)
        public function updateMeterReading(Request $request)
        {
            $request->validate([
                'billing_month'=>'required',
                'reading_date'=>'required|date',
                'data' => 'required|array'
            ]);

            try {
                DB::beginTransaction();
                foreach ($request->data as $roomId => $types) {
                    foreach ($types as $type => $values) {
                        if (is_null($values['current_value'])) continue;

                        $prev = (float)$values['previous_value'];
                        $current = (float)$values['current_value'];

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
            $rooms = Room::with(['tenants' => function($q) use ($endOfMonth) {
                    $q->where('start_date', '<=', $endOfMonth)
                    ->where('status', 'กำลังใช้งาน');
                }, 'roomPrice']) 
                ->where('status', 'มีผู้เช่า')
                ->get();

            $rooms = $rooms->filter(fn($room) => $room->tenants->isNotEmpty());

            $rooms->each(function($room) use ($billing_month) {
                $tenant = $room->tenants->first();

                // 1. ดึงเลขมิเตอร์ยกมา (Previous Values)
                $prevMonth = \Carbon\Carbon::parse($billing_month)->subMonth()->format('Y-m');
                foreach(['water', 'electric'] as $type) {
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
                }

                $room->tenant_id = $tenant->id;
            });

            // แปลงรอบเดือนเรียกเก็บ (ไม่เอา "วัน" จึงใส่ false)
            $thai_billing_month = $this->toThaiDate($billing_month, false);

            return view('admin.invoices.show', compact('rooms', 'billing_month', 'thai_billing_month'));
        }
        // แปลงเป็นวันที่ไทย
        private function toThaiDate($date, $showDay = true)
        {
            if (!$date) return '-';
            
            $carbon = \Carbon\Carbon::parse($date)->locale('th');
            $thaiYear = $carbon->year + 543; // แปลงเป็น พ.ศ.

            if ($showDay) {
                // ผลลัพธ์: 13 มกราคม 2569
                return $carbon->isoFormat('D MMMM') . ' ' . $thaiYear;
            } else {
                // ผลลัพธ์: มกราคม 2569
                return $carbon->isoFormat('MMMM') . ' ' . $thaiYear;
            }
        }
        public function insertInvoiceOne(Request $request)
        {
            // 1. Validation: ตรวจสอบข้อมูลนำเข้าเบื้องต้น
            $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'billing_month' => 'required|string|max:7',
                'issue_date'    => 'required|date'
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
                $room = Room::with(['roomPrice', 'tenants' => function($q) {
                    $q->where('status', 'กำลังใช้งาน');
                }])->findOrFail($request->room_id);

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
                'issue_date'    => 'required|date' 
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
                    if ($result) $count++;
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
            if ($exists) return null;

            // 2. ตรวจสอบความพร้อมของข้อมูลมิเตอร์ (ต้องมีทั้งค่าน้ำและค่าไฟ)
            $meterReadings = MeterReading::where('room_id', $roomId)
                                ->where('billing_month', $billingMonth)
                                ->get();
            
            if ($meterReadings->where('meter_type', 'water')->isEmpty() || 
                $meterReadings->where('meter_type', 'electric')->isEmpty()) {
                return null; // ข้ามห้องที่ยังจดมิเตอร์ไม่ครบ
            }

            // 3. ดึงข้อมูลห้อง ผู้เช่า และราคามาตรฐาน
            $room = Room::with(['roomPrice', 'tenants' => function($q) {
                        $q->where('status', 'กำลังใช้งาน');
                    }])->findOrFail($roomId);

            $tenant = $room->tenants->first();
            if (!$tenant) return null;

            $expenses = TenantExpense::all(); // โหลดรายการค่าใช้จ่ายมาตรฐาน

            // 4. คำนวณข้อมูลบิลเบื้องต้น
            $invoiceNumber = 'INV' . str_replace('-', '', $billingMonth) . '-' . $room->room_number;
            // กำหนดชำระเป็นวันที่ 5 ของเดือนถัดไป
            $dueDate = \Carbon\Carbon::parse($billingMonth)->addMonth()->startOfMonth()->addDays(4);

            // 5. สร้าง Invoice หลัก
            $invoice = Invoice::create([
                'tenant_id'      => $tenant->id,
                'room_id'        => $room->id,
                'invoice_number' => $invoiceNumber,
                'billing_month'  => $billingMonth,
                'issue_date'     => $issueDate, // วันที่เลือกมาจากแอดมิน
                'total_amount'   => 0, // รออัปเดตท้ายสุด
                'status'         => 'กรุณาส่งบิล',
                'due_date'       => $dueDate,
            ]);

            $totalAmount = 0;

            // 6. เพิ่มรายการย่อย (InvoiceDetails) -----------------------------------

            // รายการที่ 1: ค่าเช่าห้อง
            $roomPrice = $room->roomPrice->price ?? 0;
            InvoiceDetail::create([
                'invoice_id'     => $invoice->id,
                'name'           => 'ค่าเช่าห้อง',
                'quantity'       => 1,
                'price_per_unit' => $roomPrice,
                'subtotal'       => $roomPrice
            ]);
            $totalAmount += $roomPrice;

            // รายการที่ 2: ค่าน้ำ/ค่าไฟ (ตามหน่วยที่ใช้จริง)
            foreach ($meterReadings as $reading) {
                $type = $reading->meter_type == 'water' ? 'ค่าน้ำ' : 'ค่าไฟ';
                $expense = $expenses->where('name', $type)->first();
                $rate = $expense->price ?? 0;
                $sub = $reading->units_used * $rate;

                InvoiceDetail::create([
                    'invoice_id'        => $invoice->id,
                    'tenant_expense_id' => $expense->id ?? null,
                    'meter_reading_id'  => $reading->id,
                    'name'              => $expense->name ?? $type,
                    'previous_unit'     => $reading->previous_value,
                    'current_unit'      => $reading->current_value,
                    'quantity'          => $reading->units_used,
                    'price_per_unit'    => $rate,
                    'subtotal'          => $sub
                ]);
                $totalAmount += $sub;
            }

            // รายการที่ 3: ค่าคนมาอาศัยเพิ่ม (ส่วนเกินจาก 2 คน)
            if ($tenant->resident_count > 2) {
                $extraPeople = $tenant->resident_count - 2;
                $extraExpense = $expenses->where('id', 5)->first(); // สมมติ ID 5 คือค่าคนเพิ่ม
                $pricePerPerson = $extraExpense->price ?? 400.00;

                InvoiceDetail::create([
                    'invoice_id'        => $invoice->id,
                    'tenant_expense_id' => $extraExpense->id ?? null,
                    'name'              => ($extraExpense->name ?? 'คนมาอาศัยเพิ่ม') . " (ส่วนเกิน $extraPeople คน)",
                    'quantity'          => $extraPeople,
                    'price_per_unit'    => $pricePerPerson,
                    'subtotal'          => $extraPeople * $pricePerPerson
                ]);
                $totalAmount += ($extraPeople * $pricePerPerson);
            }

            // รายการที่ 4: ค่าที่จอดรถ
            if ($tenant->has_parking) {
                $parking = $expenses->where('id', 3)->first(); // สมมติ ID 3 คือค่าจอดรถ
                if ($parking) {
                    InvoiceDetail::create([
                        'invoice_id'        => $invoice->id,
                        'tenant_expense_id' => $parking->id,
                        'name'              => $parking->name,
                        'quantity'          => 1,
                        'price_per_unit'    => $parking->price,
                        'subtotal'          => $parking->price
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
            // 1. ดึงข้อมูลใบแจ้งหนี้พร้อมรายการย่อย และความสัมพันธ์ที่เกี่ยวข้อง
            $invoice = Invoice::with(['details','tenant','tenant.room','tenant.room.roomPrice','details.meterReading'])->findOrFail($id);

            // 2. จัดการวันที่ภาษาไทย
                // แปลง billing_month (ประจำเดือน) -> มกราคม 2569
                $billingDate = \Carbon\Carbon::parse($invoice->billing_month)->locale('th');
                $invoice->thai_billing_month = $billingDate->isoFormat('MMMM') . ' ' . ($billingDate->year + 543);

                // แปลง issue_date (วันที่ออกบิล) -> 12 มกราคม 2569
                $issueDate = \Carbon\Carbon::parse($invoice->issue_date)->locale('th');
                $invoice->thai_issue_date = $issueDate->isoFormat('D MMMM') . ' ' . ($issueDate->year + 543);

                // แปลง due_date (กำหนดชำระ) -> 05 กุมภาพันธ์ 2569
                $dueDate = \Carbon\Carbon::parse($invoice->due_date)->locale('th');
                $invoice->thai_due_date = $dueDate->isoFormat('D MMMM') . ' ' . ($dueDate->year + 543);

                // หาวันที่จดมิเตอร์จากรายการแรกที่มีข้อมูล
                $firstReading = $invoice->details->whereNotNull('meter_reading_id')->first();
                if ($firstReading && $firstReading->meterReading) {
                    $readDate = \Carbon\Carbon::parse($firstReading->meterReading->reading_date)->locale('th');
                    $invoice->thai_reading_date = $readDate->isoFormat('D MMMM') . ' ' . ($readDate->year + 543);
                } else {
                    $invoice->thai_reading_date = '-';
                }
                // 2. เพิ่มการแปลงยอดรวมเป็นตัวอักษรภาษาไทย
                $invoice->total_amount_thai = $this->bahtText($invoice->total_amount);
            // 3. ดึงข้อมูลบริษัท/อพาร์ทเม้นท์ (สมมติว่ามีแค่ record เดียว)
            $apartment = DB::table('apartment')->first(); 
            
            return view('admin.invoices.invoice_details', compact('invoice', 'apartment'));
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
                        'prev' => (float)$request->water_prev,
                        'current' => (float)$request->water_current,
                    ],
                    'electric' => [
                        'prev' => (float)$request->electric_prev,
                        'current' => (float)$request->electric_current,
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

                return view('admin.invoices.edit_details', compact('invoice', 'apartment', 'expenses'));
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
                        $subtotal = (float)$item['quantity'] * (float)$item['price'];
                        
                        InvoiceDetail::create([
                            'invoice_id'        => $invoice->id,
                            'tenant_expense_id' => $item['expense_id'] ?? null,
                            'meter_reading_id'  => $item['meter_reading_id'] ?? null, // รักษา ID มิเตอร์ไว้
                            'name'              => $item['name'],
                            'previous_unit'     => $item['previous_unit'] ?? null,
                            'current_unit'      => $item['current_unit'] ?? null,
                            'quantity'          => $item['quantity'],
                            'price_per_unit'    => $item['price'],
                            'subtotal'          => $subtotal,
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

            public function sendInvoiceOne(Request $request){
                $request->validate([
                    'invoice_id'=>'required'
                ]);
                try{
                    DB::beginTransaction();
                    Invoice::where('id',$request->invoice_id)
                        ->update([
                            'status'=>'ค้างชำระ'
                        ]);
                    DB::commit();
                    return redirect()->back()->with('success','ส่งบิลสำเร็จ');
                }catch(\Exception $e){
                    DB::rollBack();
                    return redirect()->back()->withErrors(['error'=>'เกิดข้อผิดพลาด'.$e->getMessage()]);
                }
            }

            public function sendInvoiceAll(Request $request){
                $request->validate([
                    'billing_month' => 'required|string|max:7'
                ]);
                try{
                    DB::beginTransaction();
                    // ค้นหา update ใบ invoice เงื่อนไข ใน "billing_month" รอบเดือนนั้นๆ และ มีสถานะ "กรุณาส่งบิล"
                    $affectedRows = Invoice::where('billing_month',$request->billing_month)
                                    ->where('status','กรุณาส่งบิล')
                                    ->update(['status' => 'ค้างชำระ']);
                    // กรณีไม่มีการ update
                    if($affectedRows === 0){
                        return redirect()->back()->withErrors(['error'=>'ไม่พบบิลที่พร้อมส่งในรอบเดือนนี้ หรือบิลถูกส่งไปหมดแล้ว']);
                    }

                    DB::commit();
                    return redirect()->back()->with('success',"ส่งบิลสำเร็จทั้งหมด $affectedRows ห้อง เรียบร้อยแล้ว");
                }catch(\Exception $e){
                    DB::rollBack();
                    return redirect()->back()->withErrors(['error'=>'เกิดข้อผิดพลาด'.$e->getMessage()]);
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
                return redirect()->back()->withErrors(['error'=>'เกิดข้อผิดพลาด'.$e->getMessage()]);
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
                return redirect()->back()->withErrors(['error'=>'เกิดข้อผิดพลาด'.$e->getMessage()]);
            }
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
