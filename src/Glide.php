<?php

/**
 * This file is part of dimtrovich/blitzphp-glide".
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\BlitzPHP\Glide;

use League\Glide\Urls\UrlBuilder;
use League\Glide\Urls\UrlBuilderFactory;

class Glide
{
    /**
     * URL builder.
     */
    protected ?UrlBuilder $urlBuilder = null;

    /**
     * Creates a formatted IMG element.
     *
     * @param array $params Image manipulation parameters.
     *
     * @return string Complete <img> tag.
     *
     * @see http://glide.thephpleague.com/1.0/api/quick-reference/
     */
    public function image(string $path, array $params = [], array $options = []): string
    {
        $output = '<img src="' . $this->url($path, $params) . '" alt="' . ($options['alt'] ?? '') . '"';

        foreach ($options as $key => $value) {
            $output .= ' ' . $key . '="' . $value . '"';
        }

        $output .= ' />';

        return $output;
    }

    /**
     * URL with query string based on resizing params.
     *
     * @param array $params Image manipulation parameters.
     *
     * @return string Image URL.
     *
     *  @see http://glide.thephpleague.com/1.0/api/quick-reference/
     */
    public function url(string $path, array $params = []): string
    {
        return trim($this->urlBuilder()->getUrl($path, $params), '/');
    }

    /**
     * Get URL builder instance.
     */
    public function urlBuilder(?UrlBuilder $urlBuilder = null): UrlBuilder
    {
        if ($urlBuilder !== null) {
            return $this->urlBuilder = $urlBuilder;
        }

        if (! isset($this->urlBuilder)) {
            $config   = config('glide', []);
            $security = $config['security'] ?? [];

            $this->urlBuilder = UrlBuilderFactory::create(
                '',
                ($security['secure'] ?? false) === true ? ($security['key'] ?: config('encryption.key')) : null,
            );
        }

        return $this->urlBuilder;
    }
}
