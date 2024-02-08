<?php

namespace App\Http\Controllers;

use App\Models\Accesses;
use App\Models\Users;
use App\Models\Files;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function addAccess(Request $request, $file_id): JsonResponse
    {
        $token = $request->bearerToken();
        $current_user = Users::where("token", $token)->first()->id;
        $file_owner = Files::where("file_id", $file_id)->first()?->user_id;

        if ($current_user == $file_owner) {
            $data = $request->all();
            $email = $data["email"];

            $user = Users::where("email", $email)->first();
            if ($user) {
                Accesses::create([
                    'user_id' => $user->id,
                    'file_id' => $file_id,
                    'access_type' => "co-author",
                ]);
                $accesses = Accesses::where("file_id", $file_id)->get();
                $users_with_access = [];
                foreach ($accesses as $access) {
                    $user = Users::where("id", $access["user_id"])->first();
                    $users_with_access[] = [
                        "fullname" => $user->first_name . " " . $user->last_name,
                        "email" => $user->email,
                        "type" => $access["access_type"],
                        "code" => 200,
                    ];
                }
                return new JsonResponse($users_with_access);
            } else {
                return new JsonResponse([
                    "message" => "Not found",
                    "code" => 404,
                ], 404);
            }
        } else {
            if ($file_owner) {
                return new JsonResponse([
                    "message" => "Forbidden for you",
                ], 403);
            } else {
                return new JsonResponse([
                    "message" => "Not found",
                    "code" => 404,
                ], 404);
            }
        }
    }

    public function removeAccess(Request $request, $file_id)
    {
        $token = $request->bearerToken();
        $current_user = Users::where("token", $token)->first()->id;
        $file_owner = Files::where("file_id", $file_id)->first()?->user_id;

        if ($current_user == $file_owner) {
            $data = $request->all();
            $email = $data["email"];

            $user = Users::where("email", $email)->first();
            if ($user && $user->id != $file_owner) {
                $coauthor = Accesses::where("user_id", $user->id)
                    ->where("file_id", $file_id)
                    ->exists();
                if ($coauthor) {
                    Accesses::where("user_id", $user->id)
                        ->where("file_id", $file_id)
                        ->delete();
                } else {
                    return new JsonResponse([
                        "message" => "Not found",
                        "code" => 404,
                    ], 404);
                }

                $accesses = Accesses::where("file_id", $file_id)->get();
                $users_with_access = [];
                foreach ($accesses as $access) {
                    $user = Users::where("id", $access["user_id"])->first();
                    $users_with_access[] = [
                        "fullname" => $user->first_name . " " . $user->last_name,
                        "email" => $user->email,
                        "type" => $access["access_type"],
                        "code" => 200,
                    ];
                }
                return new JsonResponse($users_with_access);
            } else {
                return new JsonResponse([
                    "message" => "Not found",
                    "code" => 404,
                ], 404);
            }
        } else {
            if ($file_owner) {
                return new JsonResponse([
                    "message" => "Forbidden for you",
                ], 403);
            } else {
                return new JsonResponse([
                    "message" => "Not found",
                    "code" => 404,
                ], 404);
            }
        }
    }
}
