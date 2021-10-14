<?php


namespace App\Http\Controllers;

use App\Services\S3Service;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    public function getImage(string $image_name, S3Service $s3Service) {
        $path = $s3Service->getOrCreate($image_name);

        return (new Response(file_get_contents($path), 200))
            ->header('Content-Type', 'image/png');
    }
}
