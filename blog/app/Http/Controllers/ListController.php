<?php

namespace App\Http\Controllers;

use App\Events\CreatingListsEvent;
use App\Http\Helper\ResponseHelper;
use App\Models\Lists;
use App\Models\UserLists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ListController extends Controller
{
    public function getItem(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'with' => 'array',
            'with.*' => 'string'
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form(
                "Error getting!",
                422,
                $validate->errors());
        }
        try {
            if (empty($request->input('withs'))) {
                $user_lists = Lists::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->where('id', $id)
                    ->get()->first();
            } else {
                $user_lists = Lists::with($request->input('withs'))
                    ->with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->where('id', $id)
                    ->get()->first();
            }

            if (!$user_lists) {
                return ResponseHelper::form(
                    "List not found!",
                    422);
            }
            return ResponseHelper::form(
                "Received!",
                200,
                ["attributes" => $user_lists]);

        } catch (\Exception $errors) {
            return ResponseHelper::form(
                "SQL request error!",
                422);
        }
    }

    public function getItems(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'filter' => 'array',
            'filter.*' => 'array',
            'order' => 'array',
            'with' => 'array',
            'with.*' => 'string|min:1',
            'per_page' => 'integer|min:1',
            'page' => 'integer|min:1',
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form(
                "Error getting!",
                422,
                $validate->errors());
        }

        $page = $request->input('page') - 1;
        $per_page = $request->input('per_page');
        try {
            if (empty($request->input('withs'))) {
                $user_lists = Lists::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            } else {
                $user_lists = Lists::with($request->input('withs'))
                    ->with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }
            return ResponseHelper::form(
                "Received!",
                200,
                ["items" => $user_lists->sortBy($request->input("order"))]);

        } catch (\Exception $errors) {
            return ResponseHelper::form(
                "SQL request error!",
                422);
        }
    }

    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'attributes.name' => 'required|string',
            'attributes.count_tasks' => 'required|integer',
            'attributes.is_completed' => 'required|boolean',
            'attributes.is_closed' => 'required|boolean'
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form(
                "Error getting!",
                422,
                $validate->errors());
        }
        $request_data_list = $request->input('attributes');
        try {
            $new_list = Lists::create([
                'name' => $request_data_list['name'],
                "count_tasks" => 0,
                'is_completed' => $request_data_list['is_completed'],
                'is_closed' => $request_data_list['is_closed'],
            ]);
            if (!$new_list) {
                return ResponseHelper::form(
                    "Error created!",
                    422);
            }
            event(new CreatingListsEvent($new_list));
            return ResponseHelper::form(
                "Created!",
                201,
                ["attributes" => $new_list]);
        } catch (\Exception $e) {
            return ResponseHelper::form(
                "SQL request error!",
                422);
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'attributes.name' => 'string|min:3',
            'attributes.count_tasks' => 'integer|min:1',
            'attributes.is_completed' => 'boolean',
            'attributes.is_closed' => 'boolean'
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form(
                "Error getting!",
                422,
                $validate->errors());
        }
        $request_data_list = $request->input('attributes');

        try {

            $updated_user_lists = UserLists::all()
                ->where('user_id', '=', Auth::user()->id)
                ->where('list_id', '=', $id);

            if (!$updated_user_lists) {
                return ResponseHelper::form(
                    "No listing found!",
                    422,
                    $validate->errors());
            }
            $updated_list = $updated_user_lists->first()->list;

            $updated_list->update($request_data_list);

            return ResponseHelper::form(
                "Updated!",
                201,
                ["attributes" => $updated_list]);

        } catch (\Exception $e) {
            return ResponseHelper::form(
                "SQL request error!",
                422);
        }
    }

    public function delete($id)
    {
        try {
            $deleted_user_lists = UserLists::all()
                ->where('user_id', '=', Auth::user()->id)
                ->where('list_id', '=', $id)->first();

            if (!$deleted_user_lists) {
                return ResponseHelper::form("No listing found!", 401);
            }
            if (!$deleted_user_lists->list->delete()) {
                return ResponseHelper::form("Error deleted!", 422);
            }
            return ResponseHelper::form("Deleted!", 200);
        } catch (\Exception $e) {
            return ResponseHelper::form(
                "SQL request error!",
                422);
        }
    }
}
