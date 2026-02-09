<table class="table table-bordered align-middle table-hover">
    <thead class="{{ $type === 'water' ? 'table-info' : 'table-danger' }} text-center">
        <tr>
            <th width="100">ห้อง</th>
            <th width="80">สถานะ</th>
            <th>เลขมิเตอร์เดือนก่อน (ยกมา)</th>
            <th>เลขมิเตอร์เดือนปัจจุบัน ({{ $thai_date }})</th>
            <th>หน่วยที่ใช้</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rooms as $room)
            @php
                $tenant = $room->tenants->first();
                foreach(['water', 'electric'] as $m_type) {
                    if($type == $m_type) {
                        $prev_val = $room->{"prev_{$m_type}"};
                    }
                }
                $currentReading = $existingReadings->where('room_id', $room->id)->where('meter_type', $type)->first();
            @endphp
            @if(!$currentReading)
                <tr>
                    <td class="text-center fw-bold fs-5">{{ $room->room_number }}</td>
                    <td class="text-center">
                        <span class="badge bg-success rounded-circle p-2">
                            <i class="bi bi-person-fill"></i>
                        </span>
                    </td>
                    {{-- ส่วนเลขมิเตอร์เดือนก่อนหน้า --}}
                    <td class="bg-light">
                        <div class="input-group">
                            <input type="number" 
                                name="data[{{ $room->id }}][{{ $type }}][previous_value]" 
                                class="form-control text-center fw-bold prev-input {{ is_null($prev_val) ? 'border-warning' : 'border-0 bg-transparent' }}" 
                                value="{{ $currentReading->previous_value ?? $prev_val ?? 0 }}" 
                                {{ !is_null($prev_val) ? 'readonly' : '' }}
                                placeholder="กรอกค่าเริ่มต้น"
                                oninput="calculateFromRow(this)"
                                min="0">
                            @if(is_null($prev_val))
                                <span class="input-group-text bg-warning text-dark small"><i class="bi bi-pencil-square"></i></span>
                            @endif
                        </div>
                        @if(is_null($prev_val))
                            <div class="text-danger mt-1" style="font-size: 0.7rem;">* ไม่พบข้อมูลเดือนก่อน กรุณาระบุ</div>
                        @endif
                    </td>
                    {{-- ส่วนเลขมิเตอร์ปัจจุบัน --}}
                    <td>
                        <div class="input-group">
                            <span class="input-group-text text-{{ $type == 'water' ? 'info' : 'danger' }}">
                                <i class="bi bi-{{ $type == 'water' ? 'droplet' : 'lightning' }}-fill"></i>
                            </span>
                            <input type="number" name="data[{{ $room->id }}][{{ $type }}][current_value]" 
                                class="form-control form-control-lg current-input fw-bold" 
                                value="{{ $currentReading->current_value ?? '' }}" 
                                required
                                oninput="calculateFromRow(this)">
                            
                            <input type="hidden" name="data[{{ $room->id }}][{{ $type }}][tenant_id]" value="{{ $tenant->id }}">
                            <input type="hidden" name="data[{{ $room->id }}][{{ $type }}][meter_type]" value="{{ $type }}">
                            <input type="hidden" name="data[{{ $room->id }}][{{ $type }}][reading_date]" value="{{ date('Y-m-d') }}">
                        </div>
                    </td>
                    <td class="text-center fw-bold fs-5 text-primary">
                        <span class="units-used">{{ $currentReading->units_used ?? 0 }}</span>
                    </td>
                </tr>
            @endif
            @empty
                <tr><td colspan="5" class="text-center p-4 text-muted">ข้อมูลมิเตอร์ในเดือน <span class="text-danger">{{ $thai_date }} ถูกจดแล้ว </span>สามารถอ่านได้ที่หน้าข้อมูลมิเตอร์ที่บันทึกแล้ว</td></tr>
        @endforelse
    </tbody>
</table>