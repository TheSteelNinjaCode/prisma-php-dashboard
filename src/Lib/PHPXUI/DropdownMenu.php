<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;
use PP\MainLayout;
use PP\StateManager;
use Lib\PHPXUI\Slot;
use Lib\PPIcons\ChevronRight;

class DropdownMenu extends PHPX
{
    public string|bool|null $open = '{false}';
    public ?string $onOpenChange = null;
    public ?string $class = '';
    public ?string $id = null;
    public mixed $children = null;

    private static bool $scriptAdded = false;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
        $this->addHydrationScript();
    }

    private function addHydrationScript(): void
    {
        if (self::$scriptAdded) {
            return;
        }

        $script = <<<JS
        <script>
            function initPHPXDropdown(dropdownmenu) {
                if (dropdownmenu.dataset.ppHydrated === 'true') return;
                dropdownmenu.dataset.ppHydrated = 'true';

                const uniqueId = dropdownmenu.id || ('dd-' + Math.random().toString(36).substr(2, 9));
                dropdownmenu.id = uniqueId;

                const trigger = dropdownmenu.querySelector('[data-slot="dropdown-menu-trigger"]');
                let content = dropdownmenu.querySelector('[data-slot="dropdown-menu-content"]');
                
                if (content && content.parentElement === dropdownmenu) {
                     const placeholder = document.createComment('portal-placeholder-' + uniqueId);
                     dropdownmenu.replaceChild(placeholder, content);
                     
                     if (typeof pp !== 'undefined' && typeof pp.createPortal === 'function') {
                         pp.createPortal(content, document.body, { 
                             contextElement: dropdownmenu 
                         });
                     } else {
                         document.body.appendChild(content);
                     }
                     
                     const contentId = 'content-' + uniqueId;
                     content.id = contentId;
                     if(trigger) trigger.setAttribute('aria-controls', contentId);

                     const nearestForm = dropdownmenu.closest('form');
                     if (nearestForm) {
                        const formId = nearestForm.id || ('form-' + Math.random().toString(36).substr(2, 9));
                        nearestForm.id = formId;
                        content.querySelectorAll('button, input, select, textarea').forEach(el => {
                            if (!el.hasAttribute('form')) el.setAttribute('form', formId);
                        });
                     }
                }

                let isOpen = false;
                
                const setOpen = (value) => {
                    if (isOpen === value) return;
                    isOpen = value;

                    if (isOpen) {
                        document.dispatchEvent(new CustomEvent('phpx-dropdown-open', {
                            detail: { id: uniqueId }
                        }));
                    }
                    
                    updateUI();
                };

                const handleOtherOpen = (e) => {
                    if (e.detail.id !== uniqueId && isOpen) {
                        setOpen(false);
                    }
                };
                document.addEventListener('phpx-dropdown-open', handleOtherOpen);

                const updateUI = () => {
                    const stateStr = isOpen ? 'open' : 'closed';
                    dropdownmenu.setAttribute('data-state', stateStr);
                    if (trigger) {
                        trigger.setAttribute('data-state', stateStr);
                        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    }
                    if (content) {
                        if (isOpen) {
                            content.hidden = false;
                            void content.offsetWidth;
                            content.setAttribute('data-state', 'open');
                            content.style.pointerEvents = 'auto';
                            
                            requestAnimationFrame(positionContent);
                            setTimeout(() => {
                                document.addEventListener('click', onOutsideClick);
                                window.addEventListener('resize', positionContent);
                                window.addEventListener('scroll', positionContent, {capture: true, passive: true});
                            }, 0);
                        } else {
                            content.setAttribute('data-state', 'closed');
                            content.style.pointerEvents = 'none';
                            
                            const cleanupListeners = () => {
                                document.removeEventListener('click', onOutsideClick);
                                window.removeEventListener('resize', positionContent);
                                window.removeEventListener('scroll', positionContent, {capture: true});
                            };
                            
                            const onAnimationEnd = () => {
                                if (!isOpen) { 
                                    content.hidden = true;
                                }
                            };
                            
                            content.addEventListener('animationend', onAnimationEnd, { once: true });
                            
                            setTimeout(() => {
                                if (!isOpen) onAnimationEnd();
                            }, 200);

                            cleanupListeners();
                        }
                    }
                };

                const positionContent = () => {
                    if (!isOpen || !content || !trigger) return;
                    const rect = trigger.getBoundingClientRect();
                    const contentRect = content.getBoundingClientRect();
                    const viewportW = document.documentElement.clientWidth;
                    const viewportH = document.documentElement.clientHeight;
                    const gap = 4;
                    
                    let side = content.getAttribute('data-side') || 'bottom';
                    
                    if (side === 'bottom' && rect.bottom + contentRect.height + gap > viewportH && rect.top > contentRect.height + gap) side = 'top';
                    if (side === 'top' && rect.top - contentRect.height - gap < 0 && rect.bottom + contentRect.height + gap < viewportH) side = 'bottom';

                    let top, left;
                    if (side === 'bottom') {
                        top = rect.bottom + gap;
                        left = rect.left + (rect.width/2) - (contentRect.width/2);
                    } else if (side === 'top') {
                        top = rect.top - contentRect.height - gap;
                        left = rect.left + (rect.width/2) - (contentRect.width/2);
                    } else if (side === 'left') {
                        top = rect.top;
                        left = rect.left - contentRect.width - gap;
                    } else {
                        top = rect.top;
                        left = rect.right + gap;
                    }
                    
                    left = Math.max(gap, Math.min(left, viewportW - contentRect.width - gap));
                    content.style.position = 'fixed';
                    content.style.top = top + 'px';
                    content.style.left = left + 'px';
                    content.style.zIndex = '100';
                };

                if (trigger) {
                    trigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        setOpen(!isOpen);
                    });
                }

                const onOutsideClick = (e) => {
                    if (!isOpen) return;
                    if (content.contains(e.target) || trigger.contains(e.target)) return;
                    setOpen(false);
                };

                if (content) {
                    content.querySelectorAll('[data-slot="dropdown-menu-item"]').forEach(item => {
                        item.addEventListener('click', () => setOpen(false));
                    });
                }

                updateUI();
            }

            pp.effect(() => {
                document.querySelectorAll('div[data-slot="dropdown-menu"]').forEach(el => {
                    initPHPXDropdown(el);
                });
            });
        </script>
        JS;

        MainLayout::addFooterScript($script);
        self::$scriptAdded = true;
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'data-slot' => 'dropdown-menu',
            'data-state' => 'closed',
            'id' => $this->id,
        ]);

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DropdownMenuTrigger extends PHPX
{
    public ?bool $asChild = false;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'dropdown-menu-trigger',
            'type' => 'button',
            'aria-expanded' => 'false',
            'data-state' => 'closed',
        ]);
        $class = $this->getMergeClasses($this->class);

        if ($this->asChild) {
            $slot = new Slot([
                'class' => $class,
                'data-slot' => 'dropdown-menu-trigger',
                'aria-expanded' => 'false',
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

class DropdownMenuContent extends PHPX
{
    public ?string $side = 'bottom';
    public ?string $align = 'center';
    public int|string|null $sideOffset = 4;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $attributes = $this->getAttributes([
            'data-slot' => 'dropdown-menu-content',
            'role' => 'menu',
            'tabindex' => '-1',
            'data-state' => 'closed',
            'hidden' => 'true',
            'data-side' => $this->side,
            'data-align' => $this->align,
            'data-offset' => (string) $this->sideOffset,
        ]);

        $class = $this->getMergeClasses(
            'bg-popover text-popover-foreground data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 fixed z-50 min-w-[8rem] rounded-md border p-1 shadow-md',
            $this->class
        );

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DropdownMenuItem extends PHPX
{
    public ?bool $disabled = false;
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $disabledFlag = $this->disabled ? 'true' : 'false';

        $attributes = $this->getAttributes([
            'data-slot' => 'dropdown-menu-item',
            'role' => 'menuitem',
            'tabindex' => '-1',
            'aria-disabled' => $disabledFlag,
            'data-disabled' => $disabledFlag,
        ]);

        $class = $this->getMergeClasses(
            "focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground data-[variant=destructive]:text-destructive data-[variant=destructive]:focus:bg-destructive/10 dark=data-[variant=destructive]:focus:bg-destructive/20 data-[variant=destructive]:focus:text-destructive [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-left text-sm outline-hidden select-none data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
            $this->class
        );

        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DropdownMenuGroup extends PHPX
{
    public ?bool $asChild = false;
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['role' => 'group', 'data-slot' => 'dropdown-menu-group']);
        $class = $this->getMergeClasses($this->class);
        if ($this->asChild) {
            $slot = new Slot(['class' => $class, 'data-slot' => 'dropdown-menu-group', 'asChild' => true, ...$this->attributesArray]);
            $slot->children = $this->children;
            return $slot->render();
        }
        return <<<HTML
        <div class="{$class}" {$attributes}>{$this->children}</div>
        HTML;
    }
}

class DropdownMenuLabel extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-label']);
        $class = $this->getMergeClasses('px-2 py-1.5 text-sm font-medium', $this->class);
        return <<<HTML
        <div class="{$class}" {$attributes}>{$this->children}</div>
        HTML;
    }
}

class DropdownMenuSeparator extends PHPX
{
    public ?string $class = '';
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-separator', 'role' => 'separator']);
        $class = $this->getMergeClasses('bg-border -mx-1 my-1 h-px', $this->class);
        return <<<HTML
        <div class="{$class}" {$attributes}></div>
        HTML;
    }
}

