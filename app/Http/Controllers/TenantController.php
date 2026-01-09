<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    //
    public function tenantIndex()
    {
        $tenant = Auth::guard('tenant')->user();
        $tenant->load(['room.roomPrice.roomType']); //ดึงข้อมูลห้องราคาและประเภท
        
        $apartment = \DB::table('apartment')->first(); //ดึงข้อมูลอพาร์ทเม้นท์
        return view('tenant.index', compact('tenant','apartment'));
    }
}
