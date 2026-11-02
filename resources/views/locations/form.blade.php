@extends('layouts.app')

@section('title', $location->exists ? 'Edit Lokasi' : 'Tambah Lokasi')

@section('content')
    <h1 class="title">{{ $location->exists ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h1>
    <p class="subtitle">Lokasi yang disimpan langsung tersedia pada form aset, ticket, topologi, CCTV, dan laporan.</p>
    <form method="post" action="{{ $location->exists ? route('locations.update', $location) : route('locations.store') }}" class="card form-grid">
        @csrf
        @if ($location->exists) @method('PUT') @endif
        <div class="field"><label>Nama lokasi/ruangan *</label><input name="name" value="{{ old('name', $location->name) }}" required placeholder="Contoh: Lab Komputer 1"></div>
        <div class="field"><label>Gedung</label><input name="building" value="{{ old('building', $location->building) }}" placeholder="Contoh: Gedung Akademik"></div>
        <div class="field"><label>Lantai</label><input name="floor" value="{{ old('floor', $location->floor) }}" placeholder="Contoh: 2"></div>
        <div class="field full"><label>Keterangan</label><textarea name="description" placeholder="Keterangan atau petunjuk lokasi">{{ old('description', $location->description) }}</textarea></div>
        <div class="full flex"><button class="btn btn-primary">Simpan Lokasi</button><a class="btn" href="{{ route('locations.index') }}">Batal</a></div>
    </form>
@endsection
