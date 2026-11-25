<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'email',
        'password',
        'alamat',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'wilayah_id',
        'ACCNT_CODE',
        'travel_account',
        'status',
        'foto',
        'pendidikan',
        'keahlian',
        'tentang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // Sengaja tidak ada di $fillable: hanya diisi lewat menu Admin wilayah (admin utama)
        'admin_wilayah' => 'array',
    ];

    /* ---------------- Admin utama vs admin wilayah ---------------- */

    /** Admin utama: tipe admin tanpa batasan wilayah (semua akun admin lama). */
    public function isSuperAdmin(): bool
    {
        return $this->type === 'admin' && empty($this->admin_wilayah);
    }

    /** Admin wilayah: tipe admin yang hanya mengelola Business Unit tertentu. */
    public function isRegionalAdmin(): bool
    {
        return $this->type === 'admin' && !empty($this->admin_wilayah);
    }

    /**
     * Wilayah yang boleh dikelola: null = semua (admin utama), [] = tidak ada (bukan admin).
     *
     * @return int[]|null
     */
    public function managedWilayahIds(): ?array
    {
        if ($this->type !== 'admin') {
            return [];
        }
        return $this->isRegionalAdmin() ? array_values(array_map('intval', (array) $this->admin_wilayah)) : null;
    }

    public function managesWilayah($wilayahId): bool
    {
        $ids = $this->managedWilayahIds();
        return $ids === null || ($wilayahId !== null && $wilayahId !== '' && in_array((int) $wilayahId, $ids, true));
    }

    /** Batasi kueri ke wilayah yang dikelola admin ini (admin utama: tanpa batas). */
    public function scopeWithinAdminScope($query, User $admin, string $column = 'wilayah_id')
    {
        $ids = $admin->managedWilayahIds();
        return $ids === null ? $query : $query->whereIn($column, $ids ?: [0]);
    }

    /**
     * Id akun admin utama. Kolom admin_wilayah mungkin belum ada (migrasi belum dijalankan):
     * dalam keadaan itu semua admin dianggap admin utama, seperti sebelumnya.
     */
    public static function superAdminIdsQuery()
    {
        $q = static::select('id')->where('type', 'admin');
        return static::hasAdminScopeColumn() ? $q->whereNull('admin_wilayah') : $q;
    }

    public static function hasAdminScopeColumn(): bool
    {
        static $has = null;
        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('users', 'admin_wilayah');
    }

    public function adminlte_image()
    {
        return $this->foto_url;
    }

    public function adminlte_desc()
    {
        return optional($this->wilayah)->nama ?? '';
    }

    public function adminlte_profile_url()
    {
        return 'personal/profile';
    }

    /** URL foto profil, atau null jika belum ada foto. */
    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->attributes['foto'] ?? null)) {
            return null;
        }
        // File bisa tidak ada di server ini (mis. diunggah di server lain yang memakai database yang sama).
        // Dalam kasus itu tampilkan inisial, bukan gambar rusak.
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($this->attributes['foto'])) {
            return null;
        }
        // Nama file ikut di URL supaya browser memuat foto baru setelah diganti.
        return route('personal.profile.photo', ['user' => $this->id, 'v' => basename($this->attributes['foto'])]);
    }

    /** Dua huruf awal nama, dipakai jika belum ada foto. */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name ?? 'U'));
        return strtoupper(mb_substr($words[0] ?? 'U', 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));
    }

    /** Daftar keahlian dari teks yang dipisah koma. */
    public function getKeahlianListAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->attributes['keahlian'] ?? ''))));
    }

    public function kepala_departemen (){
        return $this->hasOne(Departemen::class, 'kepala_departemen', 'id');
    }

    public function wilayah (){
        return $this->belongsTo(Wilayah::class);
    }

    public function roles () {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }
    public function hasRole($role)
    {
        return (bool)$this->roles()->where('role', $role)->first();
    }

    public function allowance(){
        return $this->hasMany(UserSalary::class, 'user_id');
    }

//    public function hasAnyRole($roles) {
//        $res = 0;
//        foreach ($roles as $role)
//            if ($this->hasRole(trim($role)))
//                $res++;
//        return $res > 0 ? true : false;
//    }
}
