<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BookingCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, ?string $reason = null)
    {
        $this->booking = $booking;
        $this->reason = $reason ?? 'Tidak ada keterangan';
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

        return (new MailMessage)
            ->subject('Booking Dibatalkan - SportBooking')
            ->greeting('Halo, ' . $this->booking->nama_pemesan)
            ->line('Booking Anda telah **dibatalkan**.')
            ->line('')
            ->line('**Detail Booking yang Dibatalkan:**')
            ->line('🏟️ Lapangan: **' . $this->booking->lapangan->title . '**')
            ->line('🎯 Kategori: ' . $this->booking->lapangan->category)
            ->line('📅 Tanggal: ' . $tanggal)
            ->line('⏰ Waktu: ' . $jamMulai . ' - ' . $jamSelesai)
            ->line('🔖 Booking ID: `' . $this->booking->booking_code . '`')
            ->line('')
            ->line('**Alasan Pembatalan:**')
            ->line($this->reason)
            ->line('')
            ->line('Jika Anda tidak merasa melakukan pembatalan ini, silakan hubungi kami segera.')
            ->action('Booking Lagi', url('/'))
            ->line('')
            ->line('Terima kasih atas pengertiannya.')
            ->salutation('Salam, Tim SportBooking');
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $tanggal = Carbon::parse($this->booking->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        $jamMulai = Carbon::parse($this->booking->jam_mulai)->format('H:i');
        $jamSelesai = Carbon::parse($this->booking->jam_selesai)->format('H:i');

        $message = "❌ *BOOKING DIBATALKAN*\n\n";
        $message .= "Halo *{$this->booking->nama_pemesan}*,\n\n";
        $message .= "Booking Anda telah dibatalkan.\n\n";
        $message .= "*📋 DETAIL BOOKING:*\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🏟️ Lapangan: *{$this->booking->lapangan->title}*\n";
        $message .= "📅 Tanggal: {$tanggal}\n";
        $message .= "⏰ Waktu: {$jamMulai} - {$jamSelesai}\n";
        $message .= "🆔 Booking ID: *{$this->booking->booking_code}*\n";
        $message .= "━━━━━━━━━━━━━━━━\n\n";
        $message .= "📝 *Alasan:* {$this->reason}\n\n";
        $message .= "Jika ada pertanyaan, silakan hubungi kami.\n\n";
        $message .= "Terima kasih,\n";
        $message .= "*SportBooking* 🙏";

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
            'reason' => $this->reason,
        ];
    }
}
