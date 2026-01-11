<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DictionaryEntry;
use Illuminate\Http\Request;

class DictionaryApiController extends Controller
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'english_word' => ['required', 'string', 'max:255', 'unique:dictionary_entries,english_word'],
            'myanmar_meaning' => ['required', 'string', 'max:255'],
            'example' => ['nullable', 'string'],
        ]);

        $entry = DictionaryEntry::create($data);

        return response()->json(['data' => $entry], 201);
    }

    public function show(DictionaryEntry $dictionary_entry)
    {
        return response()->json(['data' => $dictionary_entry]);
    }

    public function update(Request $request, DictionaryEntry $dictionary_entry)
    {
        $data = $request->validate([
            'english_word' => ['required', 'string', 'max:255', 'unique:dictionary_entries,english_word,' . $dictionary_entry->id],
            'myanmar_meaning' => ['required', 'string', 'max:255'],
            'example' => ['nullable', 'string'],
        ]);

        $dictionary_entry->update($data);

        return response()->json(['data' => $dictionary_entry]);
    }

    public function destroy(DictionaryEntry $dictionary_entry)
    {
        $dictionary_entry->delete();

        return response()->json(['message' => 'Dictionary entry deleted']);
    }
}

