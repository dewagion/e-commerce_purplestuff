<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminArtistController extends Controller
{
    public function index()
    {
        $artists = Artist::orderBy('name')->paginate(12);

        return view('admin.artists.index', [
            'artists' => $artists,
            'artist' => new Artist(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        Artist::create([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name']),
        ]);

        return redirect()->route('admin.artists.index')->with('success', 'Artist berhasil ditambahkan.');
    }

    public function edit(Artist $artist)
    {
        $artists = Artist::orderBy('name')->paginate(12);

        return view('admin.artists.index', [
            'artists' => $artists,
            'artist' => $artist,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Artist $artist)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $artist->update([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name'], $artist->id),
        ]);

        return redirect()->route('admin.artists.index')->with('success', 'Artist berhasil diperbarui.');
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();

        return redirect()->route('admin.artists.index')->with('success', 'Artist berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (
            Artist::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}