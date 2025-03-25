<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;

class TagController extends Controller
{
    public function store(Request $request)
    {
        // Валидация входных данных
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $tag = Tag::create([
            'user_id' => $request->attributes->get('user_id'),
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'message' => 'Тег успешно создан!',
            'name' => $tag,
        ], 201);
    }
}
