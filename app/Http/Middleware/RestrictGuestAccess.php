<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi user dengan role 'guest' agar hanya bisa mengakses:
 * - halaman data kalibrasi (index)
 * - cetak sticker (single & batch)
 * - logout
 *
 * Route lain (dashboard, users, master data, dst) otomatis di-redirect
 * balik ke halaman kalibrasi. Middleware ini bekerja berdasarkan NAMA ROUTE,
 * jadi harus dipasang setelah 'auth' agar auth()->user() sudah tersedia.
 *
 * Cara pasang: cukup taruh file ini di app/Http/Middleware/, lalu di
 * routes/web.php pasang langsung pakai class name (tidak perlu daftar
 * alias di Kernel.php / bootstrap/app.php):
 *
 *     Route::middleware(['auth', \App\Http\Middleware\RestrictGuestAccess::class])
 *         ->group(function () { ... });
 */
class RestrictGuestAccess
{
    /**
     * Nama route yang BOLEH diakses oleh role 'guest'.
     * Sesuaikan/tambahkan jika ada route lain yang perlu diizinkan
     * (misal: kalibrasi.show jika ada halaman detail).
     */
    protected array $allowedRouteNames = [
        'kalibrasi.index',
        'kalibrasi.sticker',
        'kalibrasi.sticker.batch',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->role === 'guest') {
            $routeName = $request->route()?->getName();

            if (!in_array($routeName, $this->allowedRouteNames, true)) {
                return redirect()
                    ->route('kalibrasi.index')
                    ->with('error', 'Akun Anda hanya memiliki akses untuk melihat dan mencetak sticker kalibrasi.');
            }
        }

        return $next($request);
    }
}