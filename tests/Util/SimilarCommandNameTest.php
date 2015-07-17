<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Util;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Util\SimilarCommandName;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SimilarCommandNameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInputOutput
     */
    public function testFindSimilarNames($input, array $suggestions)
    {
        $commands = new CommandCollection(array(
            new Command(
                CommandConfig::create('package')->addAlias('package-alias')
            ),
            new Command(
                CommandConfig::create('pack')->addAlias('pack-alias')
            ),
            new Command(CommandConfig::create('pack')),
        ));

        $this->assertSame($suggestions, SimilarCommandName::find($input, $commands));
    }

    public function getInputOutput()
    {
        return array(
            array('pac', array('pack', 'package')),
            array('pack', array('pack', 'package')),
            array('pack-', array('pack')),
            array('pack-a', array('pack')),
            array('pack-al', array('pack-alias')),
            array('pack-ali', array('pack-alias')),
            array('pack-alia', array('pack-alias')),
            array('pack-alias', array('pack-alias', 'package-alias')),
            array('packa', array('pack', 'package')),
            array('packag', array('package', 'pack')),
            array('package', array('package')),
            array('package-', array('package')),
            array('package-a', array('package')),
            array('package-al', defined('HHVM_VERSION') ? array('package') : array('package-alias')),
            array('package-ali', array('package-alias')),
            array('package-alia', array('package-alias', 'pack-alias')),
            array('package-alias', array('package-alias', 'pack-alias')),
        );
    }
}
