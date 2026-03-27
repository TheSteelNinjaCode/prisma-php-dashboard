<?php

use Lib\PHPXUI\{Button, DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger};
use Lib\PPIcons\Bell;
use Lib\Auth\Auth;
use PP\Attributes\Exposed;

$user = Auth::getInstance()->getPayload();

#[Exposed(requiresAuth: true)]
function logout() {
    Auth::getInstance()->signOut(redirect: true);
}

?>

<header class="fixed top-0 right-0 w-full md:w-[calc(100%-16rem)] z-40 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl shadow-sm shadow-indigo-100/20 dark:shadow-none border-b border-slate-100/50">
    <div class="flex justify-end items-center h-16 px-8">
        <div class="flex items-center gap-4">
            <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors relative">
                <Bell class="size-5" />
                <span class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full border-2 border-white"></span>
            </button>
            <div class="flex items-center gap-3 ml-2 group cursor-pointer">
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        <img alt="User Profile" class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/10 group-hover:ring-primary/30 transition-all" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB-4fvPTLhNCxxVnSozaMgfYWEcjNi9APXEWykfCubgngIJVzU7U-tZLvGqgW8MwKL1cNop2cDwAIjmZxthwMtG2YwDoobg9sD1_OW7_s4huHxOkimAWGN1lE2LFj1128IpCOUYi0XY-YRAGAfoVJ8-41lB2bLIZS1lFT4XMo_p5Bnc2owBvfMreWcVkYSPJ6iCafZJkYAjFpW33KoWoQAmI6SLdwdbD1RTeNTKvijGpMgsMsLXRvlsjTNcHIrvq87rh4eO4fPvd-w" />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent class="w-56" align="start">
                        <DropdownMenuLabel><?= $user->name ?></DropdownMenuLabel>

                        <DropdownMenuGroup>
                            <DropdownMenuItem>
                                Profile
                                <DropdownMenuShortcut>⇧⌘P</DropdownMenuShortcut>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>

                        <DropdownMenuSeparator />

                        <DropdownMenuItem onclick="handleLogout()">
                            Log out
                            <DropdownMenuShortcut>⇧⌘Q</DropdownMenuShortcut>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </div>

    <script>
        async function handleLogout() {
            await pp.fetchFunction('logout');
        }
    </script>
</header>