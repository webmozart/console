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

use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Style\StyleSet;

/**
 * The console output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Output extends OutputInterface
{
    /**
     * Sets the dimensions (width and height) of the output.
     *
     * @param Dimensions $dimensions The output dimensions.
     */
    public function setDimensions(Dimensions $dimensions);

    /**
     * Returns the dimensions (width and height) of the output.
     *
     * @return Dimensions The output dimensions.
     */
    public function getDimensions();

    /**
     * Sets the style set used by the output.
     *
     * @param StyleSet $styleSet The style set.
     */
    public function setStyleSet(StyleSet $styleSet);

    /**
     * Returns the style set used by the output.
     *
     * @return StyleSet The style set.
     */
    public function getStyleSet();
}
