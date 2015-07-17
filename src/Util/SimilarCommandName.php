<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Util;

use Webmozart\Console\Api\Command\CommandCollection;

/**
 * Utility to find similar command names.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SimilarCommandName
{
    /**
     * Searches a command collection for similar names.
     *
     * @param string            $commandName The command name that was not found.
     * @param CommandCollection $commands    The available commands.
     *
     * @return string[] The names of similar commands.
     */
    public static function find($commandName, CommandCollection $commands)
    {
        $threshold = 1e3;
        $distancesByName = array();

        // Include aliases in the search
        $actualNames = $commands->getNames(true);

        foreach ($actualNames as $actualName) {
            // Get Levenshtein distance between the input and each command name
            $distance = levenshtein($commandName, $actualName);

            $isSimilar = $distance <= (strlen($commandName) / 3);
            $isSubString = false !== strpos($actualName, $commandName);

            if ($isSimilar || $isSubString) {
                $distancesByName[$actualName] = $distance;
            }
        }

        // Only keep results with a distance below the threshold
        $distancesByName = array_filter($distancesByName, function ($distance) use ($threshold) {
            return $distance < 2 * $threshold;
        });

        // Display results with shortest distance first
        asort($distancesByName);

        $suggestedNames = array_keys($distancesByName);

        return self::filterDuplicates($suggestedNames, $commands);
    }

    private static function filterDuplicates(array $names, CommandCollection $commands)
    {
        $filteredNames = array();

        foreach ($names as $nameToFilter) {
            // Check all existing names for duplicates
            foreach ($filteredNames as $filteredName) {
                // $nameToFilter is a duplicate - skip
                if ($commands->get($nameToFilter) === $commands->get($filteredName)) {
                    continue 2;
                }
            }

            $filteredNames[] = $nameToFilter;
        }

        return $filteredNames;
    }

    private function __construct()
    {
    }
}