class DropdownMenuShortcut extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-shortcut']);
        $class = $this->getMergeClasses('text-muted-foreground ml-auto text-xs tracking-widest', $this->class);
        return <<<HTML
        <span class="{$class}" {$attributes}>{$this->children}</span>
        HTML;
    }
}

class DropdownMenuSub extends PHPX
{
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-sub']);
        $class = $this->getMergeClasses('relative', $this->class);
        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}

class DropdownMenuSubTrigger extends PHPX
{
    public ?bool $asChild = false;
    public ?string $class = '';
    public ?string $id = null;
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-sub-trigger', 'data-state' => 'closed', 'aria-expanded' => 'false', 'tabindex' => '-1']);
        $class = $this->getMergeClasses("w-full text-left hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[state=open]:bg-accent data-[state=open]:text-accent-foreground flex cursor-default items-center rounded-sm px-2 py-1.5 text-sm outline-hidden select-none", $this->class);
        if ($this->asChild) {
            $slot = new Slot(['class' => $class, 'data-slot' => 'dropdown-menu-sub-trigger', 'asChild' => true, ...$this->attributesArray]);
            $slot->children = $this->children;
            return $slot->render();
        }
        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
            <ChevronRight class="ml-auto size-4" />
        </div>
        HTML;
    }
}

class DropdownMenuSubContent extends PHPX
{
    public ?string $side = 'right';
    public ?string $align = 'start';
    public ?string $class = '';
    public mixed $children = null;
    public function render(): string
    {
        $attributes = $this->getAttributes(['data-slot' => 'dropdown-menu-sub-content', 'role' => 'menu', 'data-state' => 'closed', 'hidden' => 'true', 'data-side' => $this->side, 'data-align' => $this->align]);
        $class = $this->getMergeClasses('fixed z-[60] min-w-[8rem] rounded-md border bg-popover p-1 text-popover-foreground shadow-lg data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95', $this->class);
        return <<<HTML
        <div class="{$class}" {$attributes}>
            {$this->children}
        </div>
        HTML;
    }
}
