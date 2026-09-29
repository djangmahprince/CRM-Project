<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExporter
{
    /**
     * @param  array{title: string, columns: list<string>, rows: list<array<string, mixed>>}  $data
     */
    public function download(array $data, string $slug, string $format): StreamedResponse
    {
        return match ($format) {
            'xlsx', 'excel' => $this->excelXml($data, $slug),
            'pdf' => $this->pdf($data, $slug),
            default => $this->csv($data, $slug),
        };
    }

    /**
     * @param  array{title: string, columns: list<string>, rows: list<array<string, mixed>>}  $data
     */
    private function csv(array $data, string $slug): StreamedResponse
    {
        $filename = $slug.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $data['columns']);
            foreach ($data['rows'] as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * SpreadsheetML Excel XML — opens in Excel without extra PHP packages.
     *
     * @param  array{title: string, columns: list<string>, rows: list<array<string, mixed>>}  $data
     */
    private function excelXml(array $data, string $slug): StreamedResponse
    {
        $filename = $slug.'-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($data): void {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Report"><Table>';
            echo '<Row>';
            foreach ($data['columns'] as $column) {
                echo '<Cell><Data ss:Type="String">'.$this->xml($column).'</Data></Cell>';
            }
            echo '</Row>';
            foreach ($data['rows'] as $row) {
                echo '<Row>';
                foreach (array_values($row) as $value) {
                    $type = is_numeric($value) ? 'Number' : 'String';
                    echo '<Cell><Data ss:Type="'.$type.'">'.$this->xml((string) ($value ?? '')).'</Data></Cell>';
                }
                echo '</Row>';
            }
            echo '</Table></Worksheet></Workbook>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    /**
     * Minimal single-page PDF for tabular export (no external PDF library).
     *
     * @param  array{title: string, columns: list<string>, rows: list<array<string, mixed>>}  $data
     */
    private function pdf(array $data, string $slug): StreamedResponse
    {
        $filename = $slug.'-'.now()->format('Ymd-His').'.pdf';
        $lines = [$data['title'], str_repeat('=', min(72, max(20, strlen($data['title'])))), implode(' | ', $data['columns'])];
        foreach ($data['rows'] as $row) {
            $lines[] = implode(' | ', array_map(fn ($v) => (string) ($v ?? ''), array_values($row)));
        }
        if ($data['rows'] === []) {
            $lines[] = 'No rows match this report.';
        }

        $content = $this->buildSimplePdf($lines);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @param  list<string>  $lines
     */
    private function buildSimplePdf(array $lines): string
    {
        $escaped = array_map(function (string $line): string {
            $safe = substr(preg_replace('/[^\x20-\x7E]/', '?', $line) ?? '', 0, 110);

            return '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $safe).') Tj';
        }, array_slice($lines, 0, 60));

        $y = 750;
        $contentStream = "BT /F1 10 Tf 40 {$y} Td\n";
        foreach ($escaped as $index => $tj) {
            if ($index > 0) {
                $contentStream .= "0 -14 Td\n";
            }
            $contentStream .= $tj."\n";
        }
        $contentStream .= 'ET';

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objects[] = '<< /Length '.strlen($contentStream)." >>\nstream\n{$contentStream}\nendstream";
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref\n0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
