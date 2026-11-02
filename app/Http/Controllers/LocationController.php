<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-locations');
        $search = $request->string('q')->trim()->toString();
        $locations = Location::query()
            ->when($search, fn ($query) => $query->where(
                fn ($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('building', 'like', "%{$search}%")
                    ->orWhere('floor', 'like', "%{$search}%")
            ))
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('locations.index', compact('locations'));
    }

    public function create(): View
    {
        Gate::authorize('manage-locations');

        return view('locations.form', ['location' => new Location]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        Location::create($request->validated());

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location): View
    {
        Gate::authorize('manage-locations');

        return view('locations.form', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        Gate::authorize('manage-locations');
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil dihapus. Referensi pada data terkait dikosongkan.');
    }
}
