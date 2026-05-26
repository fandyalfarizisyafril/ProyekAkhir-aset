<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Kepala Dinas.
     */
    public function index(): View
    {
        return view('pages.kepala-dinas.dashboard');
    }
}
