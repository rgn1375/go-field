<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Cache lapangan list for 5 minutes with pagination
        $page = request()->get('page', 1);
        $cacheKey = "lapangan_home_page_{$page}";
        
        $lapangan = Cache::remember($cacheKey, 300, function () {
            return Lapangan::where('status', 1)
                ->select('id', 'title', 'category', 'price', 'weekday_price', 'weekend_price', 
                         'peak_hour_start', 'peak_hour_end', 'peak_hour_multiplier', 'image', 'status')
                ->orderBy('created_at', 'desc')
                ->paginate(6);
        });
        
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
