<?php

namespace Lib\PPIcons;

use PP\PHPX\PHPX;

class ArrowLeft extends PHPX
{
    public ?string $class = '';

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'class' => $class,
        ]);

        return <<<HTML
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {$attributes}><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        HTML;
    }
}
