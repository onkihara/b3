<?php

declare(strict_types=1);

namespace Onkihara\B3;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use Auth;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;


class B3FileAdapter implements FilesystemAdapter
{

    private PendingRequest $client;
    private array $config;

    function __construct(array $config)
    {
        $options = config('app.guzzle.options') ?? [];
        $this->client = Http::withOptions($options)->withToken($this->getToken($config));
        $this->config = $config;
    }


    /**
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function fileExists(string $path): bool {
        $response = $this->request('get','exists',['path' => $path]);
        $result = json_decode((string)$response->getBody(),true);
        return $result['success'];
    }

    /**
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function directoryExists(string $path): bool {
        $response = $this->request('get','isdir',['path' => $path]);
        $result = json_decode((string)$response->getBody(),true);
        return $result['success'];
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void {
        $this->request('post','upload',$path,$contents);
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void {
        $this->request('post','upload',$path,$contents);
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $path): string {
        $response = $this->request('get','retreive',['path' => $path]);
        $stream = $response->getBody(); // = GuzzleHttp\Psr7\Stream
        return $stream->getContents();
    }

    /**
     * @return resource
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function readStream(string $path) {

    }

    /**
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $path): void {}

    /**
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void {}

    /**
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void {}

    /**
     * @throws InvalidVisibilityProvided
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void {}

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes {}

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes {}

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes {}

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes {}

    /**
     * @return iterable<StorageAttributes>
     *
     * @throws FilesystemException
     */
    public function listContents(string $path, bool $deep): iterable {}

    /**
     * @throws UnableToMoveFile
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void {}

    /**
     * @throws UnableToCopyFile
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void {}


    /**
     * request
     */
    public function request($method,$type,$params = [])
    {
        try {
            $url = $this->config['server'].'/'.$this->config['version'].'/'.$type;
            $response = $this->client->{$method}($url,$params);
        } catch(FilesystemException $exception) {
           throw new B3Exception("Http Request Error");
        }
        //return $response;
        if ($response->failed()) {
            throw new B3Exception("Response failed: ".$response->status());
        }
        return $response;
    }


    /**
     * erzeugt ein JWT-Token
     */
     public static function getToken(array $config) : String
     {
        $now = time();
        $user = Auth::user() ?? null;

        $payload = array(
            "iss" => $config['iss'],
            "sub" => $config['sub'],
            "aud" => $config['aud'],
            "iat" => $now,
            "exp" => $now + $config['expiration_time_in_seconds'],
            "userid" => $user ? $user->getKey() : ''
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        return JWT::encode($payload, $config['secret'], $config['algo']);
     }
}