<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadImport;
use App\Models\User;
use App\Notifications\ImportCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use League\Csv\Reader;

class ImportLeadsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly LeadImport $import
    ) {}

    public function handle(): void
    {
        $this->import->update(['status' => 'processing']);

        $csv = Reader::createFromPath(storage_path("app/{$this->import->filepath}"));
        $csv->setHeaderOffset($this->import->column_mapping['header_row'] ?? 0);

        $mapping = $this->import->column_mapping['fields'] ?? [];
        $strategy = $this->import->duplicate_strategy;
        $records = $csv->getRecords();
        $total = 0;
        $processed = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $row) {
            $total++;

            try {
                DB::beginTransaction();

                $data = $this->mapRow($row, $mapping);
                $existing = $this->findDuplicate($data);

                if ($existing && $strategy === 'skip') {
                    $processed++;
                } elseif ($existing && $strategy === 'update') {
                    $existing->update($data);
                    $processed++;
                } else {
                    Lead::create($data);
                    $processed++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "Row {$total}: {$e->getMessage()}";
            }

            if ($total % 50 === 0) {
                $this->import->update([
                    'total_rows' => $total,
                    'processed' => $processed,
                    'failed' => $failed,
                    'errors' => $errors,
                ]);
            }
        }

        $this->import->update([
            'total_rows' => $total,
            'processed' => $processed,
            'failed' => $failed,
            'errors' => $errors,
            'status' => $failed > 0 && $processed === 0 ? 'failed' : 'completed',
        ]);

        Notification::send(User::all(), new ImportCompleted($this->import));
    }

    private function mapRow(array $row, array $mapping): array
    {
        $data = [];
        foreach ($mapping as $csvColumn => $leadField) {
            if (isset($row[$csvColumn])) {
                $data[$leadField] = $row[$csvColumn];
            }
        }
        return $data;
    }

    private function findDuplicate(array $data): ?Lead
    {
        if (!empty($data['email'])) {
            $lead = Lead::where('email', $data['email'])->first();
            if ($lead) {
                return $lead;
            }
        }

        if (!empty($data['website'])) {
            return Lead::where('website', $data['website'])->first();
        }

        return null;
    }
}
