<?php

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;
use PP\PHPX\TwMerge;
use DOMElement;
use PP\PHPX\TemplateCompiler;

class Slot extends PHPX
{
    public ?string $class = '';

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    private function processChildNodes(string $children, string $class): string
    {
        $dom = TemplateCompiler::convertToXml($children);

        $root = $dom->documentElement;
        $updatedContent = [];
        $firstElementFound = false;

        foreach ($root->childNodes as $node) {
            if (!$firstElementFound && $node instanceof DOMElement) {
                $firstElementFound = true;

                $existingClass = $node->getAttribute("class");
                $mergeClass = TwMerge::merge($class, $existingClass);
                $node->setAttribute("class", $mergeClass);

                foreach ($this->props as $key => $value) {
                    if ($key !== 'class' && $key !== 'asChild' && $key !== 'children' && is_string($value)) {
                        $node->setAttribute($key, $value);
                    }
                }
            }
            $updatedContent[] = $dom->saveXML($node);
        }

        return implode('', $updatedContent);
    }

    public function render(): string
    {
        $slotClass = $this->props['class'] ?? '';
        $attributes = $this->getAttributes();
        $class = $this->getMergeClasses($slotClass, $this->class);
        $asChild = $this->props['asChild'] ?? false;

        if ($asChild) {
            return $this->processChildNodes($this->children, $class);
        }

        return <<<HTML
        <div class="{$class}" {$attributes}>{$this->children}</div>
        HTML;
    }
}
