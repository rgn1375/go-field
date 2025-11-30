# Past Time Validation - Waktu Yang Sudah Lewat Tidak Bisa Dibooking

## ✅ Implementasi

Sistem sekarang **memblokir booking** untuk waktu yang sudah lewat, bahkan hanya 1 milidetik!

## 🕐 Cara Kerja

### 1. **Real-Time Check di generateTimeSlots()**
```php
$now = Carbon::now();
$selectedDate = Carbon::parse($this->selectedDate);
$isToday = $selectedDate->isToday();

if ($isToday) {
    $slotStartTime = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $jamMulai);
    $isPast = $slotStartTime->lte($now); // <= sekarang = LEWAT
}

// Mark as booked if past
'is_booked' => $isBooked || $isPast,
'is_past' => $isPast,
```

### 2. **Double Validation di submitBooking()**
```php
$selectedDateTime = Carbon::parse($this->selectedDate . ' ' . $timeSlot['jam_mulai']);

if ($selectedDateTime->lte(Carbon::now())) {
    session()->flash('error', 'Waktu booking sudah lewat. Silakan pilih waktu yang lain.');
    return redirect()->route('detail', $this->lapangan->id);
}
```

### 3. **UI Indication**
Slot yang sudah lewat ditampilkan dengan:
- ❌ Background abu-abu
- 🔒 Icon lock
- 📝 Label "Sudah Lewat"
- 🚫 Cursor not-allowed

## 🎯 Skenario

### Waktu Sekarang: 13:00:00

| Waktu Slot | Status | Alasan |
|------------|--------|--------|
| 12:00-13:00 | ❌ TIDAK BISA | Sudah selesai |
| 13:00-14:00 | ❌ TIDAK BISA | Tepat sekarang (lte) |
| 13:00:01 | ✅ BISA | 1 detik dari sekarang |
| 14:00-15:00 | ✅ BISA | 1 jam dari sekarang |
| Besok 10:00 | ✅ BISA | Hari lain |

### Edge Cases

**1 Milidetik Yang Lalu**
```
Sekarang: 13:03:21.605244
Booking:  13:03:21.605243 (1ms ago)
Result:   ❌ TIDAK BISA ✅ BENAR!
```

**Tepat Sekarang**
```
Sekarang: 13:00:00.000
Booking:  13:00:00.000
Result:   ❌ TIDAK BISA (lte = true)
```

**1 Milidetik Dari Sekarang**
```
Sekarang: 13:00:00.000
Booking:  13:00:00.001
Result:   ✅ BISA (gt = true)
```

## 📊 Flow Diagram

```
User pilih tanggal & waktu
         ↓
    Hari ini?
    ↙        ↘
  YES         NO
   ↓           ↓
[Check Time] [Allow]
   ↓
Slot time <= NOW?
    ↙        ↘
  YES         NO
   ↓           ↓
[Block]     [Allow]
   ↓
Show "Sudah Lewat"
```

## 🧪 Testing

Run test script:
```bash
php test-past-time-validation.php
```

Expected results:
- ✅ 1 jam lalu: LEWAT
- ✅ 1 menit lalu: LEWAT
- ✅ 1 milidetik lalu: LEWAT
- ✅ Sekarang persis: LEWAT
- ✅ 1 milidetik dari sekarang: BISA
- ✅ 1 jam dari sekarang: BISA

## ⚡ Performance

- **Check Time**: O(1) - Instant comparison
- **No Database Query**: Pure logic comparison
- **Only for Today**: Future dates skip check

## 🎨 UI Updates

### Before:
```html
<div class="...">Sudah Dipesan</div>
```

### After:
```html
<div class="... opacity-60">
    @if($slot['is_past'])
        Sudah Lewat
    @else
        Sudah Dipesan
    @endif
</div>
```

## 📝 Code Locations

- **Livewire**: `app/Livewire/BookingForm.php`
  - Line ~200: `generateTimeSlots()` with isPast check
  - Line ~250: `submit()` with past validation
  - Line ~330: `submitBooking()` with double-check
- **View**: `resources/views/livewire/booking-form-new.blade.php`
  - Line ~90: Display "Sudah Lewat" label

## ✅ Result

**100% Protected:**
- ✅ Waktu yang sudah lewat TIDAK BISA dibooking
- ✅ Bahkan 1 milidetik yang lewat DITOLAK
- ✅ Real-time validation (bukan cache)
- ✅ Double validation (UI + Submit)
- ✅ User-friendly error message
- ✅ Visual indication (grayed out)

**Tidak ada yang bisa booking waktu yang sudah lewat!** ⏰🚫
