<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestNotification extends Notification implements ShouldQueue
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
            ->subject('⚠️ Permintaan Pembatalan & Refund - ' . $this->booking->booking_code)
            ->greeting('Halo Admin!')
            ->line('Ada permintaan pembatalan dengan refund yang memerlukan persetujuan Anda:')
            ->line('**Kode Booking:** ' . $this->booking->booking_code)
            ->line('**Nama Pemesan:** ' . $this->booking->nama_pemesan)
            ->line('**Lapangan:** ' . $this->booking->lapangan->nama)
            ->line('**Tanggal:** ' . \Carbon\Carbon::parse($this->booking->tanggal)->format('d F Y'))
            ->line('**Jam:** ' . $this->booking->jam_mulai . ' - ' . $this->booking->jam_selesai)
            ->line('**Total Harga:** Rp ' . number_format($this->booking->harga, 0, ',', '.'))
            ->line('**Jumlah Refund:** Rp ' . number_format($this->booking->refund_amount, 0, ',', '.') . ' (' . $this->booking->refund_percentage . '%)')
            ->line('**Alasan Pembatalan:** ' . ($this->booking->cancellation_reason ?? 'Tidak disebutkan'))
            ->action('Review & Approve', url('/admin/bookings/' . $this->booking->id))
            ->line('Silakan review dan approve/reject permintaan pembatalan ini.')
            ->line('⚠️ **Status saat ini: Menunggu Persetujuan Admin**');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Pembatalan',
            'message' => "Permintaan pembatalan dari {$this->booking->nama_pemesan} dengan refund {$this->booking->refund_percentage}% - {$this->booking->booking_code}",
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'customer_name' => $this->booking->nama_pemesan,
            'lapangan_name' => $this->booking->lapangan->nama,
            'date' => $this->booking->tanggal,
            'time' => $this->booking->jam_mulai . ' - ' . $this->booking->jam_selesai,
            'refund_amount' => $this->booking->refund_amount,
            'refund_percentage' => $this->booking->refund_percentage,
            'cancellation_reason' => $this->booking->cancellation_reason,
            'action_url' => '/admin/bookings/' . $this->booking->id,
            'icon' => 'heroicon-o-exclamation-triangle',
            'icon_color' => 'warning',
        ];
    }
}
