<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DictionaryEntry;
use Illuminate\Http\Request;

class PublicDictionaryController extends Controller
{
    public function index(Request $request)
    {
        $query = DictionaryEntry::query()->orderBy('english_word');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('english_word', 'like', "%{$search}%")
                    ->orWhere('myanmar_meaning', 'like', "%{$search}%");
            });
        }

        $entries = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $entries->items(),
            'pagination' => [
                'current_page' => $entries->currentPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'last_page' => $entries->lastPage(),
                'has_more_pages' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function show(DictionaryEntry $dictionary_entry)
    {
        return response()->json(['data' => $dictionary_entry]);
    }
}

