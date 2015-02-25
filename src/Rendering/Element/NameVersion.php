<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Element;

use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Renderable;

/**
 * Renders the name and version of an application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NameVersion implements Renderable
{
    private $config;

    /**
     * Creates the renderer.
     *
     * @param ApplicationConfig $config The application configuration.
     */
    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Renders the name and version.
     *
     * @param Canvas $canvas      The canvas to render the object on.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Canvas $canvas, $indentation = 0)
    {
        if ($this->config->getDisplayName() && $this->config->getVersion()) {
            $paragraph = new Paragraph("{$this->config->getDisplayName()} version <em>{$this->config->getVersion()}</em>");
        } elseif ($this->config->getDisplayName()) {
            $paragraph = new Paragraph("{$this->config->getDisplayName()}");
        } else {
            $paragraph = new Paragraph("Console Tool");
        }

        $paragraph->render($canvas, $indentation);
    }
}
