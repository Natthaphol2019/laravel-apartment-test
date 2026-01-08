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
}
