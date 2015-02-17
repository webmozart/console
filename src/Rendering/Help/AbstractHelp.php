<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Help;

use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Element\EmptyLine;
use Webmozart\Console\Rendering\Element\LabeledParagraph;
use Webmozart\Console\Rendering\Element\Paragraph;
use Webmozart\Console\Rendering\Layout\BlockLayout;
use Webmozart\Console\Rendering\Renderable;

/**
 * Base class for rendering help pages.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractHelp implements Renderable
{
    /**
     * Renders the usage.
     *
     * @param Canvas $canvas      The canvas.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Canvas $canvas, $indentation = 0)
    {
        $layout = new BlockLayout();

        $this->renderHelp($layout);

        $layout->render($canvas, $indentation);
    }

    /**
     * Renders the usage.
     *
     * Overwrite this class in your sub-classes to implement the actual
     * rendering.
     *
     * @param BlockLayout $layout The layout.
     */
    abstract protected function renderHelp(BlockLayout $layout);

    /**
     * Renders a list of arguments.
     *
     * @param BlockLayout $layout    The layout.
     * @param Argument[]  $arguments The arguments to render.
     */
    protected function renderArguments(BlockLayout $layout, array $arguments)
    {
        $layout->add(new Paragraph('<h>ARGUMENTS</h>'));
        $layout->beginBlock();

        foreach ($arguments as $argument) {
            $this->renderArgument($layout, $argument);
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders an argument.
     *
     * @param BlockLayout $layout   The layout.
     * @param Argument    $argument The argument to render.
     */
    protected function renderArgument(BlockLayout $layout, Argument $argument)
    {
        $description = $argument->getDescription();
        $name = '<em><'.$argument->getName().'></em>';
        $defaultValue = $argument->getDefaultValue();

        if (null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf('<h> (default: %s)</h>', $this->formatValue($defaultValue));
        }

        $layout->add(new LabeledParagraph($name, $description));
    }

    /**
     * Renders a list of options.
     *
     * @param BlockLayout $layout  The layout.
     * @param Option[]    $options The options to render.
     */
    protected function renderOptions(BlockLayout $layout, array $options)
    {
        $layout->add(new Paragraph('<h>OPTIONS</h>'));
        $layout->beginBlock();

        foreach ($options as $option) {
            $this->renderOption($layout, $option);
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders a list of global options.
     *
     * @param BlockLayout $layout  The layout.
     * @param Option[]    $options The global options to render.
     */
    protected function renderGlobalOptions(BlockLayout $layout, array $options)
    {
        $layout->add(new Paragraph('<h>GLOBAL OPTIONS</h>'));
        $layout->beginBlock();

        foreach ($options as $option) {
            $this->renderOption($layout, $option);
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders an option.
     *
     * @param BlockLayout $layout The layout.
     * @param Option      $option The option to render.
     */
    protected function renderOption(BlockLayout $layout, Option $option)
    {
        $description = $option->getDescription();
        $defaultValue = $option->getDefaultValue();

        if ($option->isLongNamePreferred()) {
            $preferredName = '--'.$option->getLongName();
            $alternativeName = $option->getShortName() ? '-'.$option->getShortName() : null;
        } else {
            $preferredName = '-'.$option->getShortName();
            $alternativeName = '--'.$option->getLongName();
        }

        $name = '<em>'.$preferredName.'</em>';

        if ($alternativeName) {
            $name .= sprintf(' (%s)', $alternativeName);
        }

        if ($option->acceptsValue() && null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf(' (default: %s)', $this->formatValue($defaultValue));
        }

        if ($option->isMultiValued()) {
            $description .= ' (multiple values allowed)';
        }

        $layout->add(new LabeledParagraph($name, $description));
    }

    /**
     * Renders the synopsis of a console command.
     *
     * @param BlockLayout $layout       The layout.
     * @param ArgsFormat  $argsFormat   The console arguments to render.
     * @param string      $appName      The name of the application binary.
     * @param string      $prefix       The prefix to insert.
     * @param bool        $lastOptional Set to `true` if the last command of the
     *                                  console arguments is optional. This
     *                                  command will be enclosed in brackets in
     *                                  the output.
     */
    protected function renderSynopsis(BlockLayout $layout, ArgsFormat $argsFormat, $appName, $prefix = '', $lastOptional = false)
    {
        $nameParts = array();
        $argumentParts = array();

        $nameParts[] = '<tt>'.($appName ?: 'console').'</tt>';

        foreach ($argsFormat->getCommandNames() as $commandName) {
            $nameParts[] = '<tt>'.$commandName->toString().'</tt>';
        }

        foreach ($argsFormat->getCommandOptions() as $commandOption) {
            $nameParts[] = $commandOption->isLongNamePreferred()
                ? '--'.$commandOption->getLongName()
                : '-'.$commandOption->getShortName();
        }

        if ($lastOptional) {
            $lastIndex = count($nameParts) - 1;
            $nameParts[$lastIndex] = '['.$nameParts[$lastIndex].']';
        }

        foreach ($argsFormat->getOptions(false) as $option) {
            // \xC2\xA0 is a non-breaking space
            if ($option->isValueRequired()) {
                $format = "%s\xC2\xA0<%s>";
            } elseif ($option->isValueOptional()) {
                $format = "%s\xC2\xA0[<%s>]";
            } else {
                $format = '%s';
            }

            $optionName = $option->isLongNamePreferred()
                ? '--'.$option->getLongName()
                : '-'.$option->getShortName();

            $argumentParts[] = sprintf('['.$format.']', $optionName, $option->getValueName());
        }

        foreach ($argsFormat->getArguments() as $argument) {
            $argName = $argument->getName();

            $argumentParts[] = sprintf(
                $argument->isRequired() ? '<%s>' : '[<%s>]',
                $argName.($argument->isMultiValued() ? '1' : '')
            );

            if ($argument->isMultiValued()) {
                $argumentParts[] = sprintf('... [<%sN>]', $argName);
            }
        }

        $argsOpts = implode(' ', $argumentParts);
        $name = implode(' ', $nameParts);

        $layout->add(new LabeledParagraph($prefix.$name, $argsOpts, 1, false));
    }

    /**
     * Formats the default value of an argument or an option.
     *
     * @param mixed $value The default value to format.
     *
     * @return string The formatted value.
     */
    protected function formatValue($value)
    {
        if (PHP_VERSION_ID < 50400) {
            return str_replace('\/', '/', json_encode($value));
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
