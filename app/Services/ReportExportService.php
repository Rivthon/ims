<?php

namespace App\Services;

use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    /** @param array{title: string, headers: array<int, string>, rows: array<int, array<int, mixed>>} $report */
    public function pdf(array $report, array $filters): Response
    {
        $dompdf = new Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml(view('reports.pdf', compact('report', 'filters'))->render());
        $dompdf->setPaper('A4', count($report['headers']) > 8 ? 'landscape' : 'portrait');
        $dompdf->render();
        $filename = Str::slug($report['title']).'-'.now()->format('Ymd-His').'.pdf';

        return response($dompdf->output(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.$filename.'"']);
    }

    /** @param array{title: string, headers: array<int, string>, rows: array<int, array<int, mixed>>} $report */
    public function excel(array $report): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');
        $sheet->fromArray($report['headers'], null, 'A1');
        if ($report['rows'] !== []) {
            $sheet->fromArray($report['rows'], null, 'A2');
        }
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB');
        for ($index = 1; $index <= Coordinate::columnIndexFromString($lastColumn); $index++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
        $relativePath = 'reports/'.Str::slug($report['title']).'-'.now()->format('Ymd-His').'.xlsx';
        Storage::makeDirectory('reports');
        $absolutePath = Storage::path($relativePath);
        (new Xlsx($spreadsheet))->save($absolutePath);
        $spreadsheet->disconnectWorksheets();

        return response()->download($absolutePath)->deleteFileAfterSend(true);
    }
}
