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
            'id_user' => 'required|integer',
            'tag' => 'required|string',
        ]);

        $tag = Tag::create([
            'id_user' => $validatedData['id_user'],
            'tag' => $validatedData['tag'],
        ]);

        return response()->json([
            'message' => 'Тег успешно создан!',
            'tag' => $tag,
        ], 201);
    }
}
