<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'email' => 'required|string|email|unique:users,email|min:1|max:40',
            'password' => 'required|string|min:1|max:30',
        ]);
        if($validate->fails()){
            return response()->json([
                    "data"=>$validate->errors(),
                    "message"=> 'Error registration!'
            ], 422);
        }else{
            $user = new User;
            $user->name = $request->post('name');
            $user->email = $request->post('email');
            $user->password = Hash::make($request->post('password'));
            $user->api_token = Str::random(30);
            $user->refresh_token = Str::random(50);

            if($user->save()){
                return response()->json([
                    "data"=>null,
                    "message"=> 'Registered!'
                ], 201);
            }else{
                return response()->json([
                    "data"=>null,
                    "message"=> 'Error registration!'
                ], 400);
            }
        }
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email|min:1|max:40',
            'password' => 'required|string|min:1|max:30',
        ]);

        if($validate->fails()){
            return response()->json($validate->errors(), 401);
        }else{
           $user = User::where(
               'email','=',$request->input('email'))
                ->first();
            if(!$user || Hash::check($user->password, $request->input('password'))){
                return response()->json([
                    "data"=> null,
                    "message"=> 'Error logged!'
                ], 401);
            }else{
                return response()->json([
                    "data"=>[
                        'access_token'=>$user->api_token,
                        'refresh_token'=>$user->refresh_token,
                    ],
                    "message"=> 'Logged!'
                ], 200);

            }
        }
    }

    public function refreshAccessToken(Request $request){
        $validate = Validator::make($request->all(), [
            'refresh_token' => 'required|string|min:1'
        ]);
        if($validate->fails()){
            return response()->json([
                "data"=>$validate->errors(),
                "message"=> 'Error refreshed!'
            ], 401);
        }else{
            $auth_user = Auth::user();
            if($auth_user->refresh_token === $request->input('refresh_token')){
                $auth_user->api_token = Str::random(30);
                $auth_user->save();
                return response()->json([
                    "data"=>[
                        'access_token'=> $auth_user->api_token,
                    ],
                    "message"=> 'Refreshed!'
                ], 201);
            }else{
                return response()->json([
                    "data"=> [
                        'refresh_token' => 'Invalid refresh token!'
                    ],
                    "message"=> 'Error Refreshed!'
                ], 401);
            }
        }
    }

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
                ], 401);
            }
            $task = User::with($request->input('withs'))
                ->where('id', $id)->first();
            if ($task == false) {
                return response()->json([
                    "data" => null,
                    "message" => 'User not found!'
                ], 404);
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
                    "message" => 'Error get users!'
                ], 401);
            }

            $page = $request->input('page') - 1;
            $per_page = $request->input('per_page');
            if ($request->input('withs') !== null){
                $tasks = User::with($request->input('withs'))
                    ->where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }else{
                $tasks = User::where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }


            return response()->json([
                "data" => [
                    "items" => $tasks->sortBy($request->input("order"))
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
}
