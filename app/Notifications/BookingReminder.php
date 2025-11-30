<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BookingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tanggal = Carbon::parse($this->booking->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        $jamMulai = Carbon::parse($this->booking->jam_mulai)->format('H:i');
        $jamSelesai = Carbon::parse($this->booking->jam_selesai)->format('H:i');
        
        // Calculate hours until booking
        $bookingDateTime = Carbon::parse($this->booking->tanggal . ' ' . $this->booking->jam_mulai);
        $hoursUntil = now()->diffInHours($bookingDateTime);

        return (new MailMessage)
            ->subject('Pengingat Booking - SportBooking')
            ->greeting('Halo, ' . $this->booking->nama_pemesan . '!')
            ->line("Ini adalah pengingat bahwa booking Anda akan dimulai dalam **{$hoursUntil} jam**.")
            ->line('')
            ->line('**Detail Booking:**')
            ->line('🏟️ Lapangan: **' . $this->booking->lapangan->title . '**')
            ->line('🎯 Kategori: ' . $this->booking->lapangan->category)
            ->line('📅 Tanggal: ' . $tanggal)
            ->line('⏰ Waktu: ' . $jamMulai . ' - ' . $jamSelesai)
            ->line('📍 Lokasi: SportBooking Arena')
            ->line('🔖 Booking ID: `' . $this->booking->booking_code . '`')
            ->line('')
            ->line('**Persiapan Sebelum Main:**')
            ->line('✅ Datang 15 menit sebelum waktu')
            ->line('✅ Bawa kartu identitas')
            ->line('✅ Gunakan pakaian & sepatu olahraga')
            ->line('✅ Bawa air minum')
            ->line('')
            ->action('Lihat Detail', route('detail', $this->booking->lapangan_id))
            ->line('')
            ->line('Sampai jumpa di lapangan!')
            ->salutation('Salam Olahraga, Tim SportBooking');
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $tanggal = Carbon::parse($this->booking->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        $jamMulai = Carbon::parse($this->booking->jam_mulai)->format('H:i');
        $jamSelesai = Carbon::parse($this->booking->jam_selesai)->format('H:i');
        
        $bookingDateTime = Carbon::parse($this->booking->tanggal . ' ' . $this->booking->jam_mulai);
        $hoursUntil = now()->diffInHours($bookingDateTime);

        $message = "⏰ *PENGINGAT BOOKING*\n\n";
        $message .= "Halo *{$this->booking->nama_pemesan}*!\n\n";
        $message .= "Booking Anda akan dimulai dalam *{$hoursUntil} jam* lagi! 🏃\n\n";
        $message .= "*📋 DETAIL BOOKING:*\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🏟️ Lapangan: *{$this->booking->lapangan->title}*\n";
        $message .= "🎯 Kategori: {$this->booking->lapangan->category}\n";
        $message .= "📅 Tanggal: {$tanggal}\n";
        $message .= "⏰ Waktu: {$jamMulai} - {$jamSelesai}\n";
        $message .= "🆔 Booking ID: *{$this->booking->booking_code}*\n";
        $message .= "━━━━━━━━━━━━━━━━\n\n";
        $message .= "✅ *CHECKLIST:*\n";
        $message .= "• Datang 15 menit lebih awal\n";
        $message .= "• Bawa kartu identitas\n";
        $message .= "• Pakai sepatu & pakaian olahraga\n";
        $message .= "• Jangan lupa air minum!\n\n";
        $message .= "Sampai jumpa di lapangan! 🎉\n\n";
        $message .= "*SportBooking* - Main Makin Seru! ⚽🏀🏐";

        return [
            'message' => $message,
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'lapangan' => $this->booking->lapangan->title,
            'tanggal' => $this->booking->tanggal,
            'jam_mulai' => $this->booking->jam_mulai,
        ];
    }
}
