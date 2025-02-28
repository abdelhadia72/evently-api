<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CategoryController extends CrudController
{
    protected $table = 'categories';

    protected $modelClass = Category::class;

    protected $restricted = ['create', 'update', 'delete'];

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return $this->modelClass;
    }

    protected function isAdmin()
    {
        $user = Auth::user();

        return $user && $user->hasRole(\App\Enums\ROLE::ADMIN);
    }

    protected function adminOnlyError()
    {
        return response()->json(
            [
                'success' => false,
                'errors' => ['Only administrators can manage category'],
            ],
            403
        );
    }

    public function readAll(Request $request)
    {
        try {
            $items = $this->getModelClass()::all();

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CategoryController.readAll : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')],
            ]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            if (! $this->isAdmin()) {
                return $this->adminOnlyError();
            }

            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);
            if (! $item) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => [__($this->getTable().'.not_found')],
                    ],
                    404
                );
            }

            // Validate request data using rules
            if (method_exists($item, 'rules')) {
                $request->validate($item->rules($id));
            } elseif (method_exists($modelClass, 'rules')) {
                $request->validate($modelClass::rules($id));
            }

            $data = $request->all();
            $item->fill($data);
            $item->save();

            $item = $item->fresh();

            if (property_exists($modelClass, 'cacheKey')) {
                cache()->forget($modelClass::$cacheKey);
            }

            return response()->json([
                'success' => true,
                'message' => ['The Category Updated Successfully'],
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CategoryController.updateOne : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()],
            ]);
        }
    }

    public function createOne(Request $request)
    {
        try {
            if (! $this->isAdmin()) {
                return $this->adminOnlyError();
            }

            $modelClass = $this->getModelClass();
            $model = new $modelClass;

            if (method_exists($model, 'rules')) {
                $request->validate($model->rules());
            }

            $data = $request->all();
            $item = $modelClass::create($data);

            if (property_exists($modelClass, 'cacheKey')) {
                cache()->forget($modelClass::$cacheKey);
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => ['The Category Created Successfully'],
                    'data' => $item,
                ],
                201
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function CategoryController.createOne: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}
