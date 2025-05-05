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
            $validatedData = $this->validateTagData($request);

            $tag = Tag::create([
                'user_id' => $request->attributes->get('user_id'),
                'name' => $validatedData['name'],
            ]);

            \Log::info("in the tag create: {$tag}");

            return $this->successResponse(new TagResource($tag), 201);
        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', $e->getMessage(), 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');

            $tags = Tag::where('user_id', $userId)->get();

            return $this->successResponse(TagResource::collection($tags));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to access this tag', 403);
            }

            return $this->successResponse(new TagResource($tag));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to delete this tag', 403);
            }

            $tag->delete();

            return $this->successResponse(null, 204); // 204 No Content
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tag = Tag::find($id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            if ($tag->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to update this tag', 403);
            }

            $validatedData = $this->validateTaskData($request);

            $tag->update([
                'name' => $validatedData['name'],
            ]);

            return $this->successResponse(new TagResource($tag));
        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', $e->getMessage(), 400);
        }
    }

    public function get_tag(Request $request, $type, $id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (! $model) {
                return $this->errorResponse('not_found', ucfirst($type).' not found', 404);
            }

            $tags = $model->tags;

            if ($tags->isEmpty()) {
                return $this->errorResponse('not_found', 'Tags not found', 404);
            }

            return $this->successResponse(TagResource::collection($tags));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function attach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            if (! $modelClass) {
                return $this->errorResponse('invalid_input', 'Invalid type provided', 400);
            }

            $model = $modelClass::find($id);

            if (! $model) {
                return $this->errorResponse('not_found', ucfirst($type).' not found', 404);
            }

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            $model->tags()->syncWithoutDetaching($tag->id);

            return $this->successResponse([
                'message' => 'Tag attached successfully to '.ucfirst($type),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function detach(Request $request, $type, $id, $tag_id)
    {
        try {
            $modelClass = TypeResolver::getModelClass($type);

            $model = $modelClass::find($id);

            if (! $model) {
                return $this->errorResponse('not_found', ucfirst($type).' not found', 404);
            }

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            $model->tags()->detach($tag_id);

            return $this->successResponse(TagResource::collection($model->tags()->get()));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function list(Request $request, $tag_id)
    {
        try {
            $allModels = [];

            $tag = Tag::find($tag_id);

            if (! $tag) {
                return $this->errorResponse('not_found', 'Tag not found', 404);
            }

            foreach (TypeResolver::allTypes() as $type => $modelClass) {
                $relationName = TypeResolver::getTableName($type);
                $allModels[$type] = $tag->$relationName()->get();
            }

            return $this->successResponse($allModels);
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    private function validateTagData(Request $request)
    {
        return $request->validate([
            'name' => 'required|string',
        ]);
    }
}
