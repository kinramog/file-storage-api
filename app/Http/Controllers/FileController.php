<?php

namespace App\Http\Controllers;

use App\Models\Files;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                while (file_exists(public_path("uploads/{$user_id}/{$file_name}"))) {
                    $count++;
                    $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . "({$count}).{$extension}";
                }
                $path = $file->move(public_path("uploads/$user_id"), $file_name);
                // $files_db = Files::create([
                //     "file_id" => $file_id,
                //     "user_id" => $user_id,
                //     "name" => $file_name,
                //     "path" => $path,
                // ]);
                $uploadedFiles[] = [
                    "success" => true,
                    "code" => 200,
                    "message" => "success",
                    "name" => $file_name,
                    "url" => url("/api/files/$file_id"),
                    "file_id" => $file_id,
                ];
            }
        }

        return new JsonResponse($uploadedFiles);
    }

    public function getFile(Request $request, $file_id) {
        $token = $request->bearerToken();
        $user_id = Users::where('token', $token)->first()->id;
        echo $file_id;
        
    }
}
