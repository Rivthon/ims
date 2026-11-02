@extends('layouts.app')

@section('title', 'Master Lokasi')

@section('content')
    <div class="flex between">
        <div><h1 class="title">Master Data Lokasi</h1><p class="subtitle">Kelola gedung, lantai, dan ruangan yang digunakan seluruh modul IMS.</p></div>
        <a class="btn btn-primary" href="{{ route('locations.create') }}">+ Tambah Lokasi</a>
    </div>
    <div class="card">
        <form class="flex" method="get" style="margin-bottom:15px"><div class="field" style="flex:1"><input name="q" value="{{ request('q') }}" placeholder="Cari lokasi, gedung, atau lantai..."></div><button class="btn">Cari</button></form>
        <div class="table-wrap"><table><thead><tr><th>Nama lokasi</th><th>Gedung</th><th>Lantai</th><th>Keterangan</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($locations as $location)
                <tr><td><strong>{{ $location->name }}</strong></td><td>{{ $location->building ?: '-' }}</td><td>{{ $location->floor ?: '-' }}</td><td>{{ Str::limit($location->description, 60) ?: '-' }}</td><td><div class="flex"><a class="btn" href="{{ route('locations.edit', $location) }}">Edit</a><form method="post" action="{{ route('locations.destroy', $location) }}" onsubmit="return confirm('Hapus lokasi ini? Referensi lokasi pada data terkait akan dikosongkan.')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td></tr>
            @empty
                <tr><td colspan="5" class="muted">Belum ada lokasi.</td></tr>
            @endforelse
        </tbody></table></div><div style="margin-top:15px">{{ $locations->links() }}</div>
    </div>
@endsection
