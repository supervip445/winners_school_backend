<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TextBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TextBookController extends Controller
{
    /**
     * Paginated list of textbooks.
     */
    public function index()
    {
        $textBooks = TextBook::with(['class', 'subject', 'teacher'])
            ->latest()
            ->paginate(10);

        $textBooks->getCollection()->transform(fn ($book) => $this->transformResource($book));

        return response()->json([
            'success' => true,
            'message' => 'Text books fetched successfully',
            'data' => $textBooks->items(),
            'pagination' => [
                'current_page' => $textBooks->currentPage(),
                'per_page' => $textBooks->perPage(),
                'total' => $textBooks->total(),
                'last_page' => $textBooks->lastPage(),
                'has_more_pages' => $textBooks->hasMorePages(),
            ],
        ]);
    }

    /**
     * Store a new textbook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'required|file|mimes:pdf|max:204800', // 200MB
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB
        ]);

        // Handle uploads
        if ($request->hasFile('pdf_file')) {
            $validated['pdf_file'] = $request->file('pdf_file')->store('textbooks/pdfs', 'public');
        }

        if ($request->hasFile('image_file')) {
            $validated['image_file'] = $request->file('image_file')->store('textbooks/images', 'public');
        }

        $textBook = TextBook::create($validated)->load(['class', 'subject', 'teacher']);

        return response()->json([
            'data' => $this->transformResource($textBook),
        ], 201);
    }

    /**
     * Show a single textbook.
     */
    public function show($id)
    {
        $textBook = TextBook::with(['class', 'subject', 'teacher'])->findOrFail($id);

        return response()->json([
            'data' => $this->transformResource($textBook),
        ]);
    }

    /**
     * Update a textbook.
     */
    public function update(Request $request, $id)
    {
        $textBook = TextBook::findOrFail($id);

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:204800',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Replace files if provided
        if ($request->hasFile('pdf_file')) {
            if ($textBook->pdf_file) {
                Storage::disk('public')->delete($textBook->pdf_file);
            }
            $validated['pdf_file'] = $request->file('pdf_file')->store('textbooks/pdfs', 'public');
        }

        if ($request->hasFile('image_file')) {
            if ($textBook->image_file) {
                Storage::disk('public')->delete($textBook->image_file);
            }
            $validated['image_file'] = $request->file('image_file')->store('textbooks/images', 'public');
        }

        $textBook->update($validated);
        $textBook->load(['class', 'subject', 'teacher']);

        return response()->json([
            'data' => $this->transformResource($textBook),
        ]);
    }

    /**
     * Delete a textbook and its files.
     */
    public function destroy($id)
    {
        $textBook = TextBook::findOrFail($id);

        if ($textBook->pdf_file) {
            Storage::disk('public')->delete($textBook->pdf_file);
        }

        if ($textBook->image_file) {
            Storage::disk('public')->delete($textBook->image_file);
        }

        $textBook->delete();

        return response()->json(['message' => 'Text book deleted successfully']);
    }

    private function transformResource(TextBook $textBook): TextBook
    {
        $textBook->pdf_url = $textBook->pdf_file ? asset('storage/' . $textBook->pdf_file) : null;
        $textBook->image_url = $textBook->image_file ? asset('storage/' . $textBook->image_file) : null;

        return $textBook;
    }
}

