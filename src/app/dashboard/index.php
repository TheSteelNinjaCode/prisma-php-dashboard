<!-- SideNavBar Component -->
<aside class="h-screen w-64 fixed left-0 top-0 hidden md:flex flex-col bg-slate-50 dark:bg-slate-950 z-50 border-r border-slate-100 dark:border-slate-800">
    <div class="flex flex-col p-6 gap-2 h-full">
        <div class="mb-8 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl primary-gradient flex items-center justify-center shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
            </div>
            <div>
                <h1 class="text-2xl font-black text-indigo-900 dark:text-indigo-100 tracking-tighter font-headline">Ethereal</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Enterprise SaaS</p>
            </div>
        </div>
        <nav class="flex-1 space-y-1">
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20 font-semibold transition-all scale-102 duration-200" href="#">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Dashboard</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors duration-200" href="#">
                <span class="material-symbols-outlined">monitoring</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Analytics</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors duration-200" href="#">
                <span class="material-symbols-outlined">description</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Reports</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors duration-200" href="#">
                <span class="material-symbols-outlined">group</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Team</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors duration-200" href="#">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Settings</span>
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-100 dark:border-slate-800 space-y-1">
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 transition-colors" href="#">
                <span class="material-symbols-outlined">help</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Support</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 transition-colors" href="#">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-['Manrope'] font-medium tracking-tight text-sm">Log Out</span>
            </a>
            <div class="mt-4 p-5 rounded-2xl primary-gradient text-white shadow-xl shadow-primary/20">
                <p class="text-[10px] font-bold opacity-80 mb-1 tracking-widest">PRO PLAN</p>
                <p class="text-sm font-bold mb-3 leading-tight">Get unlimited seats &amp; insights</p>
                <button class="w-full py-2 bg-white/20 backdrop-blur-md rounded-lg text-xs font-extrabold hover:bg-white/30 transition-all active:scale-95">Upgrade Pro</button>
            </div>
        </div>
    </div>
