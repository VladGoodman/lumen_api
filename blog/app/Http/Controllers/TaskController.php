<?php

namespace App\Http\Controllers;

use App\Http\Helper\ResponseHelper;
use App\Models\Task;
use App\Models\UserLists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{

    public function getItem(Request $request, $id)
    {
        if (!(int)$id || empty($id)) {
            return ResponseHelper::form("Page not found!", 404);
        }
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
            if (!$request->input('withs')) {
                $task = Task::where('executor_user_id', Auth::id())
                    ->where('id', $id)->first();
            } else {
                $task = Task::with($request->input('withs'))
                    ->where('executor_user_id', Auth::id())
                    ->where('id', $id)->first();
            }
            if (!$task) {
                return ResponseHelper::form("Task not found!", 422, $validate->errors());
            }
            return ResponseHelper::form("Received", 200, ["attributes" => $task]);
        } catch (\Exception $errors) {
            return ResponseHelper::form("SQL request error!", 401);
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

            if (!empty($request->input('withs'))) {
                $tasks = Task::with($request->input('withs'))
                    ->where($request->input('filter'))
                    ->where('executor_user_id', Auth::id())
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            } else {
                $tasks = Task::where($request->input('filter'))
                    ->where('executor_user_id', Auth::id())
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }
            return ResponseHelper::form(
                "Received!",
                200,
                ["items" => $tasks->sortBy($request->input("order"))]);

        } catch (\Exception $errors) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }

    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'attributes.name' => 'required|string',
            'attributes.list_id' => 'required|integer',
            'attributes.is_completed' => 'required|boolean',
            'attributes.description' => 'string',
            'attributes.urgency' => 'required|integer',
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form("Error getting!", 422, $validate->errors());
        }

        try {
            $list = UserLists::all()
                ->where('list_id', $request->post('attributes')['list_id'])
                ->where('user_id', Auth::id())->first();
            if (!$list) {
                return ResponseHelper::form(
                    "Error created!",
                    422,
                    ["error" => 'No list with this ID found']);

            } else {
                $new_task = Task::create([
                    'executor_user_id' => Auth::id(),
                    'name' => $request->post('attributes')['name'],
                    'list_id' => $request->post('attributes')['list_id'],
                    'is_completed' => $request->post('attributes')['is_completed'],
                    'urgency' => $request->post('attributes')['urgency'],
                    'description' => isset($request->post('attributes')['description']) ? $request->post('attributes')['description'] : null
                ]);
            }

            if (!$new_task) {
                return ResponseHelper::form(
                    "Error created task",
                    422);
            }
            return ResponseHelper::form(
                "Created!",
                201,
                ["attributes" => $new_task]);
        } catch (\Exception $e) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'attributes.name' => 'string|min:3',
            'attributes.list_id' => 'integer|min:1',
            'attributes.is_completed' => 'boolean',
            'attributes.description' => 'string|min:3',
            'attributes.urgency' => 'integer|min:1',
            'id' => 'integer|min1'
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form("Error getting!", 422, $validate->errors());
        }
        try {
            $updated_task = Auth::user()->tasks->where('id', $id)->first();

            if (!$updated_task) {
                return ResponseHelper::form("No task found!", 401);
            }

            if (!Auth::user()->lists
                ->where('id', $request->post('attributes')['list_id'])
                ->first()) {
                return ResponseHelper::form("No list found!", 401);
            }

            $updated_task->update($request->input('attributes'));

            return ResponseHelper::form(
                "Updated!",
                200,
                ["attributes" => $updated_task]);
        } catch (\Exception $e) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }

    public function delete($id)
    {
        try {
            $deleted_task = Auth::user()->tasks->where('id', $id)->first();

            if (!$deleted_task) {
                return ResponseHelper::form("No task found!", 422);
            }
            if (!$deleted_task->delete()) {
                return ResponseHelper::form("Error deleted!", 422);
            }
            return ResponseHelper::form("Deleted!", 200);
        } catch (\Exception $e) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }
}
