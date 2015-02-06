<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Descriptor;

use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Adapter\CommandAdapter;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Command\Command;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class XmlDescriptor extends \Symfony\Component\Console\Descriptor\XmlDescriptor
{
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        if ($object instanceof Application) {
            $object = new ApplicationAdapter($object);
        } elseif ($object instanceof Command) {
            $object = new CommandAdapter($object);
        }

        parent::describe($output, $object, $options);
    }
}
