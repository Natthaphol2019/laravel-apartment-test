<table class="table table-bordered align-middle table-hover">
    <thead class="{{ $type === 'water' ? 'table-info' : 'table-danger' }} text-center">
        <tr>
            <th width="100">ห้อง</th>
            <th>เลขมิเตอร์เดือนก่อน</th>
            <th>เลขมิเตอร์ {{ $thai_date }} (แก้ไข)</th>
            <th>หน่วยที่ใช้ใหม่</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rooms as $room)
            @php
                $tenant = $room->tenants->first();
                $prev_val = $room->{"prev_{$type}"};
                $currentReading = $existingReadings->where('room_id', $room->id)->where('meter_type', $type)->first();
            @endphp
            @if($currentReading)
            <tr>
                <td class="text-center fw-bold fs-5 bg-light">{{ $room->room_number }}</td>
                <td class="bg-light text-center fw-bold">
                    {{ $currentReading->previous_value }}
                    <input type="hidden" name="data[{{ $room->id }}][{{ $type }}][previous_value]" class="prev-input" value="{{ $currentReading->previous_value }}">
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                        <input type="number" name="data[{{ $room->id }}][{{ $type }}][current_value]" 
                               class="form-control form-control-lg current-input fw-bold border-warning" 
                               value="{{ $currentReading->current_value }}" 
                               oninput="calculateFromRow(this)" required
                               min="0">
                        <input type="hidden" name="data[{{ $room->id }}][{{ $type }}][tenant_id]" value="{{ $tenant->id }}">
                    </div>
                </td>
                <td class="text-center fw-bold fs-5 text-primary">
                    <span class="units-used">{{ $currentReading->units_used }}</span>
                </td>
            </tr>
            @endif
        @empty
            <tr><td colspan="4" class="text-center p-4 text-muted">ยังไม่มีข้อมูลที่จดบันทึกในเดือนนี้ <span class="text-danger">กรุณากรอกมิเตอร์ในรอบเดือน {{ $thai_date }}</span> </td></tr>
        @endforelse
    </tbody>
</table>