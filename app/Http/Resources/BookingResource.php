<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lapangan' => new LapanganResource($this->whenLoaded('lapangan')),
            'user' => new UserResource($this->whenLoaded('user')),
            'tanggal' => $this->tanggal,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'duration' => $this->duration,
            'nama_pemesan' => $this->nama_pemesan,
            'nomor_telepon' => $this->nomor_telepon,
            'email' => $this->email,
            'harga' => (float) $this->harga,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_status_label' => match($this->payment_status) {
                'unpaid' => 'Belum Dibayar',
                'waiting_confirmation' => 'Menunggu Konfirmasi',
                'paid' => 'Sudah Dibayar',
                'refunded' => 'Dikembalikan',
                default => 'Unknown'
            },
            'payment_proof' => $this->payment_proof ? asset('storage/' . $this->payment_proof) : null,
            'status' => $this->status,
            'status_label' => match($this->status) {
                'pending' => 'Pending',
                'confirmed' => 'Dikonfirmasi',
                'cancelled' => 'Dibatalkan',
                'completed' => 'Selesai',
                default => 'Unknown'
            },
            'cancellation_reason' => $this->cancellation_reason,
            'cancellation_type' => $this->cancellation_type,
            'refund_amount' => $this->refund_amount ? (float) $this->refund_amount : null,
            'refund_processed_at' => $this->refund_processed_at?->toISOString(),
            'points_earned' => $this->points_earned,
            'points_redeemed' => $this->points_redeemed,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
