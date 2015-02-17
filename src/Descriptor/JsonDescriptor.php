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

use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Adapter\CommandAdapter;
use Webmozart\Console\Adapter\IOAdapter;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonDescriptor implements Descriptor
{
    public function describe(IO $io, $object, array $options = array())
    {
        $descriptor = new \Symfony\Component\Console\Descriptor\JsonDescriptor();

        if ($object instanceof Application) {
            $object = new ApplicationAdapter($object);
        } elseif ($object instanceof Command) {
            $object = new CommandAdapter($object, new ApplicationAdapter($object->getApplication()));
        }

        $descriptor->describe(new IOAdapter($io), $object, $options);

        return 0;
    }
}
