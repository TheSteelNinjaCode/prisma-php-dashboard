<?php

use Lib\PHPXUI\Button;

use Lib\PPIcons\{ChartNoAxesCombined, CircleHelp, FileText, LayoutDashboard, LogOut, Settings, Users};
use PP\Request;

$currentUrl = Request::$pathname;
echo "<!-- Current URL: $currentUrl -->"; ///dashboard/settings

$notActive = "flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors duration-200";

$active = "flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20 font-semibold transition-all scale-102 duration-200";

?>

<aside class="h-screen w-64 fixed left-0 top-0 hidden md:flex flex-col bg-slate-50 dark:bg-slate-950 z-50 border-r border-slate-100 dark:border-slate-800">
    <div class="flex flex-col p-6 gap-2 h-full">
        <div class="mb-8 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl primary-gradient flex items-center justify-center shadow-lg shadow-primary/20">
                <img src="/favicon.ico" alt="Logo" class="size-6" />
            </div>
            <div>
                <h1 class="text-2xl font-black text-indigo-900 dark:text-indigo-100 tracking-tighter font-headline">Prisma PHP</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Dashboard SK</p>
            </div>
        </div>
        <nav class="flex-1 space-y-1">
            <a class="<?= $currentUrl === '/dashboard' ? $active : $notActive ?>" href="/dashboard">
                <LayoutDashboard class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Dashboard</span>
            </a>
            <a class="<?= $currentUrl === '/dashboard/analytics' ? $active : $notActive ?>" href="#">
                <ChartNoAxesCombined class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Analytics</span>
            </a>
            <a class="<?= $currentUrl === '/dashboard/reports' ? $active : $notActive ?>" href="#">
                <FileText class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Reports</span>
            </a>
            <a class="<?= $currentUrl === '/dashboard/team' ? $active : $notActive ?>" href="#">
                <Users class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Team</span>
            </a>
            <a class="<?= $currentUrl === '/dashboard/settings' ? $active : $notActive ?>" href="/dashboard/settings">
                <Settings class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Settings</span>
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-100 dark:border-slate-800 space-y-1">
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 transition-colors" href="#">
                <CircleHelp class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Support</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 transition-colors" href="#">
                <LogOut class="size-5" />
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Log Out</span>
            </a>
            <div class="mt-4 p-5 rounded-2xl bg-primary shadow-xl shadow-primary/20">
                <p class="text-[10px] font-bold opacity-80 mb-1 tracking-widest text-primary-foreground">PRO PLAN</p>
                <p class="text-sm font-bold mb-3 leading-tight text-primary-foreground">Get unlimited seats &amp; insights</p>
                <Button variant="outline">Upgrade Pro</Button>
            </div>
        </div>
    </div>
</aside>