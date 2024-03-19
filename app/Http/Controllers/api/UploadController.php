<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    private string $DEFAULT_IMAGE_FOLDER = 'temp';

    public function upload(Request $request)
    {
        $fileType = $request->type ? $request->type : $this->DEFAULT_IMAGE_FOLDER;
        $file = $request->file('image');
        $file->store('public/' . $fileType);

        $productPath = 'storage/' . $fileType . '/' . $file->hashName();

        return [
            'file' => $file->hashName(),
            'path' => asset($productPath),
        ];
    }

    public function delete(Request $request)
    {
        $path = $request->path;

        try {
            $fileIsDeleted = Storage::disk('local')->delete($path);

            return response()->json([
                'imageIsDeleted' => $fileIsDeleted
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => $error->getMessage()
                ]
            ], 500);
        }
    }
}
