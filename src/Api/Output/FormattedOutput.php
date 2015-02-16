<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Output;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface FormattedOutput extends Output
{
    public function writeRaw($string);

    public function writeRawLine($string);

    public function format($string);

    public function removeFormat($string);

    public function isVerbose();

    public function isVeryVerbose();

    public function isDebug();

    public function isQuiet();
}
