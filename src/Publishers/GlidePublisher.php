<?php

/**
 * This file is part of dimtrovich/blitzphp-glide".
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dimtrovich\BlitzPHP\Glide\Publishers;

use BlitzPHP\Publisher\Publisher;

class GlidePublisher extends Publisher
{
    /**
     * {@inheritDoc}
     */
    protected string $source = __DIR__ . '/../Config/';

    /**
     * {@inheritDoc}
     */
    protected string $destination = CONFIG_PATH;

    /**
     * {@inheritDoc}
     */
    public function publish(): bool
    {
        return $this->addPaths(['glide.php'])->merge(false);
    }
}
