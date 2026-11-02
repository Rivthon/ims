<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\ReportDataService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportCenterController extends Controller
{
    public function index(Request $request, ReportDataService $reports): View
    {
        Gate::authorize('view-reports');
        $filters = $this->validatedFilters($request, $reports);

        return view('reports.index', ['types' => $reports->types(), 'filters' => $filters, 'report' => $reports->generate($filters['type'], $filters), 'locations' => Location::query()->orderBy('name')->get(['id', 'name'])]);
    }

    public function export(Request $request, ReportDataService $reports, ReportExportService $exporter): Response
    {
        Gate::authorize('view-reports');
        $filters = $this->validatedFilters($request, $reports);
        $format = $request->validate(['format' => ['required', Rule::in(['pdf', 'excel'])]])['format'];
        $report = $reports->generate($filters['type'], $filters);

        return $format === 'pdf' ? $exporter->pdf($report, $filters) : $exporter->excel($report);
    }

    private function validatedFilters(Request $request, ReportDataService $reports): array
    {
        $filters = array_replace(['type' => 'tickets', 'date_from' => null, 'date_to' => null, 'status' => null, 'location_id' => null], $request->validate(['type' => ['nullable', Rule::in(array_keys($reports->types()))], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'status' => ['nullable', 'string', 'max:50'], 'location_id' => ['nullable', 'integer', 'exists:locations,id']]));
        $filters['type'] ??= 'tickets';

        return $filters;
    }
}
