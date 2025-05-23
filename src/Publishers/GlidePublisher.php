<?php

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
