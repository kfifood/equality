<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'full_name' => 'required',
            'role' => 'required|in:superadmin,manager,staff,supervisor,admin',
            'phone' => 'nullable',
            'department' => 'nullable',
            'rfid_code' => 'nullable|unique:users,rfid_code'
        ]);

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'role' => $request->role,
            'phone' => $request->phone,
            'department' => $request->department,
            'rfid_code' => $request->rfid_code,
            'is_active' => true
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => [
                'required',
                Rule::unique('users')->ignore($user->id)
            ],
            'full_name' => 'required',
            'role' => 'required|in:superadmin,manager,staff,supervisor,admin',
            'phone' => 'nullable',
            'department' => 'nullable',
            'rfid_code' => [
                'nullable',
                Rule::unique('users')->ignore($user->id)
            ]
        ]);

        $user->update($request->only([
            'username', 'full_name', 'role', 'phone', 'department', 'rfid_code'
        ]));

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('users.index')
            ->with('success', "User berhasil $status.");
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:6'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Password berhasil diubah.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}