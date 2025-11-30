<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BookingConfirmed extends Notification implements ShouldQueue
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
     *
     * @return array<int, string>
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
            ->subject('Booking Dikonfirmasi - SportBooking')
            ->greeting('Halo, ' . $this->booking->nama_pemesan . '!')
            ->line('Terima kasih telah melakukan booking di SportBooking.')
            ->line('Booking Anda telah **dikonfirmasi**. Berikut detail booking Anda:')
            ->line('')
            ->line('**Detail Booking:**')
            ->line('🏟️ Lapangan: **' . $this->booking->lapangan->title . '**')
            ->line('🎯 Kategori: ' . $this->booking->lapangan->category)
            ->line('📅 Tanggal: ' . $tanggal)
            ->line('⏰ Waktu: ' . $jamMulai . ' - ' . $jamSelesai)
            ->line('💰 Total: **Rp ' . number_format($this->booking->lapangan->price, 0, ',', '.') . '**')
            ->line('')
            ->line('**Booking ID:** `' . $this->booking->booking_code . '`')
            ->line('')
            ->action('Lihat Detail Booking', route('detail', $this->booking->lapangan_id))
            ->line('')
            ->line('**Catatan Penting:**')
            ->line('• Harap datang 15 menit sebelum waktu booking')
            ->line('• Bawa kartu identitas untuk verifikasi')
            ->line('• Hubungi kami jika ada perubahan jadwal')
            ->line('')
            ->line('Terima kasih telah memilih SportBooking!')
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

        $message = "🎉 *BOOKING DIKONFIRMASI*\n\n";
        $message .= "Halo *{$this->booking->nama_pemesan}*!\n\n";
        $message .= "Booking Anda telah berhasil dikonfirmasi.\n\n";
        $message .= "*📋 DETAIL BOOKING:*\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "🏟️ Lapangan: *{$this->booking->lapangan->title}*\n";
        $message .= "🎯 Kategori: {$this->booking->lapangan->category}\n";
        $message .= "📅 Tanggal: {$tanggal}\n";
        $message .= "⏰ Waktu: {$jamMulai} - {$jamSelesai}\n";
        $message .= "💰 Total: *Rp " . number_format($this->booking->lapangan->price, 0, ',', '.') . "*\n";
        $message .= "🆔 Booking ID: *{$this->booking->booking_code}*\n";
        $message .= "━━━━━━━━━━━━━━━━\n\n";
        $message .= "⚠️ *CATATAN PENTING:*\n";
        $message .= "• Datang 15 menit sebelum waktu\n";
        $message .= "• Bawa kartu identitas\n";
        $message .= "• Simpan nomor booking ini\n\n";
        $message .= "Terima kasih telah memilih *SportBooking*! 🙏\n\n";
        $message .= "_Pesan otomatis, tidak perlu dibalas_";

        return [
            'message' => $message,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'lapangan' => $this->booking->lapangan->title,
            'tanggal' => $this->booking->tanggal,
            'jam_mulai' => $this->booking->jam_mulai,
            'jam_selesai' => $this->booking->jam_selesai,
        ];
    }
}
