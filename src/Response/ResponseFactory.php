<?php

/**
 * This file is part of dimtrovich/blitzphp-glide".
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\BlitzPHP\Glide\Response;

use BlitzPHP\Http\Response;
use Dimtrovich\BlitzPHP\Glide\Exception\ResponseException;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Glide\Responses\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Create response.
     */
    public function create(FilesystemOperator $cache, string $path): ResponseInterface
    {
        try {
            $resource = $cache->readStream($path);
        } catch (FilesystemException $e) {
            throw new ResponseException(null, null, $e);
        }

        $stream = new Stream($resource);

        $contentType   = $cache->mimeType($path);
        $contentLength = $cache->fileSize($path);

        return (new Response())->withBody($stream)
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Length', (string) $contentLength);
    }
}
