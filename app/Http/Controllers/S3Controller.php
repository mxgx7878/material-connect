<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Aws\S3\S3Client;

class S3Controller extends Controller
{
    /**
     * Generate a presigned PUT URL so frontend can upload directly to S3.
     * All uploads are scoped under material-connect/{folder}/...
     */
    public function generatePresignedUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename'     => 'required|string|max:255',
            'content_type' => 'required|string|max:255',
            'folder'       => 'nullable|string|max:100', // e.g. products/photos
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $folder    = trim($request->input('folder', 'misc'), '/');
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $safeName  = Str::slug(pathinfo($request->filename, PATHINFO_FILENAME));
        $unique    = $safeName . '-' . Str::random(10) . ($extension ? '.' . $extension : '');

        $key    = "material-connect/{$folder}/{$unique}";
        $bucket = env('AWS_BUCKET');
        $region = env('AWS_DEFAULT_REGION');

        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $cmd = $s3->getCommand('PutObject', [
            'Bucket'      => $bucket,
            'Key'         => $key,
            'ContentType' => $request->content_type,
        ]);

        $presignedUrl = (string) $s3->createPresignedRequest($cmd, '+5 minutes')->getUri();
        $publicUrl    = "https://{$bucket}.s3.{$region}.amazonaws.com/{$key}";

        return response()->json([
            'presigned_url' => $presignedUrl,
            'public_url'    => $publicUrl,
            'key'           => $key,
            'expires_in'    => 300,
        ], 200);
    }
}