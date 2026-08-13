<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Get all website content.
     */
    public function index()
    {
        $contents = Content::query()
            ->orderBy('key')
            ->pluck('value', 'key');

        return response()->json($contents);
    }

    /**
     * Create or update website content.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $content = Content::updateOrCreate(
            ['key' => $validated['key']],
            ['value' => $validated['value'] ?? null]
        );

        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }
}