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