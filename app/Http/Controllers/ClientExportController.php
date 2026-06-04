<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query();

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

        $headers = [
            'company_name', 'contact_name', 'email', 'phone', 'website',
            'industry', 'city', 'country', 'source', 'status', 'notes',
            'last_contacted_at', 'created_at',
        ];

        $response = new StreamedResponse(function () use ($query, $headers) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $headers);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($clients) use ($stream, $headers) {
                foreach ($clients as $client) {
                    $row = [];
                    foreach ($headers as $h) {
                        $value = $client->{$h} ?? '';
                        $row[] = $value instanceof \BackedEnum ? $value->value : $value;
                    }
                    fputcsv($stream, $row);
                }
            });

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="clients-export-' . now()->format('Y-m-d-His') . '.csv"');

        return $response;
    }
}
