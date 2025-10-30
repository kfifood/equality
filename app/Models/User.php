<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users'; // Explicitly define table name

    protected $fillable = [
        'username',
        'password',
        'rfid_code',
        'full_name',
        'role',
        'phone',
        'department',
        'is_active',
        'login_at',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'login_at' => 'datetime',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Method untuk mencari user berdasarkan RFID
    public static function findByRfid($rfidCode)
    {
        return static::where('rfid_code', $rfidCode)
                    ->where('is_active', true)
                    ->first();
    }

    // Method untuk cek role
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isSuperadmin()
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // Relasi ke riwayat penggunaan sebagai PIC (jika diperlukan)
    public function riwayatPenggunaan()
    {
        return $this->hasMany(RiwayatPenggunaan::class, 'pic', 'full_name');
    }

    // Scope untuk user aktif
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk role tertentu
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Scope untuk department tertentu
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Accessor untuk status aktif
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }

    // Accessor untuk format last login
    public function getLastLoginFormattedAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->format('d/m/Y H:i') : 'Belum pernah login';
    }

    // Method untuk update last login
    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => $this->login_at,
            'login_at' => now()
        ]);
    }

    // Method untuk cek permissions berdasarkan role
    public function canAccessTimbangan()
    {
        return in_array($this->role, ['superadmin', 'admin', 'manager', 'supervisor', 'staff']);
    }

    public function canManageUsers()
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function canManageMasterData()
    {
        return in_array($this->role, ['superadmin', 'admin', 'manager']);
    }

    public function canViewReports()
    {
        return in_array($this->role, ['superadmin', 'admin', 'manager', 'supervisor']);
    }
}