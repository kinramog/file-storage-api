<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Users;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "email" => "required|unique:users",
            "password" => "required|min:3|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{3,12}/",
            "first_name" => "required|min:2",
            "last_name" => "required",
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                "success" => false,
                "code" => 422,
                "message" => $validator->errors(),
            ], 422);
        } else {
            $data["token"] = Str::random(40);
            Users::create($data);
            return new JsonResponse([
                "success" => true,
                "code" => 201,
                "message" => "Success",
                "token" => $data["token"],
            ], 201);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "email" => "required",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                "success" => false,
                "code" => 422,
                "message" => $validator->errors(),
            ], 422);
        } else {
            $token = $request->bearerToken();
            $user = Users::where('token', $token)
                ->where('email', $data["email"])
                ->where('password', $data["password"])
                ->first();

            if ($user) {
                return new JsonResponse([
                    "success" => true,
                    "code" => 200,
                    "message" => "Success",
                    "token" => $token,
                ], 200);
            } else {
                return new JsonResponse([
                    "success" => false,
                    "code" => 401,
                    "message" => "Authorization failed",
                ], 401);
            }
        }
    }
}
