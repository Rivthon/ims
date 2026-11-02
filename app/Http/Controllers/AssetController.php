<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Services\AssetCodeGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $assets = Asset::with(['category', 'location'])
            ->when($request->string('q')->toString(), fn ($query, $search) => $query->where(
                fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('assets.index', compact('assets'));
    }

    public function create(AssetCodeGenerator $codeGenerator): View
    {
        $categories = Category::query()->where('type', 'asset')->orderBy('name')->get();

        return view('assets.form', [
            'asset' => new Asset,
            'categories' => $categories,
            'locations' => Location::orderBy('name')->get(),
            'codePreviews' => $categories->mapWithKeys(fn (Category $category) => [
                $category->id => filled($category->asset_prefix) ? $codeGenerator->preview($category) : null,
            ]),
        ]);
    }

    public function store(StoreAssetRequest $request, AssetCodeGenerator $codeGenerator): RedirectResponse
    {
        $asset = DB::transaction(function () use ($request, $codeGenerator): Asset {
            $data = $request->validated();
            $category = Category::query()->findOrFail($data['category_id']);
            $data['code'] = $codeGenerator->generate($category);

            return Asset::create($data);
        });

        return redirect()->route('assets.index')->with('success', "Aset {$asset->code} ditambahkan.");
    }

    public function edit(Asset $asset): View
    {
        return view('assets.form', [
            'asset' => $asset,
            'categories' => Category::query()->where('type', 'asset')->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'codePreviews' => collect(),
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()->route('assets.index')->with('success', 'Aset diperbarui.');
    }
}
