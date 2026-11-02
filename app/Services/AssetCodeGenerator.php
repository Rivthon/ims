<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCodeSequence;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AssetCodeGenerator
{
    public function generate(Category $category): string
    {
        $prefix = $this->prefix($category);

        return DB::transaction(function () use ($prefix): string {
            AssetCodeSequence::firstOrCreate(['prefix' => $prefix]);
            $sequence = AssetCodeSequence::query()->where('prefix', $prefix)->lockForUpdate()->firstOrFail();
            $nextNumber = max($sequence->last_number, $this->largestExistingNumber($prefix)) + 1;
            $sequence->update(['last_number' => $nextNumber]);

            return $this->format($prefix, $nextNumber);
        }, 3);
    }

    public function preview(Category $category): string
    {
        $prefix = $this->prefix($category);
        $reservedNumber = AssetCodeSequence::query()->where('prefix', $prefix)->value('last_number') ?? 0;

        return $this->format($prefix, max($reservedNumber, $this->largestExistingNumber($prefix)) + 1);
    }

    private function prefix(Category $category): string
    {
        abort_unless($category->type === 'asset' && filled($category->asset_prefix), 422, 'Kategori aset belum memiliki prefix kode.');

        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $category->asset_prefix));
    }

    private function largestExistingNumber(string $prefix): int
    {
        $base = $this->base($prefix).'-';

        return Asset::query()->where('code', 'like', $base.'%')->pluck('code')
            ->map(function (string $code) use ($base): int {
                $suffix = substr($code, strlen($base));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })->max() ?? 0;
    }

    private function format(string $prefix, int $number): string
    {
        return sprintf('%s-%03d', $this->base($prefix), $number);
    }

    private function base(string $prefix): string
    {
        return 'SBH-ICT-'.$prefix;
    }
}
