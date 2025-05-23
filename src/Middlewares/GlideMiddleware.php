<?php

namespace Dimtrovich\BlitzPHP\Glide\Middlewares;

use BlitzPHP\Http\Request;
use BlitzPHP\Http\Response;
use BlitzPHP\Middlewares\FileViewer;
use BlitzPHP\Utilities\Iterable\Arr;
use Dimtrovich\BlitzPHP\Glide\Exception\ResponseException;
use Dimtrovich\BlitzPHP\Glide\Exception\SignatureException;
use Dimtrovich\BlitzPHP\Glide\Response\ResponseFactory;
use Exception;
use GuzzleHttp\Psr7\Stream;
use League\Glide\Server;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GlideMiddleware extends FileViewer implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    protected bool $render = false;

    /**
     * Default config.
     */
    protected array $defaultConfig = [
        'path'       => null,
        'base_url'   => null,
        'cache_time' => '+1 days',
        'security'   => [
            'secure' => false,
            'key' => null,
        ],
        'headers'        => [],
        'allowed_params' => ['w', 'h', 'fit', 'border', 'blur', 'q', 'fm', 's', 'a', 'dpr', 'bg', 'mark', 'markw', 'markh', 'markx', 'marky', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky', 'markw', 'markh', 'markpad', 'markpos', 'markalpha', 'markfit', 'markbg', 'markpos', 'markx', 'marky'],
    ];

    protected array $config = [];

    /**
     * Return response with image data.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->config = Arr::merge($this->defaultConfig, config('glide', []));
        /** @var Response */
        $response     = parent::process($request, $handler);

        if (null === $this->disk || '' === $this->path) {
            return $this->parentResponse($request, $response);
        }

        $path = $this->disk->path($this->path);

        if (! $request->hasAny($this->config('allowed_params'))) {
            return $this->parentResponse($request, $response, $path);
        }
        
        if (isset($this->config['path']) && strpos($this->path, $this->config['path'] ?? '') !== 0) {
            return $this->parentResponse($request, $response);
        }
        
        $this->checkSignature($request);
        
        $server = $this->getServer();        
        
        $modifiedTime = null;
        if ($this->config('cache_time')) {
            if (null === $return = $this->checkModified($request, $server)) {
                return $this->parentResponse($request, $response);
            }

            if ($return instanceof ResponseInterface) {
                return $return;
            }
            
            $modifiedTime = $return;
        }

        if (null === $response = $this->getResponse($request, $server)) {
            return $this->parentResponse($request, $response);
        }

        if (null !== $cacheTime = $this->config('cache_time')) {
            $response = $this->withCacheHeaders($response, $cacheTime, $modifiedTime,);
        }

        $response = $this->withCustomHeaders($response);

        return $response;
    }

    /**
     * Get glide server instance.
     */
    protected function getServer(): Server
    {
        if (empty($baseUrl = $this->config('base_url'))) {
            $config  = $this->disk->getConfig();
            $baseUrl = str_replace(config('app.base_url') . '/', '', $config['url'] ?? '');
        }

        return ServerFactory::create([
            'response'          => new ResponseFactory,
            'source'            => $this->disk->getDriver(),
            'cache'             => $this->disk->getDriver(),
            'cache_path_prefix' => '.cache',
            'base_url'          => $baseUrl,
        ]);
    }

    /**
     * Check signature token if secure URLs are enabled.
     *
     * @throws SignatureException
     */
    protected function checkSignature(ServerRequestInterface $request): void
    {
        if (! $this->config('security.secure')) {
            return;
        }

        $key = $this->config('security.key') ?: config('encryption.key');
        try {
            SignatureFactory::create($key)->validateRequest(
                $this->path,
                $request->getQueryParams(),
            );
        } catch (Exception $e) {
            throw new SignatureException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get file's modified time.
     *
     * After comparing with "If-Modified-Since" either return modified time or
     * response with 304 Not Modified status.
     *
     * @return \Psr\Http\Message\ResponseInterface|int|null
     */
    protected function checkModified(ServerRequestInterface $request, Server $server)
    {
        try {
            $modifiedTime = $server->getSource()->lastModified($server->getSourcePath($this->path));
        } catch (Exception $e) {
            return $this->handleException($e);
        }

        if ($this->isNotModified($request, $modifiedTime)) {
            $response = new Response(['status' => 304]);
            $response = $this->withCustomHeaders($response);

            return $response->withHeader('Last-Modified', (string) $modifiedTime);
        }

        return $modifiedTime;
    }

    /**
     * Get response instance which contains image to render.
     */
    protected function getResponse(ServerRequestInterface $request, Server $server): ?ResponseInterface
    {
        $queryParams   = $request->getQueryParams();
        $allowedParams = $this->config('allowed_params', []);

        if ($allowedParams !== []) {
            $queryParams = array_intersect_key($queryParams, array_flip($allowedParams));
        }

        if (($queryParams === [] || (count($queryParams) === 1 && isset($queryParams['s']))) && $this->config('original_pass_through')) {
            try {
                $response = $this->passThrough($request, $server);
            } catch (Exception $e) {
                return $this->handleException($e);
            }

            return $response;
        }

        try {
            $response = $server->getImageResponse($this->path, $queryParams);
        } catch (Exception $e) {
            return $this->handleException($e);
        }

        return $response;
    }

    /**
     * Generate response using original image.
     *
     * @throws ResponseException
     */
    protected function passThrough(ServerRequestInterface $request, Server $server): ?ResponseInterface
    {
        $source = $server->getSource();
        $path   = $server->getSourcePath($this->path);

        $resource = $source->readStream($path);
        $stream   = new Stream($resource);

        $contentType   = $source->mimeType($path);
        $contentLength = $source->fileSize($path);

        return (new Response())->withBody($stream)
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Length', (string) $contentLength);
    }

    /**
     * Compare file's modfied time with "If-Modified-Since" header.
     */
    protected function isNotModified(ServerRequestInterface $request, string|int $modifiedTime): bool
    {
        $modifiedSince = $request->getHeaderLine('If-Modified-Since');
        if (! $modifiedSince) {
            return false;
        }

        return strtotime($modifiedSince) === (int) $modifiedTime;
    }

    /**
     * Return response instance with caching headers.
     */
    protected function withCacheHeaders(ResponseInterface $response, string $cacheTime, string|int $modifiedTime): ResponseInterface {
        /** @var int $expire */
        $expire = strtotime($cacheTime);
        $maxAge = $expire - time();

        return $response
            ->withHeader('Cache-Control', 'public,max-age=' . $maxAge)
            ->withHeader('Date', gmdate(DATE_RFC7231, time()))
            ->withHeader('Last-Modified', gmdate(DATE_RFC7231, (int) $modifiedTime))
            ->withHeader('Expires', gmdate(DATE_RFC7231, $expire));
    }

    /**
     * Return response instance with headers specified in config.
     */
    protected function withCustomHeaders(ResponseInterface $response): ResponseInterface
    {
        foreach ((array) $this->config('headers') as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }

    /**
     * Handle exception.
     *
     * @throws ResponseException
     */
    protected function handleException(Exception $e): ?ResponseInterface
    {
        if (BLITZ_DEBUG) {
            throw $e;
        }

        throw new ResponseException('', 0, $e);
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        if (! str_contains($key, '.')) {
            return $this->config[$key] ?? $default;
        }

        return Arr::dataGet($this->config, $key, $default);
    }

    protected function parentResponse(Request $request, ?ResponseInterface $response, ?string $path = null): ResponseInterface
    {
        $response ??= $this->response;

        if (null === $this->disk || null === $path || ! $response instanceof Response) {
            return $response; 
        }

        if ($request->boolean('download')) {
            return $response->download($path);
        }

        return $response->file($path);
    }
}
