<?php

/**
 * This file is part of dimtrovich/blitzphp-glide".
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
