<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\TypeResolver;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $tag = Tag::create([
            'user_id' => $request->attributes->get('user_id'),
            'name' => $validatedData['name'],
        ]);

        \Log::info(message: "in the tag create: {$tag}");

        return new TagResource($tag);
    }

    public function index(Request $request)
    {
        $userId = $request->attributes->get('user_id');

        $tags = Tag::where('user_id', $userId)->get();

        return TagResource::collection($tags);
    }

    public function show(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return response()->json([
                'message' => 'Tag not found',
            ], 404);
        }

        return new TagResource($tag);
    }

    public function get_tag(Request $request, $type, $id)
    {
        $modelClass = TypeResolver::getModelClass($type);

        // Ищем запись по ID
        $model = $modelClass::find($id);

        $tags = $model->tags;

        if (! $tags) {
            return response()->json([
                'message' => 'Tags not found',
            ], 404);
        }

        return TagResource::collection($tags);
    }

    public function attach(Request $request, $type, $id, $tag_id)
    {
        $modelClass = TypeResolver::getModelClass($type);

        if (! $modelClass) {
            return response()->json([
                'message' => 'Invalid type provided',
            ], 400);
        }

        $model = $modelClass::find($id);

        if (! $model) {
            return response()->json([
                'message' => ucfirst($type).' not found',
            ], 404);
        }

        $tag = Tag::find($tag_id);

        if (! $tag) {
            return response()->json([
                'message' => 'Tag not found',
            ], 404);
        }

        $model->tags()->syncWithoutDetaching($tag->id); // если тег уже прикреплен то ничего не делаем

        return response()->json([
            'message' => 'Tag attached successfully to '.ucfirst($type),
        ], 200);
    }

    public function detach(Request $request, $type, $id, $tag_id)
    {
        $modelClass = TypeResolver::getModelClass($type);

        $model = $modelClass::find($id);

        if (! $model) {
            return response()->json([
                'message' => ucfirst($type).' not found',
            ], 404);
        }

        $tag = Tag::find($tag_id);

        if (! $tag) {
            return response()->json([
                'message' => 'Tag not found',
            ], 404);
        }

        if ($model && $tag) {
            $model->tags()->detach($tag_id);

            return TagResource::collection($model->tags()->get());
        }
    }

    public function list(Request $request, $tag_id)
    {
        // to do
        $allModels = [];

        $tag = Tag::find($tag_id);

        if (! $tag) {
            return response()->json([
                'message' => 'Tag not found',
            ], 404);
        }

        // $allModels['tasks'] = $tag->tasks()->get();
        // $allModels['habits'] = $tag->habits()->get();

        foreach (TypeResolver::allTypes() as $type => $modelClass) {
            $relationName = TypeResolver::getTableName($type);
            $allModels[$type] = $tag->$relationName()->get();
        }

        return response()->json($allModels, 200);
    }
}
