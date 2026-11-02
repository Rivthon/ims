<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportCenterControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reporter_is_forbidden_from_report_center(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);

        $this->actingAs($reporter)->get(route('reports.index'))->assertForbidden();
    }

    public function test_auditor_can_filter_and_preview_asset_report(): void
    {
        $auditor = User::factory()->create(['role' => 'auditor']);
        Asset::create(['code' => 'AST-REPORT-01', 'name' => 'Laptop Audit', 'condition' => 'good', 'status' => 'active']);

        $this->actingAs($auditor)->get(route('reports.index', ['type' => 'assets', 'status' => 'active']))
            ->assertOk()->assertSee('Laporan Peralatan IT')->assertSee('AST-REPORT-01');
    }

    public function test_authorized_user_can_export_pdf_and_excel(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        Asset::create(['code' => 'AST-EXPORT-01', 'name' => 'PC Export', 'condition' => 'good', 'status' => 'active']);

        $pdf = $this->actingAs($staff)->get(route('reports.export', ['type' => 'assets', 'format' => 'pdf']));
        $excel = $this->actingAs($staff)->get(route('reports.export', ['type' => 'assets', 'format' => 'excel']));

        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $excel->assertOk()->assertDownload();
    }
}
