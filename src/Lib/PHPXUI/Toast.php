<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;
use Lib\PPIcons\X;
use Lib\PHPXUI\Portal;

class Toast extends PHPX
{
    public ?string $class = '';
    /** @property ?string $position = top-left|top-center|top-right|bottom-left|bottom-center|bottom-right|left-center|right-center|center */
    public ?string $position = 'bottom-right';
    /** @property ?string $inDirection = top|bottom|left|right|auto */
    public ?string $inDirection = 'auto';
    /** @property ?string $outDirection = top|bottom|left|right|auto */
    public ?string $outDirection = 'auto';
    public ?int $duration = 5000;
    public ?string $to = 'body';
    public ?string $description = "{''}";
    /** @property string|bool|null $open = '{true}'|'{false}'|* */
    public string|bool|null $open = '{false}';
    public ?string $onOpenChange = null;
    public mixed $children = null;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $portalId = uniqid('portal-');
        $toastId = uniqid('toast-');
        $pos = $this->position ?: 'bottom-right';

        $anchor = match ($pos) {
            'top-left'      => 'top-0 left-0',
            'top-center'    => 'top-0 left-1/2 -translate-x-1/2',
            'top-right'     => 'top-0 right-0',
            'bottom-left'   => 'bottom-0 left-0',
            'bottom-center' => 'bottom-0 left-1/2 -translate-x-1/2',
            'bottom-right'  => 'bottom-0 right-0',
            'left-center'   => 'left-0 top-1/2 -translate-y-1/2',
            'right-center'  => 'right-0 top-1/2 -translate-y-1/2',
            'center'        => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
            default         => 'bottom-0 right-0',
        };

        $stackReverse = in_array($pos, [
            'bottom-left',
            'bottom-center',
            'bottom-right'
        ], true);
        $stackClass = $stackReverse ? 'flex-col-reverse' : 'flex-col';

        $edgeForAuto = match ($pos) {
            'top-left', 'top-center', 'top-right'          => 'top',
            'bottom-left', 'bottom-center', 'bottom-right' => 'bottom',
            'left-center'                                   => 'left',
            'right-center'                                  => 'right',
            'center'                                        => 'bottom',
            default                                         => 'right',
        };

        $normalize = static fn(?string $dir): string => match ($dir) {
            'top', 'bottom', 'left', 'right' => $dir,
            'auto', null, ''                 => 'auto',
            default                          => 'right',
        };

        $reqIn  = $normalize($this->inDirection);
        $reqOut = $normalize($this->outDirection);

        $inDir  = $reqIn  === 'auto' ? $edgeForAuto : $reqIn;
        $outDir = $reqOut === 'auto' ? $edgeForAuto : $reqOut;

        $inClass = [
            'top'    => 'slide-in-from-top-full',
            'bottom' => 'slide-in-from-bottom-full',
            'left'   => 'slide-in-from-left-full',
            'right'  => 'slide-in-from-right-full',
        ][$inDir] ?? 'slide-in-from-right-full';

        $outClass = [
            'top'    => 'slide-out-to-top-full',
            'bottom' => 'slide-out-to-bottom-full',
            'left'   => 'slide-out-to-left-full',
            'right'  => 'slide-out-to-right-full',
        ][$outDir] ?? 'slide-out-to-right-full';

        $toastClasses = implode(' ', [
            'group pointer-events-auto relative flex w-full items-center',
            'justify-between space-x-4 overflow-hidden rounded-md p-6 pr-8',
            'shadow-lg transition-all',
            'border bg-background text-foreground',
            "{open ? 'animate-in {$inClass}' : 'animate-out fade-out-80 {$outClass} opacity-0'}",
        ]);

        $attributes = $this->getAttributes([
            'style' => 'user-select: none; touch-action: none',
            'aria-live' => 'polite',
            'aria-atomic' => 'true',
        ], ['open', 'onOpenChange', 'description']);

        $containerClass = $this->getMergeClasses(
            "fixed z-[100] {$anchor} flex {$stackClass} max-h-screen md:max-w-[420px] w-[min(92vw,420px)] gap-2 p-4",
            $this->class
        );

        $children = null;
        if (empty($this->children)) {
            $children = <<<HTML
                <div class="grid gap-1">
                    <div class="text-sm opacity-90">{description}</div>
                </div>
            HTML;
        } else {
            $children = $this->children;
        }

        return <<<HTML
            <div role="region" aria-label="Notifications" id="{$toastId}" open="{$this->open}" onOpenChange="{$this->onOpenChange}" description="{$this->description}">
                <Portal to="{$this->to}" id="{$portalId}" hidden="true">
                    <ol data-slot="toast-viewport" class="{$containerClass}">
                        <li data-slot="toast" data-state="{open ? 'open' : 'closed'}" class="{$toastClasses}" {$attributes}>
                            {$children}
    
                            <button
                                type="button"
                                onclick="closeToast()"
                                class="absolute right-1 top-1 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-1 group-hover:opacity-100
                                    group-[.destructive]:text-red-300
                                    group-[.destructive]:hover:text-red-50
                                    group-[.destructive]:focus:ring-red-400
                                    group-[.destructive]:focus:ring-offset-red-600"
                                aria-label="Close"
                            >
                                <X class="size-4" />
                            </button>
                        </li>
                    </ol>
                </Portal>

                <script>
                    const toastId = "{$toastId}";
                    const duration = {$this->duration};
                    const portalId = "{$portalId}";
                    
                    let timer;
                    let remaining = duration;
                    let start;
                    let listenersAttached = false;

                    function closeToast() {
                        if (typeof onOpenChange !== 'undefined') {
                            onOpenChange(false);
                        }
                    }

                    const getToastElement = () => {
                        const portal = document.getElementById(portalId);
                        return portal?.querySelector('[data-slot="toast"]');
                    };

                    const startToastTimer = () => {
                        clearTimeout(timer);
                        start = Date.now();
                        timer = setTimeout(() => {
                            closeToast();
                        }, remaining);
                    };

                    const pauseToastTimer = () => {
                        clearTimeout(timer);
                        remaining -= Date.now() - start;
                    };

                    const resumeToastTimer = () => {
                        startToastTimer();
                    };

                    const attachListeners = () => {
                        const toastElement = getToastElement();
                        if (!toastElement) {
                            return;
                        }

                        if (listenersAttached) {
                            return;
                        }

                        toastElement.addEventListener('mouseenter', pauseToastTimer);
                        toastElement.addEventListener('mouseleave', resumeToastTimer);
                        listenersAttached = true;
                    };

                    const detachListeners = () => {
                        const toastElement = getToastElement();
                        if (!toastElement || !listenersAttached) {
                            return;
                        }

                        toastElement.removeEventListener('mouseenter', pauseToastTimer);
                        toastElement.removeEventListener('mouseleave', resumeToastTimer);
                        listenersAttached = false;
                    };

                    pp.effect(() => {
                        if (open) {
                            remaining = duration;
                            
                            const portal = document.getElementById(portalId);
                            if (portal) {
                                portal.hidden = false;
                            }

                            attachListeners();
                            startToastTimer();
                        } else {
                            clearTimeout(timer);
                            detachListeners();

                            setTimeout(() => {
                                const portal = document.getElementById(portalId);
                                if (portal) {
                                    portal.hidden = true;
                                }
                            }, 300);
                        }
                    }, [open]);
                </script>
            </div>
        HTML;
    }
}
