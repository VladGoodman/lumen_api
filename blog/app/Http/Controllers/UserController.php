<?php

namespace App\Http\Controllers;

use App\Http\Helper\ResponseHelper;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Namshi\JOSE\JWT;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'email' => 'required|string|email|unique:users,email|min:1|max:40',
            'password' => 'required|string|min:1|max:30',
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form("Error register!", 422, $validate->errors());
        }
        try {
            $new_user = User::create([
                'name' => $request->post('name'),
                'password' => Hash::make($request->post('password')),
                'email' => $request->post('email'),
                'api_token' => Str::random(20),
                'refresh_token' => Str::random(30)
            ]);
            if ($new_user) {
                return response()->json([
                    "data" => null,
                    "message" => 'Registered!'
                ], 201);
            } else {
                return ResponseHelper::form("Error registration!", 400);
            }
        } catch (\Exception $e) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email|min:1|max:40',
            'password' => 'required|string|min:1|max:30',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 401);
        }
        try {
            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return ResponseHelper::form(['error' => 'Unauthorized'], 401);
            }
            $payload = JWTFactory::sub(Auth::id())
                ->exp(time() + (1))
                ->refreshesToken($token)->make();

            $refresh_token = JWTAuth::encode($payload);
            return ResponseHelper::form('Logged!',
                200,
                [
                    "access_token" => $token,
                    "refresh_token" => $refresh_token->get()
                ]);

        } catch (\Exception $e) {
            return ResponseHelper::form("SQL request error!", 401);
        }

    }

    public function refreshAccessToken(Request $request)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser(JWTAuth::decode($token)['refreshesToken']);
        if (Auth::user() === $user) {
            return ResponseHelper::form('Success!',
                200,
                [
                    "access_token" => \auth()->refresh(),
                ]);
        } else {
            return ResponseHelper::form('Error auth!',
                422,
                [
                    "access_token" => \auth()->refresh(),
                ]);
        }
    }

    public function getItem(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'with' => 'array',
            'with.*' => 'string'
        ]);
        if ($validate->fails()) {
            return ResponseHelper::form("Error logged!", 422, $validate->errors());
        }
        try {
            $task = User::with($request->input('withs'))
                ->where('id', $id)->first();
            if (!$task) {
                return ResponseHelper::form("User not found!", 404);
            }
            return ResponseHelper::form(
                "Received!",
                201,
                [
                    "attributes" => $task
                ]);
        } catch (\Exception $errors) {
            return ResponseHelper::form("SQL request error!", 401);
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
                return ResponseHelper::form("Error logged!", 422, $validate->errors());
            }

            $page = $request->input('page') - 1;
            $per_page = $request->input('per_page');
            if ($request->input('withs') !== null) {
                $tasks = User::with($request->input('withs'))
                    ->where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            } else {
                $tasks = User::where($request->input('filter'))
                    ->skip($page * $per_page)
                    ->take($per_page)
                    ->get();
            }
            return ResponseHelper::form(
                "Received!",
                201,
                ["items" => $tasks->sortBy($request->input("order"))]);


        } catch (\Exception $errors) {
            return ResponseHelper::form("SQL request error!", 401);
        }
    }


    protected function respondWithToken($token)
    {
        return ResponseHelper::form("Get token!", 200,
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
    }
}
