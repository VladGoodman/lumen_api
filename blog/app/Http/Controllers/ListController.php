<?php

namespace App\Http\Controllers;

use App\Models\Lists;
use App\Models\User;
use App\Models\UserLists;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ListController extends Controller
{
    public function getItem(Request $request, $id)
    {
        try {
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
            if($request->input('withs') == false) {
                $user_lists = Lists::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->where('id', $id)
                    ->get()->first();
            }else{
                $user_lists = Lists::with($request->input('withs'))
                    ->with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->where('id', $id)
                    ->get()->first();
            }


            if ($user_lists == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'List not found!'
                ], 422);
            }

            return response()->json([
                "data" => [
                    "attributes" => $user_lists
                ],
                "message" => 'Received!'
            ], 200);

        } catch (\Exception $errors) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
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
                ], 422);
            }

            $page = $request->input('page') - 1;
            $per_page = $request->input('per_page');
            $filter = $request->input('filter');
            if($request->input('withs') == false){
                $user_lists = Lists::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }else{
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

            return response()->json([
                "data" => [
                    "items" => $user_lists->sortBy($request->input("order"))
                ],
                "message" => 'Received!'
            ], 200);

        } catch (\Exception $errors) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
        }
    }

    public function create(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'attributes.name' => 'required|string',
                'attributes.count_tasks' => 'required|integer',
                'attributes.is_completed' => 'required|boolean',
                'attributes.is_closed' => 'required|boolean'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error created!'
                ], 422);
            }
            $list = new Lists;
            $list->name = $request->post('attributes')['name'];
            $list->is_completed = true;
            $list->is_closed = false;
            if ($list->save()) {
                $user_list = new UserLists();
                $user_list->list_id = $list->id;
                $user_list->user_id = Auth::user()->id;
                if ($user_list->save()) {
                    return response()->json([
                        "data" => [
                            "attributes" => Lists::all()->where('id', $list->id)->first()
                        ],
                        "message" => 'Created!'
                    ], 201);
                }
            }
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validate = Validator::make($request->all(), [
                'attributes.name' => 'string|min:3',
                'attributes.count_tasks' => 'integer|min:1',
                'attributes.is_completed' => 'boolean',
                'attributes.is_closed' => 'boolean'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    "data" => $validate->errors(),
                    "message" => 'Error created!'
                ], 422);
            }

            $updated_user_lists = UserLists::all()
                ->where('user_id', '=', Auth::user()->id)
                ->where('list_id', '=', $id);

            if ($updated_user_lists == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'No listing found!'
                ], 422);
            }
            $updated_list = $updated_user_lists->first()->list;

            $updated_list->update($request->input('attributes'));

            return response()->json([
                "data" => [
                    "attributes" => $updated_list
                ],
                "message" => 'Updated!'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
        }
    }

    public function delete($id)
    {
        try {
            $deleted_user_lists = UserLists::all()
                ->where('user_id', '=', Auth::user()->id)
                ->where('list_id', '=', $id)->first();

            if ($deleted_user_lists == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'No listing found!'
                ], 401);
            }
            if ($deleted_user_lists->list->delete()) {
                return response()->json([
                    "data" => null,
                    "message" => 'Deleted!'
                ], 200);
            } else {
                return response()->json([
                    "data" => null,
                    "message" => 'Error deleted!'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                "data" => null,
                "message" => 'SQL request error!'
            ], 422);
        }
    }
}
