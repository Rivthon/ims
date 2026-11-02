<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopologyRequest;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Topology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TopologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('topologies.index', [
            'networkTopologies' => Topology::query()->where('type', 'network')->withCount(['nodes', 'links'])->orderBy('name')->get(),
            'cctvTopologies' => Topology::query()->where('type', 'cctv')->withCount(['nodes', 'links'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('topologies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTopologyRequest $request): RedirectResponse
    {
        $topology = Topology::create([...$request->validated(), 'created_by' => $request->user()->id]);

        return redirect()->route('topologies.show', $topology)->with('success', 'Topologi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Topology $topology): View
    {
        $topology->load(['nodes.asset', 'nodes.location', 'links.source', 'links.target']);

        return view('topologies.show', [
            'topology' => $topology,
            'assets' => Asset::query()->orderBy('code')->get(['id', 'code', 'name']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'canManage' => request()->user()->can('manage-topology'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topology $topology): View
    {
        return view('topologies.edit', compact('topology'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTopologyRequest $request, Topology $topology): RedirectResponse
    {
        $topology->update($request->validated());

        return redirect()->route('topologies.show', $topology)->with('success', 'Topologi diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topology $topology): RedirectResponse
    {
        Gate::authorize('manage-topology');
        $topology->delete();

        return redirect()->route('topologies.index')->with('success', 'Topologi dihapus.');
    }
}
