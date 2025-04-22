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
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);

            $tag = Tag::create([
                'user_id' => $request->attributes->get('user_id'),
                'name' => $validatedData['name'],
            ]);

            \Log::info("in the tag create: {$tag}");

            return response()->json([
                'data' => new TagResource($tag),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'validation_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');

            $tags = Tag::where('user_id', $userId)->get();

            return response()->json([
                'data' => TagResource::collection($tags),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (!$tag) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Tag not found',
                        ],
                    ],
                ], 404);
            }

            return response()->json([
                'data' => new TagResource($tag),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function get_tag(Request $request, $type, $id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (!$model) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => ucfirst($type) . ' not found',
                        ],
                    ],
                ], 404);
            }

            $tags = $model->tags;

            if ($tags->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Tags not found',
                        ],
                    ],
                ], 404);
            }

            return response()->json([
                'data' => TagResource::collection($tags),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function attach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            if (!$modelClass) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'invalid_input',
                            'message' => 'Invalid type provided',
                        ],
                    ],
                ], 400);
            }

            $model = $modelClass::find($id);

            if (!$model) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => ucfirst($type) . ' not found',
                        ],
                    ],
                ], 404);
            }

            $tag = Tag::find($tag_id);

            if (!$tag) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Tag not found',
                        ],
                    ],
                ], 404);
            }

            $model->tags()->syncWithoutDetaching($tag->id);

            return response()->json([
                'data' => [
                    'message' => 'Tag attached successfully to ' . ucfirst($type),
                ],
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function detach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (!$model) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => ucfirst($type) . ' not found',
                        ],
                    ],
                ], 404);
            }

            $tag = Tag::find($tag_id);

            if (!$tag) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Tag not found',
                        ],
                    ],
                ], 404);
            }

            $model->tags()->detach($tag_id);

            return response()->json([
                'data' => TagResource::collection($model->tags()->get()),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function list(Request $request, $tag_id)
    {
        try {
            $allModels = [];

            $tag = Tag::find($tag_id);

            if (!$tag) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Tag not found',
                        ],
                    ],
                ], 404);
            }

            foreach (TypeResolver::allTypes() as $type => $modelClass) {
                $relationName = TypeResolver::getTableName($type);
                $allModels[$type] = $tag->$relationName()->get();
            }

            return response()->json([
                'data' => $allModels,
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }
}