<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Admin Perbidang.
     */
    public function index(): View
    {

        
        return view('pages.admin-perbidang.dashboard');
    }
}
