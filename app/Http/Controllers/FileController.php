<?php

namespace App\Http\Controllers;

use App\Models\Accesses;
use App\Models\Files;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        $user_id = Users::where('token', $token)->first()->id;

        $files = $request->file('files');

        $uploadedFiles = [];
        foreach ($files as $file) {
            $errors = [];
            $allowedExtensions = ["doc", "pdf", "docx", "zip", "jpeg", "jpg", "png"];
            if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                $errors[] = "Недопустимый тип файла. Разрешенные типы " . implode(", ", $allowedExtensions);
            }
            if ($file->getSize() / 1024 / 1024 > 2) {
                $errors[] = "Недопустимый размер файла. Максимальный размер - 2 Мб";
            }
            if ($errors) {
                $uploadedFiles[] = [
                    "success" => false,
                    "message" => $errors,
                    "name" => $file->getClientOriginalName(),
                ];
            } else {
                $file_id = Str::random(10);
                $extension = $file->getClientOriginalExtension();
                $file_name = $file->getClientOriginalName();
                $count = 0;
                while (file_exists(public_path("uploads/{$file_name}"))) {
                    $count++;
                    $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . "({$count}).{$extension}";
                }
                $path = $file->move(public_path("uploads/"), $file_name);
                $files_db = Files::create([
                    "file_id" => $file_id,
                    "user_id" => $user_id,
                    "name" => $file_name,
                    "path" => $path,
                ]);
                $uploadedFiles[] = [
                    "success" => true,
                    "code" => 200,
                    "message" => "success",
                    "name" => $file_name,
                    "url" => url("/api/files/$file_id"),
                    "file_id" => $file_id,
                ];
                Accesses::create([
                    'user_id' => $user_id,
                    'file_id' => $file_id,
                    'access_type' => "author",
                ]);
            }
        }
        return new JsonResponse($uploadedFiles, 200);
    }

    public function downloadFile(Request $request, $file_id)
    {
        // $token = $request->bearerToken();
        $file = Files::where("file_id", $file_id)->first();
        $path = public_path("uploads/" . $file->name);
        return response()->download($path);
    }

    public function getFiles(Request $request)
    {
        $token = $request->bearerToken();
        $current_user = Users::where("token", $token)->first()->id;
        $files = Files::where("user_id", $current_user)->get();
        $user_files = [];
        foreach ($files as $file) {
            $accesses = Accesses::where("file_id", $file->file_id)->get();
            $users_with_access = [];
            foreach ($accesses as $access) {
                $user = Users::where("id", $access["user_id"])->first();
                $users_with_access[] = [
                    "fullname" => $user->first_name . " " . $user->last_name,
                    "email" => $user->email,
                    "type" => $access["access_type"],
                ];
            }

            $user_files[] = [
                "file_id" => $file->file_id,
                "name" => $file->name,
                "code" => 200,
                "url" => url("/api/files/$file->file_id"),
                "accesses" => $users_with_access,
            ];
        }
        return new JsonResponse($user_files);
    }

    public function sharedFiles(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        $current_user = Users::where("token", $token)->first()->id;
        $accesses = Accesses::where("user_id", $current_user)->get();
        $shared_files = [];
        foreach ($accesses as $access) {
            $file_name = Files::where("file_id", $access->file_id)->first()->name;
            if ($access->access_type != "author") {
                $shared_files[] = [
                    "file_id" => $access->file_id,
                    "code" => 200,
                    "name" => $file_name,
                    "url" => url("/api/files/$access->file_id"),
                ];
            }
        }
        return new JsonResponse($shared_files);
    }

    public function deleteFile(Request $request, $file_id)
    {
        $token = $request->bearerToken();
        $current_user = Users::where("token", $token)->first()->id;
        $file_owner = Accesses::where("file_id", $file_id)
            ->where("access_type", "author")
            ->first()
            ?->user_id;
        if ($current_user == $file_owner) {
            $file_name = Files::where("file_id", $file_id)->first()?->name;

            if (file_exists(public_path("uploads/$file_name")) && $file_name) {
                unlink(public_path("uploads/$file_name"));
                Files::where("file_id", $file_id)->delete();
                Accesses::where("file_id", $file_id)->delete();

                return new JsonResponse([
                    "success" => true,
                    "code" => 200,
                    "message" => "File deleted",
                ]);
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

    public function renameFile(Request $request, $file_id)
    {
        $token = $request->bearerToken();
        $data = $request->all();
        $validator = Validator::make($data, [
            "name" => "required",
        ]);
        if ($validator->fails()) {
            return new JsonResponse([
                "success" => false,
                "code" => 422,
                "message" => $validator->errors(),
            ], 422);
        }

        $new_filename = $data["name"];
        $current_user = Users::where("token", $token)->first()->id;
        $file_owner = Accesses::where("file_id", $file_id)
            ->where("access_type", "author")
            ->first()
            ?->user_id;
        if ($current_user == $file_owner) {
            $file_name = Files::where("file_id", $file_id)->first()?->name;
            $path = Files::where("file_id", $file_id)->first()?->path;
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if (file_exists(public_path("uploads/$file_name")) && $file_name) {
                rename(
                    public_path("uploads/$file_name"),
                    public_path("uploads/$new_filename.$extension")
                );
                Files::where("file_id", $file_id)->update(["name" => "$new_filename.$extension"]);

                return new JsonResponse([
                    "success" => true,
                    "code" => 200,
                    "message" => "Renamed",
                ]);
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
