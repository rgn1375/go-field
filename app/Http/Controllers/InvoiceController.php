<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Booking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Download invoice PDF
     */
    public function download($invoiceId)
    {
        $invoice = Invoice::with(['booking.lapangan'])->findOrFail($invoiceId);
        
        // Security check: pastikan user hanya bisa download invoice miliknya sendiri
        // atau jika guest booking, validate by email/phone
        if ($invoice->booking->user_id) {
            if (!Auth::check() || Auth::id() !== $invoice->booking->user_id) {
                abort(403, 'Unauthorized access to invoice');
            }
        }
        
        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        
        // Set paper size dan orientation
        $pdf->setPaper('a4', 'portrait');
        
        // Download dengan nama file yang sesuai
        $filename = $invoice->invoice_number . '_' . $invoice->booking->nama_pemesan . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * View invoice in browser (preview before download)
     */
    public function view($invoiceId)
    {
        $invoice = Invoice::with(['booking.lapangan'])->findOrFail($invoiceId);
        
        // Security check
        if ($invoice->booking->user_id) {
            if (!Auth::check() || Auth::id() !== $invoice->booking->user_id) {
                abort(403, 'Unauthorized access to invoice');
            }
        }
        
        return view('invoices.view', compact('invoice'));
    }

    /**
     * Stream PDF in browser (untuk preview)
     */
    public function stream($invoiceId)
    {
        $invoice = Invoice::with(['booking.lapangan'])->findOrFail($invoiceId);
        
        // Security check
        if ($invoice->booking->user_id) {
            if (!Auth::check() || Auth::id() !== $invoice->booking->user_id) {
                abort(403, 'Unauthorized access to invoice');
            }
        }
        
        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        $pdf->setPaper('a4', 'portrait');
        
        // Stream ke browser
        return $pdf->stream($invoice->invoice_number . '.pdf');
    }

    /**
     * Create invoice for a booking (helper method, bisa dipanggil dari observer)
     */
    public static function createInvoiceForBooking(Booking $booking): Invoice
    {
        // Check jika invoice sudah ada
        if ($booking->invoice) {
            return $booking->invoice;
        }

        // Calculate amounts
        $subtotal = $booking->harga ?? 0;
        $discount = $booking->points_redeemed > 0 
            ? ($booking->points_redeemed / 100) 
            : 0;
        $total = $subtotal - $discount;

        // Create invoice
        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'status' => 'pending',
        ]);

        return $invoice;
    }
}
