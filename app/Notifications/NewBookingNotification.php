<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 Booking Baru - ' . $this->booking->booking_code)
            ->greeting('Halo Admin!')
            ->line('Ada booking baru yang perlu Anda ketahui:')
            ->line('**Kode Booking:** ' . $this->booking->booking_code)
            ->line('**Nama Pemesan:** ' . $this->booking->nama_pemesan)
            ->line('**Lapangan:** ' . $this->booking->lapangan->nama)
            ->line('**Tanggal:** ' . \Carbon\Carbon::parse($this->booking->tanggal)->format('d F Y'))
            ->line('**Jam:** ' . $this->booking->jam_mulai . ' - ' . $this->booking->jam_selesai)
            ->line('**Total Harga:** Rp ' . number_format($this->booking->harga, 0, ',', '.'))
            ->line('**Status Pembayaran:** ' . ucfirst($this->booking->payment_status))
            ->action('Lihat Detail Booking', url('/admin/bookings/' . $this->booking->id))
            ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking Baru',
            'message' => "Booking baru dari {$this->booking->nama_pemesan} untuk {$this->booking->lapangan->nama}",
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->nama_pemesan,
            'lapangan_name' => $this->booking->lapangan->nama,
            'date' => $this->booking->tanggal,
            'time' => $this->booking->jam_mulai . ' - ' . $this->booking->jam_selesai,
            'total' => $this->booking->harga,
            'payment_status' => $this->booking->payment_status,
            'action_url' => '/admin/bookings/' . $this->booking->id,
            'icon' => 'heroicon-o-calendar',
            'icon_color' => 'success',
        ];
    }
}
