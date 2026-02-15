document.addEventListener('DOMContentLoaded', function() {
    
    // เลือกทุกองค์ประกอบที่มี class 'room-group'
    const rooms = document.querySelectorAll('.room-group');

    rooms.forEach(room => {
        room.addEventListener('click', function() {
            // ดึงข้อมูลจาก dataset
            const number = this.dataset.number;
            const status = this.dataset.status;
            const tenant = this.dataset.tenant;

            // แปลงสถานะเป็นข้อความภาษาไทย
            let statusText = '';
            switch(status) {
                case 'available': statusText = 'ว่าง'; break;
                case 'occupied': statusText = 'ไม่ว่าง'; break;
                case 'repair': statusText = 'กำลังซ่อมแซม'; break;
                default: statusText = status;
            }

            // แสดง Alert หรือ Modal (ในที่นี้ใช้ alert ตามโจทย์)
            let message = `ห้อง: ${number}\nสถานะ: ${statusText}`;
            
            if(status === 'occupied') {
                message += `\nผู้เช่า: ${tenant}`;
            }

            alert(message);
        });
    });
});