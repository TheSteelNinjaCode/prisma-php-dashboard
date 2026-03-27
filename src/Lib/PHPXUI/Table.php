<?php

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;

class Table extends PHPX
{
    public ?string $class = '';
    public ?string $containerClass = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table',
        ]);
        $class = $this->getMergeClasses('w-full caption-bottom text-sm', $this->class);

        $baseContainer = 'relative w-full overflow-x-auto';
        $containerClass = trim($baseContainer . ' ' . (string) ($this->containerClass ?? ''));

        return <<<HTML
        <div data-slot="table-container" class="{$containerClass}">
            <table class="{$class}" {$attributes}>
                {$this->children}
            </table>
        </div>
        HTML;
    }
}


class TableHeader extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-header',
        ]);
        $class = $this->getMergeClasses('[&_tr]:border-b', $this->class);

        return <<<HTML
        <thead class="{$class}" {$attributes}>
            {$this->children}
        </thead>
        HTML;
    }
}

class TableBody extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-body',
        ]);
        $class = $this->getMergeClasses('&_tr:last-child]:border-0', $this->class);

        return <<<HTML
        <tbody class="{$class}" {$attributes}>
            {$this->children}
        </tbody>
        HTML;
    }
}

class TableFooter extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-footer',
        ]);
        $class = $this->getMergeClasses('bg-muted/50 border-t font-medium [&>tr]:last:border-b-0', $this->class);

        return <<<HTML
        <tfoot class="{$class}" {$attributes}>
            {$this->children}
        </tfoot>
        HTML;
    }
}

class TableRow extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-row',
        ]);
        $class = $this->getMergeClasses('hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors', $this->class);

        return <<<HTML
        <tr class="{$class}" {$attributes}>
            {$this->children}
        </tr>
        HTML;
    }
}

class TableHead extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-head',
        ]);
        $class = $this->getMergeClasses('text-foreground h-10 px-2 text-left align-middle font-medium whitespace-nowrap [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]', $this->class);

        return <<<HTML
        <th class="{$class}" {$attributes}>
            {$this->children}
        </th>
        HTML;
    }
}

class TableCell extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-cell',
        ]);
        $class = $this->getMergeClasses('p-2 align-middle whitespace-nowrap [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]', $this->class);

        return <<<HTML
        <td class="{$class}" {$attributes}>
            {$this->children}
        </td>
        HTML;
    }
}

class TableCaption extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'table-caption',
        ]);
        $class = $this->getMergeClasses('text-muted-foreground mt-4 text-sm', $this->class);

        return <<<HTML
        <caption class="{$class}" {$attributes}>
            {$this->children}
        </caption>
        HTML;
    }
}