</aside>
<main class="md:ml-64 min-h-screen">
    <!-- TopNavBar Component -->
    <header class="fixed top-0 right-0 w-full md:w-[calc(100%-16rem)] z-40 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl shadow-sm shadow-indigo-100/20 dark:shadow-none border-b border-slate-100/50">
        <div class="flex justify-between items-center h-16 px-8">
            <div class="flex items-center gap-8">
                <div class="relative hidden lg:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input class="bg-surface-container-low border-none rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 w-64 placeholder:text-slate-400 transition-all font-medium" placeholder="Search analytics..." type="text" />
                </div>
                <nav class="hidden xl:flex items-center gap-6">
                    <a class="text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 pb-1 font-['Inter'] text-sm font-semibold transition-all ease-in-out" href="#">Overview</a>
                    <a class="text-slate-500 dark:text-slate-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-['Inter'] text-sm font-medium transition-all ease-in-out" href="#">Insights</a>
                    <a class="text-slate-500 dark:text-slate-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-['Inter'] text-sm font-medium transition-all ease-in-out" href="#">Activity</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors relative">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full border-2 border-white"></span>
                </button>
                <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors">
                    <span class="material-symbols-outlined">apps</span>
                </button>
                <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>
                <button class="primary-gradient text-white px-5 py-2 rounded-full text-sm font-bold flex items-center gap-2 hover:shadow-lg hover:shadow-primary/30 transition-all scale-102 active:scale-95">
                    <span class="material-symbols-outlined text-sm">add</span>
                    New Report
                </button>
                <div class="flex items-center gap-3 ml-2 group cursor-pointer">
                    <img alt="User Profile" class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/10 group-hover:ring-primary/30 transition-all" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB-4fvPTLhNCxxVnSozaMgfYWEcjNi9APXEWykfCubgngIJVzU7U-tZLvGqgW8MwKL1cNop2cDwAIjmZxthwMtG2YwDoobg9sD1_OW7_s4huHxOkimAWGN1lE2LFj1128IpCOUYi0XY-YRAGAfoVJ8-41lB2bLIZS1lFT4XMo_p5Bnc2owBvfMreWcVkYSPJ6iCafZJkYAjFpW33KoWoQAmI6SLdwdbD1RTeNTKvijGpMgsMsLXRvlsjTNcHIrvq87rh4eO4fPvd-w" />
                </div>
            </div>
        </div>
    </header>
    <!-- Content Canvas -->
    <div class="pt-24 pb-12 px-8 max-w-7xl mx-auto space-y-10">
        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
            <div>
                <h2 class="text-4xl font-extrabold font-headline tracking-tight text-on-surface">Analytics Hub</h2>
                <p class="text-on-surface-variant font-medium mt-1">Real-time performance metrics for your organization.</p>
            </div>
            <div class="flex gap-1 bg-surface-container rounded-xl p-1 w-fit">
                <button class="px-5 py-2 text-xs font-bold rounded-lg bg-white shadow-sm text-primary transition-all">Last 30 Days</button>
                <button class="px-5 py-2 text-xs font-bold rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-all">Quarterly</button>
            </div>
        </div>
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- KPI 1 -->
            <div class="bg-white p-7 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">group</span>
                    </div>
                    <span class="text-[11px] font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">+12.5%</span>
                </div>
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1">Total Users</p>
                <h3 class="text-3xl font-black font-headline text-on-surface tracking-tight">42,840</h3>
            </div>
            <!-- KPI 2 -->
            <div class="bg-white p-7 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">card_membership</span>
                    </div>
                    <span class="text-[11px] font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">+4.2%</span>
                </div>
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1">Active Subscriptions</p>
                <h3 class="text-3xl font-black font-headline text-on-surface tracking-tight">18,204</h3>
            </div>
            <!-- KPI 3 -->
            <div class="bg-white p-7 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">payments</span>
                    </div>
                    <span class="text-[11px] font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">+8.1%</span>
                </div>
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1">Monthly Revenue</p>
                <h3 class="text-3xl font-black font-headline text-on-surface tracking-tight">$1.24M</h3>
            </div>
        </div>
        <!-- Main Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Large Chart: Revenue Over Time -->
            <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
                    <div>
                        <h4 class="text-xl font-bold font-headline text-on-surface tracking-tight">Revenue Dynamics</h4>
                        <p class="text-sm font-medium text-on-surface-variant/70">Annual growth projection and current trajectory</p>
                    </div>
                    <div class="flex gap-5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Net Revenue</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary-container/40"></span>
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Projection</span>
                        </div>
                    </div>
                </div>
                <div class="h-72 w-full relative">
                    <svg class="w-full h-full" preserveaspectratio="none" viewbox="0 0 800 240">
                        <defs>
                            <lineargradient id="grad-area" x1="0%" x2="0%" y1="0%" y2="100%">
                                <stop offset="0%" style="stop-color: #5D3FD3; stop-opacity:0.12"></stop>
                                <stop offset="100%" style="stop-color: #5D3FD3; stop-opacity:0"></stop>
                            </lineargradient>
                            <filter height="200%" id="shadow" width="200%" x="0" y="0">
                                <fedropshadow dx="0" dy="8" shadow-color="rgba(93, 63, 211, 0.2)" stddeviation="6"></fedropshadow>
                            </filter>
                        </defs>
                        <!-- Grid Lines -->
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="800" y1="40" y2="40"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="800" y1="100" y2="100"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="800" y1="160" y2="160"></line>
                        <path d="M0,200 Q150,180 300,120 T500,80 T800,40 L800,240 L0,240 Z" fill="url(#grad-area)"></path>
                        <path d="M0,200 Q150,180 300,120 T500,80 T800,40" fill="none" stroke="#5D3FD3" stroke-linecap="round" stroke-width="4"></path>
                        <path d="M0,210 Q150,200 300,170 T500,140 T800,120" fill="none" opacity="0.4" stroke="#5D3FD3" stroke-dasharray="8,6" stroke-width="2.5"></path>
                    </svg>
                    <div class="absolute bottom-0 left-0 right-0 flex justify-between px-2 pt-6 border-t border-slate-50">
                        <span class="text-[10px] font-extrabold text-slate-400">JAN</span>
                        <span class="text-[10px] font-extrabold text-slate-400">MAR</span>
                        <span class="text-[10px] font-extrabold text-slate-400">MAY</span>
                        <span class="text-[10px] font-extrabold text-slate-400">JUL</span>
                        <span class="text-[10px] font-extrabold text-slate-400">SEP</span>
                        <span class="text-[10px] font-extrabold text-slate-400">NOV</span>
                    </div>
                </div>
            </div>
            <!-- Bar Chart: User Growth -->
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm flex flex-col">
                <h4 class="text-xl font-bold font-headline text-on-surface tracking-tight mb-1">User Growth</h4>
                <p class="text-sm font-medium text-on-surface-variant/70 mb-10">Acquisition by channel</p>
                <div class="flex-1 flex items-end justify-between gap-3 h-48 mb-10">
                    <div class="w-full bg-slate-50 rounded-xl relative group h-[100%]">
                        <div class="absolute bottom-0 w-full bg-primary/20 rounded-xl h-[40%] transition-all group-hover:h-[50%]"></div>
                        <div class="absolute bottom-0 w-full bg-primary rounded-xl h-[25%] transition-all group-hover:h-[35%]"></div>
                    </div>
                    <div class="w-full bg-slate-50 rounded-xl relative group h-[100%]">
                        <div class="absolute bottom-0 w-full bg-primary/20 rounded-xl h-[70%] transition-all group-hover:h-[80%]"></div>
                        <div class="absolute bottom-0 w-full bg-primary rounded-xl h-[45%] transition-all group-hover:h-[55%]"></div>
                    </div>
                    <div class="w-full bg-slate-50 rounded-xl relative group h-[100%]">
                        <div class="absolute bottom-0 w-full bg-primary/20 rounded-xl h-[55%] transition-all group-hover:h-[65%]"></div>
                        <div class="absolute bottom-0 w-full bg-primary rounded-xl h-[40%] transition-all group-hover:h-[50%]"></div>
                    </div>
                    <div class="w-full bg-slate-50 rounded-xl relative group h-[100%]">
                        <div class="absolute bottom-0 w-full bg-primary/20 rounded-xl h-[85%] transition-all group-hover:h-[95%]"></div>
                        <div class="absolute bottom-0 w-full bg-primary rounded-xl h-[60%] transition-all group-hover:h-[70%]"></div>
                    </div>
                    <div class="w-full bg-slate-50 rounded-xl relative group h-[100%]">
                        <div class="absolute bottom-0 w-full bg-primary/20 rounded-xl h-[45%] transition-all group-hover:h-[55%]"></div>
                        <div class="absolute bottom-0 w-full bg-primary rounded-xl h-[30%] transition-all group-hover:h-[40%]"></div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Organic Search</span>
                        <span class="text-xs font-black text-on-surface">42%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary w-[42%] rounded-full"></div>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Direct Traffic</span>
                        <span class="text-xs font-black text-on-surface">28%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-300 w-[28%] rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Recent Activity Table Section -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h4 class="text-2xl font-bold font-headline text-on-surface tracking-tight">Recent Activity</h4>
                    <p class="text-sm font-medium text-on-surface-variant/70">Live feed of global user interactions and system events.</p>
                </div>
                <button class="text-primary font-bold text-sm px-4 py-2 hover:bg-primary/5 rounded-xl transition-all">View All Activity</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-y border-slate-100">User</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-y border-slate-100">Action</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-y border-slate-100">Tier</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-y border-slate-100">Time</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-y border-slate-100">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Row 1 -->
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-xs font-extrabold text-primary border border-primary/20">JB</div>
                                    <div>
                                        <p class="text-sm font-bold text-on-surface">Jerome Bell</p>
                                        <p class="text-[11px] font-medium text-slate-400">jerome.b@example.com</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-semibold text-on-surface">Upgraded to Enterprise</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black border border-indigo-100">ENTERPRISE</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-[11px] font-bold text-slate-500 uppercase">2 mins ago</span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs font-bold text-emerald-700">Successful</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-xs font-extrabold text-purple-600 border border-purple-100">AW</div>
                                    <div>
                                        <p class="text-sm font-bold text-on-surface">Albert Webb</p>
                                        <p class="text-[11px] font-medium text-slate-400">albert.w@domain.io</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm font-semibold text-on-surface">New report generated</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black border border-slate-200">TEAM</span>
                            </td>
                            <td class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase">14 mins ago</td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-bold text-emerald-700">Active</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 3 -->
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-xs font-extrabold text-slate-600 border border-slate-200">ES</div>
                                    <div>
                                        <p class="text-sm font-bold text-on-surface">Eleanor Simmons</p>
                                        <p class="text-[11px] font-medium text-slate-400">e.simmons@web.com</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm font-semibold text-on-surface">Password reset request</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black border border-slate-200">BASIC</span>
                            </td>
                            <td class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase">45 mins ago</td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-bold text-amber-700">Pending</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
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