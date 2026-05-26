<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard User Umum.
     */
    public function index(): View
    {
        return view('pages.user.dashboard');
    }
}
