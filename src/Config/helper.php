<?php

use Dimtrovich\BlitzPHP\Glide\Glide;

if (! function_exists('glide')) {
    /**
     * @return Glide|string
     */
    function glide(?string $path = null, ?int $w = null, ?int $h = null, array $params = [])
    {
        $glide = service(Glide::class);

        if (! empty($path)) {
            return $glide->url($path, ['w' => $w, 'h' => $h] + $params);
        }

        return $glide;
    }
}
