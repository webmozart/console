<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Input;

/**
 * An input option which stores parameter names.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputOption extends \Symfony\Component\Console\Input\InputOption
{
    private $valueName;

    public function __construct($name, $shortcut = null, $mode = null, $description = '', $default = null, $valueName = '...')
    {
        parent::__construct($name, $shortcut, $mode, $description, $default);

        $this->valueName = $valueName;
    }

    public function getValueName()
    {
        return $this->valueName;
    }
}
