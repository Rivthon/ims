<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopologyLinkRequest;
use App\Models\Topology;
use App\Models\TopologyLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TopologyLinkController extends Controller
{
    public function store(StoreTopologyLinkRequest $request, Topology $topology): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $validNodeCount = $topology->nodes()->whereKey([$data['source_node_id'], $data['target_node_id']])->count();
        if ($validNodeCount !== 2) {
            throw ValidationException::withMessages(['source_node_id' => 'Kedua node harus berasal dari topologi yang sama.']);
        }
        $connectionExists = $topology->links()->where(function ($query) use ($data) {
            $query->where(function ($pair) use ($data) {
                $pair->where('source_node_id', $data['source_node_id'])->where('target_node_id', $data['target_node_id']);
            })->orWhere(function ($pair) use ($data) {
                $pair->where('source_node_id', $data['target_node_id'])->where('target_node_id', $data['source_node_id']);
            });
        })->exists();
        if ($connectionExists) {
            throw ValidationException::withMessages(['source_node_id' => 'Kedua node ini sudah terhubung.']);
        }
        $link = $topology->links()->create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Koneksi berhasil dibuat.', 'link_id' => $link->id], 201);
        }

        return back()->with('success', 'Koneksi antar-node ditambahkan.');
    }

    public function destroy(Topology $topology, TopologyLink $link): RedirectResponse
    {
        Gate::authorize('manage-topology');
        abort_unless($link->topology_id === $topology->id, 404);
        $link->delete();

        return back()->with('success', 'Koneksi dihapus.');
    }
}
