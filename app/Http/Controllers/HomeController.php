<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Use cursor pagination for better performance
        $lapangan = Lapangan::where('status', 1)
            ->select('id', 'title', 'category', 'price', 'weekday_price', 'weekend_price', 
                     'peak_hour_start', 'peak_hour_end', 'peak_hour_multiplier', 'image', 'status')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // Secondary sort for stable pagination
            ->cursorPaginate(9); // 9 items per page (3x3 grid)
        
        return view('home', compact('lapangan'));
    }

    public function detail($id)
    {
        // Cache individual lapangan for 10 minutes
        $lapangan = Cache::remember("lapangan_detail_{$id}", 600, function () use ($id) {
            return Lapangan::findOrFail($id);
        });
        
        return view('detail', compact('lapangan'));
    }
}
