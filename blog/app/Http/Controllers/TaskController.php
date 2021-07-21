<?php

namespace App\Http\Controllers;

use App\Models\Lists;
use App\Models\Task;
use App\Models\User;
use App\Models\UserLists;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Closure;
use function PHPUnit\Framework\isType;

class TaskController extends Controller
{

    public function getItem(Request $request, $id)
    {
        try {
            if( (int) $id === 0){
                return response()->json([
                    "message" => 'Page not found!'
                ], 404);
            }
            if ($id === null){
                return response()->json([
                    "message" => 'Error!'
                ], 404);
            }
            $validate = Validator::make($request->all(), [
                'with' => 'array',
                'with.*' => 'string'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error getting!'
                ], 422);
            }
            $task = Task::with($request->input('withs'))
                ->where('executor_user_id', Auth::id())
                ->where('id', $id)->first();
            if ($task == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'Task not found!'
                ], 422);
            }

            return response()->json([
                "data" => [
                    "attributes" => $task
                ],
                "message" => 'Received!'
            ], 201);

        } catch (\Exception $errors) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 401);
        }
    }

    public function getItems(Request $request)
    {
        try {
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
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error get items!'
                ], 401);
            }
            $page = $request->input('page') - 1;
            $per_page = $request->input('per_page');


            if ($request->input('withs') !== null) {
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

            return response()->json([
                "data" => [
                    "items" => $tasks->sortBy($request->input("order"))
                ],
                "message" => 'Received!'
            ], 200);

        } catch (\Exception $errors) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 401);
        }
    }

    public function create(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'attributes.name' => 'required|string|min:3',
                'attributes.list_id' => 'required|integer|min:1',
                'attributes.is_completed' => 'required|boolean',
                'attributes.description' => 'string|min:3',
                'attributes.urgency' => 'required|integer|min:1',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error created!'
                ], 422);
            }

            $new_task = new Task();
            $new_task->executor_user_id = Auth::id();
            $new_task->name = $request->post('attributes')['name'];
            $list = Auth::user()->lists
                ->where('id', $request->post('attributes')['list_id'])
                ->first();
            if ($list == false) {
                return response()->json([
                    "data" => [
                        "error" => 'No list with this ID found'
                    ],
                    "message" => 'Error created!'
                ], 422);
            }

            $new_task->list_id = $request->post('attributes')['list_id'];
            $new_task->is_completed = $request->post('attributes')['is_completed'];
            $new_task->urgency = $request->post('attributes')['urgency'];
            $new_task->description = isset($request->post('attributes')['description']) ? $request->post('attributes')['description'] : null;
            if ($new_task->save() == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'SQL request error!'
                ], 422);
            }

            return response()->json([
                "data" => [
                    "attributes" => $new_task
                ],
                "message" => 'Created!'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validate = Validator::make($request->all(), [
                'attributes.name' => 'string|min:3',
                'attributes.list_id' => 'integer|min:1',
                'attributes.is_completed' => 'boolean',
                'attributes.description' => 'string|min:3',
                'attributes.urgency' => 'integer|min:1',
                'id' => 'integer|min1'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error created!'
                ], 401);
            }

            $updated_task = Auth::user()->tasks->where('id', $id)->first();

            if ($updated_task == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'No task found!'
                ], 401);
            }

            if (Auth::user()->lists
                    ->where('id', $request->post('attributes')['list_id'])
                    ->first() == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'No list found!'
                ], 401);
            }

            $updated_task->update($request->input('attributes'));

            return response()->json([
                "data" => [
                    "attributes" => $updated_task
                ],
                "message" => 'Updated!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 401);
        }
    }

    public function delete($id)
    {
        try {
            $deleted_task = Auth::user()->tasks->where('id', $id)->first();

            if ($deleted_task == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'No task found!'
                ], 422);
            }
            if ($deleted_task->delete()) {
                return response()->json([
                    "data" => null,
                    "message" => 'Deleted!'
                ], 200);
            }
            return response()->json([
                "data" => null,
                "message" => 'Error deleted!'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
        }
    }
}
