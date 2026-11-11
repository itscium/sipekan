<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /** Folder foto di disk "local" (storage/app), tidak bisa diakses langsung dari web. */
    private const PHOTO_DIR = 'profile-photos';
    private const PHOTO_SIZE = 400;

    public function __construct (){
        $this->middleware('auth');
    }

    public function index(){
        $user = User::where('id', Auth::id())->first();
        $cek = UserSalary::where('user_id', Auth::id())->first();
        $salary = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
            whereHas('allowances', function ($query){
                $query->where('jenis', 'report')->where('tipe', 'personal');
            })->get();
        $dept = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
        whereHas('allowances', function ($query){
            $query->where('tipe', 'departemen');
        })->get();
        $monthly = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
            whereHas('allowances', function ($query){
                $query->where('tipe', 'personal')->where('kategori', 'monthly');
            })->get();
        return view('personal.profile.index',compact('user', 'salary', 'monthly', 'dept', 'cek'));
    }

    public function edit()
    {
        return view('personal.profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Simpan profil milik user yang sedang login.
     * Nama, email, kode akun, dan wilayah tetap diatur admin (dipakai untuk login dan payroll).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=100,min_height=100'],
            'hapus_foto' => ['nullable', 'boolean'],
            'no_hp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'pendidikan' => ['nullable', 'string', 'max:255'],
            'keahlian' => ['nullable', 'string', 'max:255'],
            'tentang' => ['nullable', 'string', 'max:1000'],
        ], [
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
            'foto.dimensions' => 'Foto minimal 100 × 100 piksel.',
            'no_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'no_hp.max' => 'Nomor HP maksimal 30 karakter.',
            'jenis_kelamin.in' => 'Pilih jenis kelamin yang tersedia.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_lahir.after' => 'Tanggal lahir tidak valid.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
            'pendidikan.max' => 'Pendidikan maksimal 255 karakter.',
            'keahlian.max' => 'Keahlian maksimal 255 karakter.',
            'tentang.max' => 'Tentang saya maksimal 1000 karakter.',
        ]);

        // Rapikan keahlian: "php,  desain ,php" -> "php, desain"
        if (!empty($data['keahlian'])) {
            $skills = array_unique(array_filter(array_map('trim', explode(',', $data['keahlian']))));
            $data['keahlian'] = implode(', ', $skills);
        }

        $oldPhoto = $user->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->storePhoto($request->file('foto'), $user->id);
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        } else {
            unset($data['foto']);
        }
        unset($data['hapus_foto']);

        $user->fill($data)->save();

        if (array_key_exists('foto', $data) && $oldPhoto && $oldPhoto !== $data['foto']) {
            Storage::disk('local')->delete($oldPhoto);
        }

        alert()->success('Tersimpan', 'Profil Anda sudah diperbarui.');
        return redirect()->route('personal.profile.index');
    }

    /** Tampilkan foto profil (hanya untuk user yang sudah login). */
    public function photo(User $user)
    {
        $path = $user->foto;
        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    /**
     * Simpan foto sebagai JPG persegi 400×400 (dipotong di tengah) agar ringan.
     * Jika ekstensi GD tidak tersedia di server, file asli disimpan apa adanya.
     */
    private function storePhoto(UploadedFile $file, int $userId): string
    {
        $name = self::PHOTO_DIR . '/' . $userId . '-' . Str::random(10);

        if (!function_exists('imagecreatetruecolor')) {
            return $file->storeAs(self::PHOTO_DIR, basename($name) . '.' . $file->extension(), 'local');
        }

        try {
            $src = imagecreatefromstring(file_get_contents($file->getRealPath()));
            if (!$src) {
                throw new \RuntimeException('Gambar tidak bisa dibaca');
            }

            // Putar sesuai orientasi kamera HP (EXIF) untuk foto JPG.
            if (function_exists('exif_read_data') && in_array($file->extension(), ['jpg', 'jpeg'])) {
                $exif = @exif_read_data($file->getRealPath());
                $rotate = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
                if ($rotate) {
                    $src = imagerotate($src, $rotate, 0);
                }
            }

            $w = imagesx($src);
            $h = imagesy($src);
            $side = min($w, $h);
            $dst = imagecreatetruecolor(self::PHOTO_SIZE, self::PHOTO_SIZE);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2),
                self::PHOTO_SIZE, self::PHOTO_SIZE, $side, $side);

            ob_start();
            imagejpeg($dst, null, 85);
            $jpg = ob_get_clean();
            imagedestroy($src);
            imagedestroy($dst);

            Storage::disk('local')->put($name . '.jpg', $jpg);
            return $name . '.jpg';
        } catch (\Throwable $e) {
            Log::warning('Foto profil gagal diproses, disimpan apa adanya: ' . $e->getMessage());
            return $file->storeAs(self::PHOTO_DIR, basename($name) . '.' . $file->extension(), 'local');
        }
    }
}
