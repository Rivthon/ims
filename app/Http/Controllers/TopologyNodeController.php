<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditTopologyNodeRequest;
use App\Http\Requests\StoreTopologyNodeRequest;
use App\Http\Requests\UpdateTopologyNodeRequest;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Topology;
use App\Models\TopologyNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TopologyNodeController extends Controller
{
    public function store(StoreTopologyNodeRequest $request, Topology $topology): RedirectResponse
    {
        $topology->nodes()->create($request->validated());

        return back()->with('success', 'Node perangkat ditambahkan.');
    }

    public function update(UpdateTopologyNodeRequest $request, Topology $topology, TopologyNode $node): JsonResponse
    {
        abort_unless($node->topology_id === $topology->id, 404);
        $node->update($request->validated());

        return response()->json(['message' => 'Posisi tersimpan.']);
    }

    public function edit(Topology $topology, TopologyNode $node): View
    {
        Gate::authorize('manage-topology');
        abort_unless($node->topology_id === $topology->id, 404);

        return view('topologies.nodes.edit', [
            'topology' => $topology,
            'node' => $node,
            'assets' => Asset::query()->orderBy('code')->get(['id', 'code', 'name']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateDetails(EditTopologyNodeRequest $request, Topology $topology, TopologyNode $node): RedirectResponse
    {
        abort_unless($node->topology_id === $topology->id, 404);
        $node->update($request->validated());

        return redirect()->route('topologies.show', $topology)->with('success', 'Detail node berhasil diperbarui.');
    }

    public function destroy(Topology $topology, TopologyNode $node): RedirectResponse
    {
        Gate::authorize('manage-topology');
        abort_unless($node->topology_id === $topology->id, 404);
        $node->delete();

        return back()->with('success', 'Node dihapus.');
    }
}
