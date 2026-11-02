@extends('layouts.app')

@section('title', $asset->exists ? 'Edit Aset' : 'Tambah Aset')

@section('content')
    <h1 class="title">{{ $asset->exists ? 'Edit Aset' : 'Tambah Aset IT' }}</h1>
    <p class="subtitle">Kode aset dibuat otomatis berdasarkan kategori dan tidak dapat diubah.</p>

    <form method="post" action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}" class="card form-grid">
        @csrf
        @if ($asset->exists)
            @method('PUT')
        @endif

        <div class="field">
            <label>Kode aset</label>
            <input id="asset-code-preview" value="{{ $asset->exists ? $asset->code : 'Pilih kategori untuk melihat kode otomatis' }}" readonly>
            <small class="muted">Nomor final diberikan otomatis ketika aset disimpan.</small>
        </div>

        <div class="field">
            <label>Nama aset *</label>
            <input name="name" value="{{ old('name', $asset->name) }}" required>
        </div>

        <div class="field">
            <label>Kategori *</label>
            <select id="asset-category" name="category_id" required>
                <option value="">Pilih</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        data-code-preview="{{ $codePreviews[$category->id] ?? '' }}"
                        @selected(old('category_id', $asset->category_id) == $category->id)>
                        {{ $category->name }}{{ filled($category->asset_prefix) ? ' ('.$category->asset_prefix.')' : ' — prefix belum diatur' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>Lokasi</label>
            <select name="location_id">
                <option value="">Pilih</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(old('location_id', $asset->location_id) == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        @foreach (['serial_number' => 'Serial number', 'pic' => 'PIC/Pengguna', 'brand' => 'Merek', 'model' => 'Model'] as $name => $label)
            <div class="field">
                <label>{{ $label }}</label>
                <input name="{{ $name }}" value="{{ old($name, $asset->$name) }}">
            </div>
        @endforeach

        <div class="field"><label>Tanggal beli</label><input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"></div>
        <div class="field"><label>Garansi sampai</label><input type="date" name="warranty_until" value="{{ old('warranty_until', $asset->warranty_until?->format('Y-m-d')) }}"></div>
        <div class="field"><label>Harga beli</label><input type="number" name="purchase_price" min="0" step="0.01" value="{{ old('purchase_price', $asset->purchase_price) }}"></div>
        <div class="field"><label>Kondisi *</label><select name="condition">@foreach (['new' => 'Baru', 'good' => 'Baik', 'needs_repair' => 'Perlu Perbaikan', 'damaged' => 'Rusak', 'disposed' => 'Dihapus'] as $value => $label)<option value="{{ $value }}" @selected(old('condition', $asset->condition ?: 'good') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label>Status *</label><select name="status">@foreach (['active' => 'Aktif', 'maintenance' => 'Maintenance', 'loaned' => 'Dipinjam', 'disposed' => 'Dihapus'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $asset->status ?: 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field full"><label>Catatan</label><textarea name="notes">{{ old('notes', $asset->notes) }}</textarea></div>
        <div class="full flex"><button class="btn btn-primary">Simpan Aset</button><a class="btn" href="{{ route('assets.index') }}">Batal</a></div>
    </form>
@endsection

@if (! $asset->exists)
    @push('scripts')
        <script>
            (() => {
                const category = document.getElementById('asset-category');
                const preview = document.getElementById('asset-code-preview');
                const updatePreview = () => {
                    const code = category.options[category.selectedIndex]?.dataset.codePreview;
                    preview.value = code || (category.value ? 'Prefix kategori belum diatur' : 'Pilih kategori untuk melihat kode otomatis');
                };
                category.addEventListener('change', updatePreview);
                updatePreview();
            })();
        </script>
    @endpush
@endif
