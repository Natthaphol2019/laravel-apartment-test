<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;   
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TenantController;

// Tenant ฝั่งผู้เช่า
Route::get('/', function () {
    return view('auth.tenantLogin');
})->name('tenant.loginForm');
Route::post('/tenant/login', [TenantLoginController::class, 'login'])->name('tenant.login');

Route::prefix('tenant')->middleware('auth:tenant')->group(function () {
    Route::post('/logout', [TenantLoginController::class, 'tenantLogout'])->name('tenant.logout');
    Route::get('/index', [TenantController::class, 'tenantIndex'])->name('tenant.index');
});
// Admin ฝั่งผู้ดูแลระบบ
Route::get('/admin/login', [AdminLoginController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login');

Route::get('/admin/register', [AdminLoginController::class, 'registerForm'])->name('admin.registerForm');
Route::post('/admin/register', [AdminLoginController::class, 'register'])->name('admin.register');

Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::post('/logout', [AdminLoginController::class, 'adminLogout'])->name('admin.logout');
    Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
    // ตั้งค่าอพาร์ทเม้นท์ Apartment
        Route::get('/apartment', [AdminController::class, 'apartmentShow'])->name('admin.apartment.show');
        Route::get('/apartment/edit/{id}',[AdminController::class, 'editApartment'])->name('admin.apartment.edit');
        Route::put('/apartment/update/{id}',[AdminController::class , 'updateApartment'])->name('admin.apartment.update');
    // จัดการประเภทตึก Building ตึก 2 4 5 ชั้น
        Route::get('/building',[AdminController::class,'buildingShow'])->name('admin.building.show');
        Route::put('/building/update/{id}',[AdminController::class , 'updateBuilding'])->name('admin.building.update');
    // จัดการประเภทห้อง Room Type
        Route::get('/room_types',[AdminController::class, 'roomTypeShow'])->name('admin.room_types.show');
        Route::post('room_types/insert', [AdminController::class, 'insertRoomType'])->name('admin.room_types.insert');
        Route::put('/room_types/update/{id}', [AdminController::class, 'updateRoomType'])->name('admin.room_types.update');
        Route::post('/room_types/delete/{id}', [AdminController::class, 'deleteRoomType'])->name('admin.room_types.delete');
    // จัดการราคาห้อง Room Price
        Route::get('/room_prices', [AdminController::class, 'roomPriceShow'])->name('admin.room_prices.show');
        Route::post('/room_prices/insert', [AdminController::class, 'insertRoomPrice'])->name('admin.room_prices.insert');
        Route::put('/room_prices/update/{id}',[AdminController::class,'updateRoomPrice'])->name('admin.room_prices.update');
        Route::post('/room_prices/delete/{id}', [AdminController::class, 'deleteRoomPrice'])->name('admin.room_prices.delete');
    // จัดการห้อง Rooms
        Route::get('/rooms', [AdminController::class, 'roomShow'])->name('admin.rooms.show');
        Route::post('/rooms/insert', [AdminController::class, 'insertRoom'])->name('admin.rooms.insert');
        Route::put('/rooms/update/{id}', [AdminController::class, 'updateRoom'])->name('admin.rooms.update');
        Route::post('/rooms/delete/{id}', [AdminController::class, 'deleteRoom'])->name('admin.rooms.delete');
    // จัดการผู้เช่า Tenant
        Route::get('/tenants', [AdminController::class, 'tenantShow'])->name('admin.tenants.show');
        Route::post('/tenants/insert', [AdminController::class, 'insertTenant'])->name('admin.tenants.insert');
        Route::put('/tenants/update/{id}', [AdminController::class, 'updateTenant'])->name('admin.tenants.update');
        Route::put('/tenants/updateStatus/{id}', [AdminController::class, 'updateStatusTenant'])->name('admin.tenants.updateStatusTenant');
        Route::post('/tenants/delete/{id}', [AdminController::class, 'deleteTenant'])->name('admin.tenants.delete');
    // ตั้งค่าเก็บค่าใช้จ่ายกับผู้เช่า Tenant Expenses
        Route::get('/tenant_expenses', [AdminController::class, 'tenantExpensesShow'])->name('admin.tenant_expenses.show');
        Route::post('/tenant_expenses/insert', [AdminController::class, 'insertTenantExpense'])->name('admin.tenant_expenses.insert');
        Route::put('/tenant_expenses/update/{id}', [AdminController::class, 'updateTenantExpense'])->name('admin.tenant_expenses.update');
        Route::post('/tenant_expenses/delete/{id}', [AdminController::class, 'deleteTenantExpense'])->name('admin.tenant_expenses.delete');
    // จดมิเตอร์น้ำไฟ Meter Readings
        Route::get('/meter_readings', [AdminController::class, 'readMeterReading'])->name('admin.meter_readings.show');
        Route::get('/meter_readings/insert', [AdminController::class, 'meterReadingsInsertForm'])->name('admin.meter_readings.insertForm');
        Route::post('/meter_readings/insert', [AdminController::class, 'insertMeterReading'])->name('admin.meter_readings.insert');
        Route::put('/meter_readings/update', [AdminController::class,'updateMeterReading'])->name('admin.meter_readings.update');
    // จัดการ บิลค่าเช่า Invoices
        Route::get('/invoices',[AdminController::class, 'invoiceShow'])->name('admin.invoices.show');
        Route::post('invoices/insertOne', [AdminController::class , 'insertInvoiceOne'])->name('admin.invoice.insertInvoiceOne');
        Route::post('invoices/insertAll', [AdminController::class , 'insertInvoicesAll'])->name('admin.invoices.insertInvoicesAll');
        Route::post('invoices/insertMeterReadingOne', [AdminController::class , 'insertInvoiceMeterReadingOne'])->name('admin.invoice.insertInvoiceMeterReadingOne');
        Route::get('invoices/details/{id}', [AdminController::class , 'readInvoiceDetails'])->name('admin.invoices.details');
        Route::get('invoices/edit/details/{id}', [AdminController::class , 'editInvoiceDetails'])->name('admin.invoices.editDetails');
        Route::put('invoices/update/details/{id}', [AdminController::class , 'updateInvoiceDetails'])->name('admin.invoices.updateDetails');
    // จัดการผู้ดูแลระบบ Admin
        Route::get('/users_manage', [AdminController::class, 'usersManageShow'])->name('admin.users_manage.show');
        Route::post('/users_manage/insert', [AdminController::class, 'insertUserManage'])->name('admin.users_manage.insert');
        Route::put('/users_manage/update/{id}', [AdminController::class, 'updateUserManage'])->name('admin.users_manage.update');
        Route::post('/users_manage/delete/{id}', [AdminController::class, 'deleteUserManage'])->name('admin.users_manage.delete');
    
});