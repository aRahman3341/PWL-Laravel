<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SitePhotos;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function index()
    {
        $urls = [];
        foreach (array_keys(SitePhotos::SLOTS) as $slot) {
            $urls[$slot] = SitePhotos::url($slot);
        }

        return view('admin.photos', ['slots' => SitePhotos::SLOTS, 'urls' => $urls]);
    }

    public function store(Request $r, string $slot)
    {
        abort_unless(isset(SitePhotos::SLOTS[$slot]), 404);

        $r->validate(
            ['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192'],
            [
                'photo.required' => 'Pilih file foto dulu.',
                'photo.image'    => 'File harus berupa gambar (JPG, PNG, atau WebP).',
                'photo.mimes'    => 'Format yang diterima: JPG, PNG, atau WebP.',
                'photo.max'      => 'Ukuran foto maksimal 8 MB.',
                'photo.uploaded' => 'Upload gagal. Periksa batas upload_max_filesize di php.ini.',
            ]
        );

        [, , $maxW, $png] = SitePhotos::SLOTS[$slot];

        if (! SitePhotos::save($r->file('photo'), $slot, $maxW, $png)) {
            return back()->withErrors(['photo' => 'Foto gagal disimpan. Pastikan folder public/company bisa ditulisi.']);
        }

        app(SupabaseAdmin::class)->audit('upload_photo', 'photos', null, ['slot' => $slot]);

        return back()->with('ok', 'Foto "' . SitePhotos::SLOTS[$slot][0] . '" diperbarui.');
    }

    public function destroy(string $slot)
    {
        abort_unless(isset(SitePhotos::SLOTS[$slot]), 404);

        SitePhotos::remove($slot);
        app(SupabaseAdmin::class)->audit('delete_photo', 'photos', null, ['slot' => $slot]);

        return back()->with('ok', 'Foto dihapus.');
    }
}
