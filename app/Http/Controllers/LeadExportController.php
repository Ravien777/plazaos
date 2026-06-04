<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query();

        if ($ids = $request->get('ids')) {
            $query->whereIn('id', (array) $ids);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        $headers = [
            'company_name', 'contact_name', 'email', 'phone', 'website',
            'industry', 'city', 'country', 'source', 'status', 'notes',
            'last_contacted_at', 'converted_at', 'created_at',
        ];

        $response = new StreamedResponse(function () use ($query, $headers) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $headers);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($leads) use ($stream, $headers) {
                foreach ($leads as $lead) {
                    $row = [];
                    foreach ($headers as $h) {
                        $value = $lead->{$h} ?? '';
                        $row[] = $value instanceof \BackedEnum ? $value->value : $value;
                    }
                    fputcsv($stream, $row);
                }
            });

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="leads-export-' . now()->format('Y-m-d-His') . '.csv"');

        return $response;
    }
}
