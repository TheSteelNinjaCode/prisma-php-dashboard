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
</div>