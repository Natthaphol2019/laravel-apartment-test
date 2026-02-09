<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\TenantExpense;
use Carbon\Carbon;
class CalculateLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-late-fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'คำนวณค่าปรับรายวันสำหรับใบแจ้งหนี้ที่ค้างชำระ';

    /**
     * Execute the console command.
     */
    public function handle(){
        try {
            // 1. ดึงราคาค่าปรับจากตารางค่าใช้จ่าย (ID 4)
            $lateFeeExpense = TenantExpense::find(4);
            $nameFeeExpense = $lateFeeExpense->name;
            $pricePerDay = $lateFeeExpense ? $lateFeeExpense->price : 50.00;

            // 2. ดึงใบแจ้งหนี้ที่ "ค้างชำระ" และเลยกำหนดส่ง
            $invoices = Invoice::where('status', 'ค้างชำระ')
                ->where('due_date', '<', Carbon::now()->startOfDay())
                ->get();

            if ($invoices->isEmpty()) {
                $this->info("ไม่พบใบแจ้งหนี้ที่ค้างชำระ");
                return;
            }

            foreach ($invoices as $invoice) {
                try {
                    $today = Carbon::now()->startOfDay();
                    $due = Carbon::parse($invoice->due_date)->startOfDay();

                    // คำนวณจำนวนวันที่ค้างชำระ (ค่าบวกเสมอ)
                    $daysLate = $today->gt($due) ? $today->diffInDays($due, true) : 0;
                    $daysToCalculate = min($daysLate, 15);
                    $totalPenalty = $daysToCalculate * $pricePerDay;

                    if ($totalPenalty > 0) {
                        DB::transaction(function () use ($invoice, $totalPenalty, $daysToCalculate, $pricePerDay, $nameFeeExpense) {

                            // 3. บันทึก/อัปเดต รายการค่าปรับลงตารางรายละเอียด
                            InvoiceDetail::updateOrCreate(
                                [
                                    'invoice_id' => $invoice->id,
                                    'tenant_expense_id' => 4
                                ],
                                [
                                    'name' => $nameFeeExpense,
                                    'quantity' => $daysToCalculate,
                                    'price_per_unit' => $pricePerDay,
                                    'subtotal' => $totalPenalty,
                                    'updated_at' => now()
                                ]
                            );

                            // 4. อัปเดตยอดรวมในใบแจ้งหนี้หลัก
                            $newTotal = InvoiceDetail::where('invoice_id', $invoice->id)->sum('subtotal');
                            $invoice->update(['total_amount' => $newTotal]);
                        });

                        $this->info("บันทึกค่าปรับ Invoice #{$invoice->invoice_number} สำเร็จ");
                    }
                } catch (\Exception $e) {
                    // แสดง Error บนหน้าจอ Terminal แทนการลงไฟล์ Log
                    $this->error("❌เกิดข้อผิดพลาดที่ห้อง {$invoice->id}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info('ดำเนินการเสร็จสิ้น');

        } catch (\Exception $e) {
            $this->error("ระบบหลักขัดข้อง: " . $e->getMessage());
        }
    }
}
