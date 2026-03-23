<?php

use PP\ImportComponent;
use PP\MainLayout;

?>

<div>
    <?php ImportComponent::render(APP_PATH . '/dashboard/sidebar.php'); ?>
    <?php ImportComponent::render(APP_PATH . '/dashboard/header.php'); ?>
    <main class="md:ml-64 min-h-screen">
        <!-- Content Canvas -->
        <div class="pt-24 pb-12 px-8 max-w-7xl mx-auto space-y-10">
            <?= MainLayout::$children ?>
        </div>
    </main>
    <!-- Contextual Glass Toast -->
    <div class="fixed bottom-8 right-8 z-50 flex items-center gap-5 glass-panel p-5 rounded-2xl shadow-2xl border border-white/40 max-w-sm transform transition-all hover:scale-[1.02]">
        <div class="w-12 h-12 rounded-2xl primary-gradient flex items-center justify-center text-white shrink-0 shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
        </div>
        <div class="pr-2">
            <p class="text-sm font-extrabold text-on-surface tracking-tight">Performance Insight</p>
            <p class="text-xs font-medium text-on-surface-variant leading-relaxed">Your user retention is up <span class="font-bold text-emerald-600">15%</span> this week. Excellent work!</p>
        </div>
        <button class="absolute top-2 right-2 p-1 text-slate-400 hover:text-slate-600 transition-colors">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
</div>