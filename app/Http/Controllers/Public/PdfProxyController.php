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
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        if (!str_ends_with(strtolower($path), '.pdf')) {
            return response()->json(['message' => 'Only PDF files are allowed'], 400);
        }

        try {
            // Fetch full body to avoid zero-byte streams in some hosts
            $response = Http::withHeaders(['Accept' => 'application/pdf'])->get($url);
            if (!$response->successful()) {
                return response()->json(['message' => 'Failed to fetch file'], $response->status());
            }

            $body = $response->body();
            if (empty($body)) {
                return response()->json(['message' => 'Empty PDF content'], 502);
            }

            $contentType = $response->header('Content-Type', 'application/pdf');
            $length = strlen($body);

            return response($body, 200, [
                'Content-Type' => $contentType,
                'Content-Length' => $length,
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Range',
                'Access-Control-Expose-Headers' => 'Content-Type,Content-Length,Accept-Ranges,Content-Range',
                'Accept-Ranges' => 'bytes',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Proxy error'], 500);
        }
    }
}

