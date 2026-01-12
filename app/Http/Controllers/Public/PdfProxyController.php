<?php

namespace App\Http\Controllers\Public;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class PdfProxyController extends Controller
{
    public function show(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['message' => 'Invalid url'], 400);
        }

        // Basic allowlist: only proxy PDF files
        if (!str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.pdf')) {
            return response()->json(['message' => 'Only PDF files are allowed'], 400);
        }

        try {
            $response = Http::withOptions(['stream' => true])->get($url);
            if (!$response->successful()) {
                return response()->json(['message' => 'Failed to fetch file'], $response->status());
            }

            $contentType = $response->header('Content-Type', 'application/pdf');

            return response()->stream(function () use ($response) {
                foreach ($response->toPsrResponse()->getBody() as $chunk) {
                    echo $chunk;
                }
            }, 200, [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Expose-Headers' => 'Content-Type,Content-Length',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Proxy error'], 500);
        }
    }
}

