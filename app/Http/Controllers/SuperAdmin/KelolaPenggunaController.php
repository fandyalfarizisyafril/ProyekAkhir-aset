<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KelolaPenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // Base Query with relationship loading
        $query = User::with('bidang');
        
        // Search filter (Nama, NIP, or Bidang)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nip', 'like', '%' . $search . '%')
                  ->orWhereHas('bidang', function ($qb) use ($search) {
                      $qb->where('nama_bidang', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Paginate users (10 per page)
        $users = $query->paginate(10)->withQueryString();
        
        // Calculate Statistics
        $totalUsers = User::count();
        $superAdminCount = User::where('role', 'Super Admin')->count();
        $suspendedCount = User::where('status', 'Ditangguhkan')->count();
        
        return view('pages.super-admin.KelolaPengguna.index', compact(
            'users', 
            'totalUsers', 
            'superAdminCount', 
            'suspendedCount',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bidangs = Bidang::all();
        $roles = ['Super Admin', 'Admin Perbidang', 'Kepala Dinas', 'User'];
        $statuses = ['Aktif', 'Non-Aktif', 'Ditangguhkan'];
        
        return view('pages.super-admin.KelolaPengguna.create', compact('bidangs', 'roles', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        
        // Hash password
        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('super-admin.pengguna.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $pengguna)
    {
        return redirect()->route('super-admin.pengguna.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $pengguna)
    {
        $bidangs = Bidang::all();
        $roles = ['Super Admin', 'Admin Perbidang', 'Kepala Dinas', 'User'];
        $statuses = ['Aktif', 'Non-Aktif', 'Ditangguhkan'];
        
        return view('pages.super-admin.KelolaPengguna.edit', compact('pengguna', 'bidangs', 'roles', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $pengguna)
    {
        $validated = $request->validated();
        
        // Check if password was provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Keep old password
            unset($validated['password']);
        }
        
        $pengguna->update($validated);
        
        return redirect()->route('super-admin.pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $pengguna)
    {
        // Don't allow user to delete themselves
        if (auth()->id() === $pengguna->id) {
            return redirect()->route('super-admin.pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        
        $pengguna->delete();
        
        return redirect()->route('super-admin.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
