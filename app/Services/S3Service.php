<?php

namespace App\Services;

use AWS;
use Aws\S3\S3Client;


class S3Service
{
    /** @var S3Client */
    private $client;

    private const S3_DO_BUCKET = 'nastavnichestvo';

    public function __construct()
    {
        $this->client = AWS::createClient('s3', ['endpoint' => 'https://ams3.digitaloceanspaces.com']);
    }

    public function checkFileExists(string $file_name): bool {
        return $this->client->doesObjectExist(self::S3_DO_BUCKET, $this->getKey($file_name));
    }

    public function initFile(string $file_name) {
        $response = $this->client->putObject([
            'Bucket'        => self::S3_DO_BUCKET,
            'Key'           => $this->getKey($file_name),
            'Body'          => file_get_contents("https://robohash.org/{$file_name}.png?size=96x96"),
            'ContentType'   => "image/png",
            'ACL'           => 'public-read',
        ]);

        return $response->toArray()['ObjectURL'] ?? '';
    }

    private function getKey(string $file_name): string {
        return "robots/$file_name.png";
    }

    public function getBucketObject(string $file_name) {
        return $this->client->getObject([
            'Bucket'        => self::S3_DO_BUCKET,
            'Key'           => $this->getKey($file_name)
        ])->toArray();
    }

    public function getImagePath(string $file_name): string {
        return $this->getBucketObject($file_name)['@metadata']['effectiveUri'] ?? '';
    }

    public function getOrCreate(string $file_name): string {
        return $this->checkFileExists($file_name) ? $this->getImagePath($file_name) : $this->initFile($file_name);
    }
}
