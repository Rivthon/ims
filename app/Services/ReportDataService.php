<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Document;
use App\Models\MaintenanceSchedule;
use App\Models\MonitoringHistory;
use App\Models\Ticket;
use App\Models\TopologyNode;
use Illuminate\Database\Eloquent\Builder;

class ReportDataService
{
    /** @return array<string, string> */
    public function types(): array
    {
        return ['tickets' => 'Perbaikan IT', 'assets' => 'Peralatan IT', 'maintenance' => 'Checklist & Maintenance', 'monitoring' => 'Uptime/Downtime', 'cctv' => 'Perangkat CCTV', 'documents' => 'Dokumen SOP/ISO'];
    }

    /** @param array{date_from?: string|null, date_to?: string|null, status?: string|null, location_id?: int|null} $filters
     * @return array{title: string, headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function generate(string $type, array $filters): array
    {
        return match ($type) {
            'assets' => $this->assets($filters), 'maintenance' => $this->maintenance($filters),
            'monitoring' => $this->monitoring($filters), 'cctv' => $this->cctv($filters),
            'documents' => $this->documents($filters), default => $this->tickets($filters),
        };
    }

    private function applyFilters(Builder $query, array $filters, string $dateColumn, bool $hasLocation = false): Builder
    {
        return $query->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate($dateColumn, '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate($dateColumn, '<=', $date))
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($hasLocation && ($filters['location_id'] ?? null), fn (Builder $q) => $q->where('location_id', $filters['location_id']));
    }

    private function tickets(array $filters): array
    {
        $rows = $this->applyFilters(Ticket::query()->with(['reporter:id,name', 'technician:id,name', 'category:id,name', 'location:id,name']), $filters, 'created_at', true)->latest()->limit(5000)->get()->map(fn (Ticket $item) => [$item->number, $item->title, $item->reporter->name, $item->location?->name, $item->category?->name, $item->priority, $item->status, $item->technician?->name, $item->created_at->format('d/m/Y H:i'), $item->completed_at?->format('d/m/Y H:i')])->all();

        return ['title' => 'Laporan Perbaikan IT', 'headers' => ['Nomor', 'Keluhan', 'Pelapor', 'Lokasi', 'Kategori', 'Prioritas', 'Status', 'Teknisi', 'Dibuat', 'Selesai'], 'rows' => $rows];
    }

    private function assets(array $filters): array
    {
        $rows = $this->applyFilters(Asset::query()->with(['category:id,name', 'location:id,name']), $filters, 'created_at', true)->latest()->limit(5000)->get()->map(fn (Asset $item) => [$item->code, $item->name, $item->category?->name, $item->location?->name, $item->brand, $item->model, $item->serial_number, $item->pic, $item->condition, $item->status, $item->purchase_date?->format('d/m/Y')])->all();

        return ['title' => 'Laporan Peralatan IT', 'headers' => ['Kode', 'Nama', 'Kategori', 'Lokasi', 'Merek', 'Model', 'Serial', 'PIC', 'Kondisi', 'Status', 'Tanggal Beli'], 'rows' => $rows];
    }

    private function maintenance(array $filters): array
    {
        $rows = $this->applyFilters(MaintenanceSchedule::query()->with(['asset:id,code,name', 'assignee:id,name']), $filters, 'scheduled_at')->orderByDesc('scheduled_at')->limit(5000)->get()->map(fn (MaintenanceSchedule $item) => [$item->title, $item->asset?->code, $item->asset?->name, $item->frequency, $item->scheduled_at->format('d/m/Y'), $item->assignee?->name, $item->status, $item->result_notes])->all();

        return ['title' => 'Laporan Checklist & Maintenance', 'headers' => ['Kegiatan', 'Kode Aset', 'Perangkat', 'Frekuensi', 'Jadwal', 'PIC', 'Status', 'Catatan'], 'rows' => $rows];
    }

    private function monitoring(array $filters): array
    {
        $rows = $this->applyFilters(MonitoringHistory::query()->with('monitoredService:id,name,url'), $filters, 'checked_at')->latest('checked_at')->limit(5000)->get()->map(fn (MonitoringHistory $item) => [$item->monitoredService?->name, $item->monitoredService?->url, $item->status, $item->http_code, $item->response_time_ms, $item->message, $item->checked_at->format('d/m/Y H:i')])->all();

        return ['title' => 'Laporan Uptime/Downtime', 'headers' => ['Layanan', 'URL', 'Status', 'HTTP', 'Response (ms)', 'Pesan', 'Waktu Cek'], 'rows' => $rows];
    }

    private function cctv(array $filters): array
    {
        $query = TopologyNode::query()->with(['topology:id,name,type', 'location:id,name'])->whereHas('topology', fn (Builder $q) => $q->where('type', 'cctv'));
        $rows = $this->applyFilters($query, $filters, 'created_at', true)->orderBy('name')->limit(5000)->get()->map(fn (TopologyNode $item) => [$item->code, $item->name, $item->topology->name, $item->location?->name, $item->ip_address, $item->mac_address, $item->connection_type, $item->channel, $item->resolution, $item->recording_mode, $item->retention_days, $item->status])->all();

        return ['title' => 'Laporan Perangkat CCTV', 'headers' => ['Kode', 'Kamera', 'Topologi', 'Lokasi', 'IP', 'MAC', 'Koneksi', 'Channel', 'Resolusi', 'Recording', 'Retensi (hari)', 'Status'], 'rows' => $rows];
    }

    private function documents(array $filters): array
    {
        $rows = $this->applyFilters(Document::query()->with(['pic:id,name', 'creator:id,name']), $filters, 'created_at')->latest()->limit(5000)->get()->map(fn (Document $item) => [$item->document_number, $item->title, $item->category, $item->current_version, $item->status, $item->effective_date?->format('d/m/Y'), $item->pic?->name, $item->creator->name])->all();

        return ['title' => 'Laporan Dokumen SOP/ISO', 'headers' => ['Nomor', 'Judul', 'Kategori', 'Versi', 'Status', 'Berlaku', 'PIC', 'Dibuat Oleh'], 'rows' => $rows];
    }
}
