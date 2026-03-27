<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\MainLayout;
use PP\PHPX\PHPX;
use Lib\PHPXUI\Slot;
use Lib\PPIcons\X;
use PP\StateManager;
use Lib\PHPXUI\Portal;

class Dialog extends PHPX
{
    /** @property string|bool|null $open = null */
    public string|bool|null $open = '{false}';
    /** @property ?string $onOpenChange = null */
    public ?string $onOpenChange = null;
    /** @property ?bool $closeOnOverlayClick = true|false */
    public ?bool $closeOnOverlayClick = true;
    /** @property ?bool $resetOnOpen = true|false */
    public ?bool $resetOnOpen = false;
    public ?string $overlayClass = '';
    public ?string $class = '';
    public ?string $id = null;

    private string $portalId;
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);

        $this->portalId = uniqid('dialog-portal-');
        $this->id = $this->props['id'] ?? uniqid('dialog-');
        StateManager::setState("phpxui.Dialog", [
            'portalId' => $this->portalId,
            'overlayClass' => $this->overlayClass,
        ]);
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'data-slot'       => 'dialog',
            'data-state'       => '{open ? "open" : "closed"}',
            'id'              => $this->id,
            'class'          => $class,
        ]);

        return <<<HTML
        <div {$attributes}>
            {$this->children}

            <script>
                const [openValue, setOpenValue] = pp.state(false);
                const dialog = document.getElementById('{$this->id}');
                const dialogOpenAttribute = dialog.getAttribute('open');
                const trigger = dialog.querySelector('[data-slot="dialog-trigger"]');
                const content = dialog.querySelector('[data-slot="dialog-content"]');
                const close = dialog.querySelectorAll('[data-slot="dialog-close"]');
                const overlay = dialog.querySelector('[data-slot="dialog-overlay"]');
                const closeOnOverlayClick = dialog.hasAttribute('closeOnOverlayClick') === true;
                const portal = document.getElementById('{$this->portalId}');
                const resetOnOpen = dialog.hasAttribute('resetOnOpen');

                (function initScrollLock(){
                    if (window.__ppScrollLock) return;
                    window.__ppScrollLock = {
                        locks: 0,
                        scrollTop: 0,
                        initialPadRight: '',
                    };
                })();

                function getScrollbarWidth() {
                    const docEl = document.documentElement;
                    return window.innerWidth - docEl.clientWidth;
                }

                function lockScroll() {
                    const state = window.__ppScrollLock;
                    if (state.locks === 0) {
                        state.scrollTop = window.scrollY || document.documentElement.scrollTop || 0;

                        const docEl = document.documentElement;
                        state.initialPadRight = docEl.style.paddingRight || '';
                        const sw = getScrollbarWidth();
                        if (sw > 0) docEl.style.paddingRight = `\${sw}px`;

                        docEl.style.overflow = 'hidden';
                        document.body.style.position = 'fixed';
                        document.body.style.top = `-\${state.scrollTop}px`;
                        document.body.style.left = '0';
                        document.body.style.right = '0';
                        document.body.style.width = '100%';
                    }
                    state.locks++;
                }

                function unlockScroll() {
                    const state = window.__ppScrollLock;
                    if (!state || state.locks === 0) return;
                    state.locks--;
                    if (state.locks === 0) {
                        const docEl = document.documentElement;
                        docEl.style.overflow = '';
                        docEl.style.paddingRight = state.initialPadRight || '';
                        document.body.style.position = '';
                        document.body.style.top = '';
                        document.body.style.left = '';
                        document.body.style.right = '';
                        document.body.style.width = '';
                        window.scrollTo(0, state.scrollTop || 0);
                    }
                }

                function focusFirstField(root) {
                    if (!root) return;
                    const first = root.querySelector(
                        '[autofocus], input:not([type="hidden"]):not([disabled]):not([tabindex="-1"]), textarea:not([disabled]):not([tabindex="-1"]), select:not([disabled]):not([tabindex="-1"]), [contenteditable=""], [contenteditable="true"], [tabindex]:not([tabindex^="-"])'
                    );
                    if (first) { first.focus({ preventScroll: true }); first.select?.(); }
                }

                function resetFields(root) {
                    if (!root) return;

                    const bubble = (el) => {
                        el.dispatchEvent(new Event('input',  { bubbles:true }));
                        el.dispatchEvent(new Event('change', { bubbles:true }));
                    };

                    const form = root.querySelector('form');
                    if (form) {
                        form.reset();
                        form.querySelectorAll('input, textarea, select').forEach(bubble);
                        return;
                    }

                    root.querySelectorAll('input:not([type="hidden"])').forEach(i => {
                        if (i.disabled) return;
                        if (i.type === 'checkbox' || i.type === 'radio') i.checked = i.defaultChecked;
                        else i.value = i.defaultValue;
                        bubble(i);
                    });

                    root.querySelectorAll('textarea').forEach(t => {
                        if (t.disabled) return;
                        t.value = t.defaultValue;
                        bubble(t);
                    });

                    root.querySelectorAll('select').forEach(s => {
                        if (s.disabled) return;
                        s.querySelectorAll('option').forEach(o => o.selected = o.defaultSelected);
                        bubble(s);
                    });
                }

                const openDialog = () => {
                    dialog.setAttribute('data-state', 'open');
                    content?.setAttribute('data-state', 'open');
                    if (overlay) {
                        overlay.setAttribute('data-state', 'open');
                    }
                    if (portal) {
                        portal.hidden = false;
                    }

                    lockScroll();

                    if (resetOnOpen) resetFields(content);
                    requestAnimationFrame(() => focusFirstField(content));
                };

                const closeDialog = () => {
                    dialog.setAttribute('data-state', 'closed');
                    content?.setAttribute('data-state', 'closed');
                    if (overlay) {
                        overlay.setAttribute('data-state', 'closed');
                    }
                    if (portal) {
                        setTimeout(() => {
                            portal.hidden = true;
                        }, 120);
                    }

                    unlockScroll();
                };

                trigger?.addEventListener('click', () => {
                    if (typeof onOpenChange !== 'undefined') {
                        onOpenChange(true);
                    } else {
                        setOpenValue(true);
                    }
                });

                close?.forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (typeof onOpenChange !== 'undefined') {
                            onOpenChange(false);
                        } else if (typeof onOpenChange === 'undefined') {
                            setOpenValue(false);
                        }
                    });
                });

                overlay?.addEventListener('click', () => {
                    if (typeof onOpenChange !== 'undefined') {
                        if (!closeOnOverlayClick) {
                            onOpenChange(false);
                        }
                    } else if (!closeOnOverlayClick) {
                        setOpenValue(false);
                    }
                });

                pp.effect(() => {
                    if (dialogOpenAttribute !== null) {
                        if (open) {
                            openDialog();
                        } else {
                            closeDialog();
                        }
                    } else {
                        if (openValue) {
                            openDialog();
                        } else {
                            closeDialog();
                        }
                    }
                }, [open, openValue]);
            </script>
        </div>
        HTML;
    }
}

