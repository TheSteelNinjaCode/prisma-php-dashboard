<?php

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;
use Lib\PHPXUI\Slot;

class Label extends PHPX
{
    public ?string $class = '';
    
    /** @property ?bool $asChild = false|true */
    public ?bool $asChild = false;
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'label',
        ]);
        $class = $this->getMergeClasses('flex items-center gap-2 text-sm leading-none font-medium select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50', $this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class' => $class,
                'asChild' => true,
                ...$this->attributesArray,
            ]);
            $slot->children = $this->children;
            return $slot->render();
        }

        return <<<HTML
        <label class="{$class}" {$attributes}>
            {$this->children}
        </label>
        HTML;
    }
}
