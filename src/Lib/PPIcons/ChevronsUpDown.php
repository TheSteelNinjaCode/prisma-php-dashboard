<?php

namespace Lib\PPIcons;

use PP\PHPX\PHPX;

class ChevronsUpDown extends PHPX
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
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {$attributes}><path d="m7 15 5 5 5-5"></path><path d="m7 9 5-5 5 5"></path></svg>
        HTML;
    }
}
