<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\ResponseHelperService;
use App\Services\TypeResolver;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validatedData = $this->validateTagData($request);

            $tag = Tag::create([
                'user_id' => $request->attributes->get('user_id'),
                'name' => $validatedData['name'],
            ]);

            \Log::info("in the tag create: {$tag}");

            return ResponseHelperService::success(new TagResource($tag), 201);
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'validation_error',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');

            $tags = Tag::where('user_id', $userId)->get();

            return ResponseHelperService::success(TagResource::collection($tags));
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return ResponseHelperService::error([
                    [
                        'code' => 'access_denied',
                        'message' => 'You do not have permission to access this tag',
                    ],
                ], 403);
            }

            return ResponseHelperService::success(new TagResource($tag));
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return ResponseHelperService::error([
                    [
                        'code' => 'access_denied',
                        'message' => 'You do not have permission to delete this tag',
                    ],
                ], 403);
            }

            $tag->delete();

            return ResponseHelperService::success(null, 204); // 204 No Content
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return ResponseHelperService::error([
                    [
                        'code' => 'access_denied',
                        'message' => 'You do not have permission to update this tag',
                    ],
                ], 403);
            }

            $validatedData = $this->validateTagData($request); // Исправлено: validateTagData вместо validateTaskData

            $tag->update([
                'name' => $validatedData['name'],
            ]);

            return ResponseHelperService::success(new TagResource($tag));
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'validation_error',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    public function get_tag(Request $request, $type, $id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (! $model) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => ucfirst($type).' not found',
                    ],
                ], 404);
            }

            $tags = $model->tags;

            if ($tags->isEmpty()) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tags not found',
                    ],
                ], 404);
            }

            return ResponseHelperService::success(TagResource::collection($tags));
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function attach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            if (! $modelClass) {
                return ResponseHelperService::error([
                    [
                        'code' => 'invalid_input',
                        'message' => 'Invalid type provided',
                    ],
                ], 400);
            }

            $model = $modelClass::find($id);

            if (! $model) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => ucfirst($type).' not found',
                    ],
                ], 404);
            }

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            $model->tags()->syncWithoutDetaching($tag->id);

            return ResponseHelperService::success([
                'message' => 'Tag attached successfully to '.ucfirst($type),
            ]);
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function detach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (! $model) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => ucfirst($type).' not found',
                    ],
                ], 404);
            }

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            $model->tags()->detach($tag_id);

            return ResponseHelperService::success(TagResource::collection($model->tags()->get()));
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function list(Request $request, $tag_id)
    {
        try {
            $allModels = [];

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Tag not found',
                    ],
                ], 404);
            }

            foreach (TypeResolver::allTypes() as $type => $modelClass) {
                $relationName = TypeResolver::getTableName($type);
                $allModels[$type] = $tag->$relationName()->get();
            }

            return ResponseHelperService::success($allModels);
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    private function validateTagData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string',
        ]);
    }
}