class DialogTrigger extends PHPX
{
    /** @property bool $asChild = true|false */
    public ?bool $asChild = false;
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-trigger',
            'type' => 'button'
        ]);
        $class = $this->getMergeClasses($this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class' => $class,
                'data-slot' => 'dialog-trigger',
                'asChild' => true,
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

class DialogClose extends PHPX
{
    /** @property bool $asChild = true|false */
    public ?bool $asChild = false;
    public ?string $class = '';
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-close',
            'type' => 'button',
        ]);
        $class = $this->getMergeClasses($this->class);

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
        <button class="{$class}" {$attributes}>
            {$this->children}
            <span class="sr-only">Close</span>
        </button>
        HTML;
    }
}

class DialogOverlay extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $dialogProps = StateManager::getState("phpxui.Dialog");
        $overlayClass = $dialogProps['overlayClass'] ?? '';
        $class = $this->getMergeClasses(
            'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/50',
            $overlayClass,
            $this->class
        );
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-overlay',
            'class' => $class,
        ]);

        return <<<HTML
        <div {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DialogContent extends PHPX
{
    public ?string $class = '';
    /** @property ?bool $disablePortal = true|false */
    public ?bool $disablePortal = false;
    public mixed $children = null;

    public function render(): string
    {
        $dialogProps = StateManager::getState("phpxui.Dialog");
        $portalId = $dialogProps['portalId'];
        $class = $this->getMergeClasses(
            'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed top-[50%] left-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 rounded-lg border p-6 shadow-lg duration-200 sm:max-w-lg',
            $this->class
        );
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-content',
            'tabindex' => '-1',
            'data-state' => 'closed',
            'data-portal-id' => $portalId,
            'class' => $class,
        ]);
        $close = (new DialogClose([
            'children' => (new X([
                'class' => 'size-4'
            ]))->render(),
            'class' => "ring-offset-background focus:ring-ring data-[state=open]:bg-accent data-[state=open]:text-muted-foreground absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 z-50"
        ]))->render();
        $overlay = (new DialogOverlay())->render();

        $inner = <<<HTML
        <div>
            {$overlay}
            <div {$attributes}>
                {$this->children}
                {$close}
            </div>
        </div>
        HTML;

        $disablePortal = $this->props['disablePortal'] ?? false;
        $portalTo = $this->props['portalTo'] ?? 'body';

        if ($disablePortal) {
            return $inner;
        }

        $portal = new Portal([
            'to' => $portalTo,
            'children' => $inner,
            'id' => $portalId,
            'hidden' => 'true',
        ]);

        return $portal->render();
    }
}

class DialogHeader extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-header'
        ]);
        $class = $this->getMergeClasses('flex flex-col gap-2 text-center sm:text-left', $this->class);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DialogFooter extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $class = $this->getMergeClasses('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', $this->class);
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-footer',
            'class' => $class,
        ]);

        return <<<HTML
        <div {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DialogTitle extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $class = $this->getMergeClasses('text-lg leading-none font-semibold', $this->class);
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-title',
            'class' => $class,
        ]);

        return <<<HTML
        <h3 {$attributes}>
            {$this->children}
        </h3>
        HTML;
    }
}

class DialogDescription extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $class = $this->getMergeClasses('text-muted-foreground text-sm', $this->class);
        $attributes = $this->getAttributes([
            'data-slot' => 'dialog-description',
            'class' => $class,
        ]);

        return <<<HTML
        <p {$attributes}>
            {$this->children}
        </p>
        HTML;
    }
}
