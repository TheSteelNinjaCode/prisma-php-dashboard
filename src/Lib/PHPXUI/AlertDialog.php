<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\MainLayout;
use PP\PHPX\PHPX;
use Lib\PHPXUI\Slot;
use PP\StateManager;
use Lib\PHPXUI\Portal;
use Lib\PHPXUI\Button;

class AlertDialog extends PHPX
{
    /** @property string|bool|null $open = null */
    public string|bool|null $open = '{false}';
    /** @property ?string $onOpenChange = null */
    public ?string $onOpenChange = null;

    public ?string $overlayClass = '';
    public mixed $children = null;
    public ?string $class = '';
    public ?string $id = null;

    private string $portalId;

    public function __construct(array $props = [])
    {
        parent::__construct($props);

        $this->portalId = uniqid('alert-dialog-portal-');
        $this->id = $this->props['id'] ?? uniqid('alert-dialog-');

        StateManager::setState('phpxui.AlertDialog', [
            'portalId'     => $this->portalId,
            'overlayClass' => $this->overlayClass,
        ]);
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'data-slot'  => 'alert-dialog',
            'data-state' => '{open ? "open" : "closed"}',
            'id'         => $this->id,
        ]);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}

            <script>
                const [openValue, setOpenValue] = pp.state(false);
                const alertdialog = document.getElementById('{$this->id}');
                const openAttr = alertdialog.getAttribute('open');
                const trigger  = alertdialog.querySelector('[data-slot="alert-dialog-trigger"]');
                const content  = alertdialog.querySelector('[data-slot="alert-dialog-content"]');
                const closes   = alertdialog.querySelectorAll('[data-slot="alert-dialog-close"]');
                const overlay  = alertdialog.querySelector('[data-slot="alert-dialog-overlay"]');
                const portal = document.getElementById('{$this->portalId}');

                const openAlert = () => {
                    alertdialog.setAttribute('data-state', 'open');
                    content?.setAttribute('data-state', 'open');
                    overlay?.setAttribute('data-state', 'open');
                    if (portal) portal.hidden = false;
                };

                const closeAlert = () => {
                    alertdialog.setAttribute('data-state', 'closed');
                    content?.setAttribute('data-state', 'closed');
                    overlay?.setAttribute('data-state', 'closed');
                    if (portal) setTimeout(() => { portal.hidden = true; }, 120);
                };

                trigger?.addEventListener('click', () => {
                    if (typeof onOpenChange !== 'undefined') {
                        onOpenChange(true);
                    } else {
                        setOpenValue(true);
                    }
                });

                closes?.forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (typeof onOpenChange !== 'undefined') {
                            onOpenChange(false);
                        } else {
                            setOpenValue(false);
                        }
                    });
                });

                overlay?.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    ev.preventDefault();
                }, true);

                document.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Escape') {
                        if (typeof onOpenChange !== 'undefined') {
                            onOpenChange(false);
                        } else {
                            setOpenValue(false);
                        }
                    }
                });

                pp.effect(() => {
                    if (openAttr !== null) {
                        if (open) openAlert(); else closeAlert();
                    } else {
                        if (openValue) openAlert(); else closeAlert();
                    }
                }, [open, openValue]);
            </script>
        </div>
        HTML;
    }
}

class AlertDialogTrigger extends PHPX
{
    /** @property bool $asChild = true|false */
    public ?bool $asChild = false;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-trigger',
            'type'      => 'button',
        ]);
        $class = $this->getMergeClasses($this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class'     => $class,
                'data-slot' => 'alert-dialog-trigger',
                'asChild'   => true,
                ...$this->attributesArray,
            ]);
            $slot->children = $this->children;
            return $slot->render();
        }

        return <<<HTML
        <button class="{$class}" {$attributes}>
            {$this->children}
        </button>
        HTML;
    }
}

class AlertDialogOverlay extends PHPX
{
    public ?string $class = '';

