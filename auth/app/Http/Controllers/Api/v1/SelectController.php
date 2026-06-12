<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Dedoc\Scramble\Attributes as OpenApi;

#[OpenApi\PathItem]
class SelectController extends Controller
{
    public function getSelectSystems(Request $request): JsonResponse
    {
        try {
            $systems = System::select('id as value', 'name as label')->get();
            return response()->json($systems, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function getSelectPermissionsByView(int $viewId): JsonResponse
    {
        try {
            $permissions = Permission::select('permissions.id as value', 'actions.code as label')
                ->join('actions', 'permissions.action_id', '=', 'actions.id')
                ->where('permissions.view_id', $viewId)
                ->orderBy('actions.code', 'asc')
                ->get();

            return response()->json($permissions, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }


}
