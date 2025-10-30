<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function credentials(Request $request)
    {
        return [
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => true,
        ];
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->login_at) {
            $user->last_login_at = $user->login_at;
        }
        
        $user->login_at = Carbon::now();
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $this->redirectTo
            ]);
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'errors' => ['Login gagal. Periksa username dan password Anda.']
            ], 422);
        }

        return parent::sendFailedLoginResponse($request);
    }

    // Method baru untuk login dengan RFID
    public function loginWithRfid(Request $request)
    {
        Log::info('=== RFID LOGIN ATTEMPT START ===');
        Log::info('RFID Code Received:', ['rfid_code' => $request->rfid_code]);
        
        try {
            $request->validate([
                'rfid_code' => 'required|string'
            ]);

            Log::info('Validation passed');

            // Cari user berdasarkan RFID code
            $user = User::where('rfid_code', $request->rfid_code)
                        ->where('is_active', true)
                        ->first();

            Log::info('User search result:', [
                'user_found' => $user ? $user->username : 'NULL',
                'user_id' => $user ? $user->id : 'NULL',
                'is_active' => $user ? $user->is_active : 'NULL'
            ]);

            if ($user) {
                // Login user
                Auth::login($user);
                Log::info('Auth login successful');

                // Update login time
                if ($user->login_at) {
                    $user->last_login_at = $user->login_at;
                }
                $user->login_at = Carbon::now();
                $user->save();

                Log::info('User login time updated');

                Log::info('=== RFID LOGIN SUCCESS ===');

                return response()->json([
                    'success' => true,
                    'message' => 'Login dengan RFID berhasil',
                    'redirect' => $this->redirectTo
                ]);
            }

            Log::warning('=== RFID LOGIN FAILED - User not found or inactive ===');

            return response()->json([
                'success' => false,
                'errors' => ['Kartu RFID tidak terdaftar atau tidak aktif']
            ], 422);

        } catch (\Exception $e) {
            Log::error('=== RFID LOGIN ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => ['Terjadi kesalahan sistem: ' . $e->getMessage()]
            ], 500);
        }
    }
}