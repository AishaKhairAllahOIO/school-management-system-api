<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ContentController extends Controller
{
    /**
     * Get all website content.
     */
    public function index()
    {
        $contents = Content::query()
            ->orderBy('key')
            ->pluck('value', 'key')
            ->toArray();

        $result = [];

        foreach ($contents as $key => $value) {
            Arr::set($result, $key, $value);
        }

        return response()->json($result);
    }

    /**
     * Create or update website content.
     *
     * If the key exists -> update.
     * If the key does not exist -> create.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $content = Content::updateOrCreate(
            [
                'key' => $validated['key'],
            ],
            [
                'value' => $validated['value'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Content saved successfully.',
            'content' => $content,
        ]);
    }
}