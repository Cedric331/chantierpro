<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\UseCases\Reports\GetReportData;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function __construct(private readonly GetReportData $getReportData)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'from', 'to']);
        $data = $this->getReportData->handle($account, $filters);

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Rapport chantier']);
        fputcsv($handle, ['Période', $this->formatPeriod($data['filters'])]);
        fputcsv($handle, []);
        fputcsv($handle, ['Résumé', 'Total']);
        foreach ($data['summary'] as $label => $value) {
            fputcsv($handle, [$label, $value]);
        }

        $this->appendSection($handle, 'Incidents', $data['incidents']);
        $this->appendSection($handle, 'Validations', $data['validations']);
        $this->appendSection($handle, 'Décisions', $data['decisions']);
        $this->appendSection($handle, 'Photos', $data['photos']);
        $this->appendSection($handle, 'Tâches', $data['tasks']);
        $this->appendSection($handle, 'Jalons', $data['milestones']);

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rapport-chantier.csv"',
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function formatPeriod(array $filters): string
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        if ($from && $to) {
            return "{$from} au {$to}";
        }

        if ($from) {
            return "Depuis {$from}";
        }

        if ($to) {
            return "Jusqu'au {$to}";
        }

        return 'Toutes dates';
    }

    /**
     * @param resource $handle
     * @param iterable<int, mixed> $rows
     */
    private function appendSection($handle, string $title, iterable $rows): void
    {
        fputcsv($handle, []);
        fputcsv($handle, [$title]);

        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = is_array($row) ? $row : $row->toArray();
        }

        if (count($normalized) === 0) {
            fputcsv($handle, ['Aucune donnée']);
            return;
        }

        fputcsv($handle, array_keys($normalized[0]));
        foreach ($normalized as $row) {
            fputcsv($handle, array_values($row));
        }
    }
}