    public function render(): string
    {
        $props        = StateManager::getState('phpxui.AlertDialog');
        $overlayClass = $props['overlayClass'] ?? '';

        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-overlay',
        ]);

        $class = $this->getMergeClasses(
            'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/50',
            $overlayClass,
            $this->class
        );

        return <<<HTML
        <div class="{$class}" {$attributes}></div>
        HTML;
    }
}

class AlertDialogContent extends PHPX
{
    public ?string $class = '';
    /** @property ?bool $disablePortal = true|false */
    public ?bool $disablePortal = false;
    public mixed $children = null;

    public function render(): string
    {
        $props    = StateManager::getState('phpxui.AlertDialog');
        $portalId = $props['portalId'];

        $attributes = $this->getAttributes([
            'data-slot'      => 'alert-dialog-content',
            'tabindex'       => '-1',
            'data-state'     => 'closed',
            'data-portal-id' => $portalId,
        ]);

        $class = $this->getMergeClasses(
            'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed top-[50%] left-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 rounded-lg border p-6 shadow-lg duration-200 sm:max-w-lg',
            $this->class
        );

        $overlay = (new AlertDialogOverlay())->render();

        $inner = <<<HTML
        <div>
            {$overlay}
            <div class="{$class}" {$attributes}>
                {$this->children}
            </div>
        </div>
        HTML;

        $disablePortal = $this->props['disablePortal'] ?? false;
        $portalTo      = $this->props['portalTo'] ?? 'body';

        if ($disablePortal) {
            return $inner;
        }

        $portal = new Portal([
            'to'       => $portalTo,
            'children' => $inner,
            'id'       => $portalId,
            'hidden'   => 'true',
        ]);

        return $portal->render();
    }
}

class AlertDialogHeader extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-header'
        ]);
        $class = $this->getMergeClasses('flex flex-col gap-2 text-center sm:text-left', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class AlertDialogFooter extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-footer'
        ]);
        $class = $this->getMergeClasses('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class AlertDialogTitle extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-title'
        ]);
        $class = $this->getMergeClasses('text-lg leading-none font-semibold', $this->class);

        return <<<HTML
        <h3 class="{$class}" {$attributes}>
            {$this->children}
        </h3>
        HTML;
    }
}

class AlertDialogDescription extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-description'
        ]);
        $class = $this->getMergeClasses('text-muted-foreground text-sm', $this->class);

        return <<<HTML
        <p class="{$class}" {$attributes}>
            {$this->children}
        </p>
        HTML;
    }
}

class AlertDialogAction extends PHPX
{
    public ?string $class = '';
    /** @property ?string $variant = default|destructive|outline|secondary|ghost|link */
    public ?string $variant = 'default';
    /** @property ?string $size = default|sm|lg|icon */
    public ?string $size = 'default';
    /** @property ?bool $asChild = false|true */
    public ?bool $asChild = false;
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-close',
            'variant'   => $this->variant,
            'size'      => $this->size,
            'type'      => 'button',
        ]);
        $class = $this->getMergeClasses($this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class'     => $class,
                'data-slot' => 'alert-dialog-close',
                'asChild'   => true,
                ...$this->attributesArray,
            ]);
            $slot->children = $this->children;
            return $slot->render();
        }

        return <<<HTML
        <Button class="{$class}" {$attributes}>
            {$this->children}
        </Button>
        HTML;
    }
}

class AlertDialogCancel extends PHPX
{
    public ?string $class = '';
    /** @property ?string $variant = default|destructive|outline|secondary|ghost|link */
    public ?string $variant = 'outline';
    /** @property ?string $size = default|sm|lg|icon */
    public ?string $size = 'default';
    /** @property ?bool $asChild = false|true */
    public ?bool $asChild = false;
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'alert-dialog-close',
            'variant'   => $this->variant,
            'size'      => $this->size,
            'type'      => 'button',
        ]);
        $class = $this->getMergeClasses($this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class'     => $class,
                'data-slot' => 'alert-dialog-close',
                'asChild'   => true,
                ...$this->attributesArray,
            ]);
            $slot->children = $this->children;
            return $slot->render();
        }

        return <<<HTML
        <Button class="{$class}" {$attributes}>
            {$this->children}
        </Button>
        HTML;
    }
}
