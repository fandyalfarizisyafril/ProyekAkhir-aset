<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Super Admin.
     */
    public function index(): View
    {
        return view('pages.super-admin.dashboard');
    }
}
