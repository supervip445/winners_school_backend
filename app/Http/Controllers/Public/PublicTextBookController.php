<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TextBook;
use Illuminate\Http\Request;

class PublicTextBookController extends Controller
{
    /**
     * List textbooks for public users (paginated).
     */
    public function index()
    {
        $books = TextBook::with(['class', 'subject', 'teacher'])
            ->latest()
            ->paginate(10);

        $books->getCollection()->transform(function ($book) {
            return $this->transformResource($book);
        });

        return response()->json([
            'success' => true,
            'message' => 'Text books fetched successfully',
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'last_page' => $books->lastPage(),
                'has_more_pages' => $books->hasMorePages(),
            ],
        ]);
    }

    /**
     * Show a single textbook.
     */
    public function show($id)
    {
        $book = TextBook::with(['class', 'subject', 'teacher'])->findOrFail($id);

        return response()->json([
            'data' => $this->transformResource($book),
        ]);
    }

    private function transformResource(TextBook $book): TextBook
    {
        $book->pdf_url = $book->pdf_file ? asset('storage/' . $book->pdf_file) : null;
        $book->image_url = $book->image_file ? asset('storage/' . $book->image_file) : null;
        return $book;
    }
}

