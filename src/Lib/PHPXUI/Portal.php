<?php

declare(strict_types=1);

namespace Lib\PHPXUI;

use PP\PHPX\PHPX;

class Portal extends PHPX
{
    /** @property ?string $to = 'body'|'#selector'|'.selector'|* */
    public ?string $to = 'body';
    public ?string $class = '';
    public mixed $children = null;

    public function render(): string
    {
        $id   = $this->props['id'] ?? uniqid('pp-portal-');
        $targetSelector = ($this->to === '' || $this->to === 'body') ? '#pp-portal-root' : $this->to;

        $attrs = $this->getAttributes([
            'id'                 => $id,
            'data-slot'          => 'portal',
            'data-portal-id'     => $id,
            'data-portal-target' => $targetSelector,
            'to'                 => $this->to,
        ]);

        $class = $this->getMergeClasses($this->class);
        $srcId = uniqid('pp-portal-src-');

        return <<<HTML
        <div id="{$srcId}" hidden="true">
            <div class="{$class}" {$attrs}>
                {$this->children}
            </div>

            <script>
                const portalContent = document.getElementById('{$id}');
                const targetSelector = '{$targetSelector}';
                
                if (!portalContent) return;

                let formOwner = null;
                let formId = null;
                const nearestForm = portalContent.closest('form');

                if (nearestForm) {
                    formOwner = nearestForm;

                    let existingId = nearestForm.getAttribute('id');

                    if (!existingId || typeof existingId !== 'string') {
                        existingId = 'pp-form-' + Math.random().toString(36).slice(2);
                        nearestForm.setAttribute('id', existingId);
                    }

                    formId = existingId;
                    portalContent.dataset.ppFormOwner = formId;
                }

                const attachFormOwner = (root) => {
                    if (!formId || typeof formId !== 'string') return;

                    const selector = 'input, select, textarea, button';

                    root.querySelectorAll(selector).forEach((el) => {
                        const currentClosest = el.closest('form');
                        if (currentClosest && currentClosest === formOwner) {
                            return;
                        }

                        if (!el.hasAttribute('form')) {
                            el.setAttribute('form', formId);
                        }
                    });
                };

                portalContent.addEventListener(
                    'keydown',
                    (e) => {
                        if (e.key === 'Enter') {
                            const t = e.target;
                            const isTextual =
                                t &&
                                (t.matches(
                                    'input:not([type=button]):not([type=submit]):not([type=checkbox]):not([type=radio])'
                                ) ||
                                    t.matches('textarea'));

                            if (isTextual && formOwner && typeof formOwner.requestSubmit === 'function') {
                                const submitBtn = portalContent.querySelector(
                                    'button[type="submit"], input[type="submit"]'
                                );
                                if (!submitBtn) {
                                    formOwner.requestSubmit();
                                    e.preventDefault();
                                }
                            }
                        }
                    },
                    true
                );

                const ensureRoot = () => {
                    let root = document.getElementById('pp-portal-root');
                    if (!root) {
                        root = document.createElement('div');
                        root.id = 'pp-portal-root';
                        document.body.appendChild(root);
                    }
                    return root;
                };

                const target =
                    targetSelector === '#pp-portal-root'
                        ? ensureRoot()
                        : document.querySelector(targetSelector) || document.body;

                const portalId = pp.createPortal(portalContent, target);

                attachFormOwner(portalContent);

                const mo = new MutationObserver((mutations) => {
                    mutations.forEach((m) => {
                        m.addedNodes.forEach((n) => {
                            if (n.nodeType === 1) {
                                attachFormOwner(n);
                            }
                        });
                    });
                });

                mo.observe(portalContent, { childList: true, subtree: true });

                document.getElementById('{$srcId}')?.remove();
            </script>
        </div>
        HTML;
    }
}
