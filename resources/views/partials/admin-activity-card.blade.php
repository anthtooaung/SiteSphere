<div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:w-0.5 before:-translate-x-px before:bg-slate-100">
    @forelse($logs as $log)
        <div class="relative flex items-start gap-6 group">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white ring-4 ring-white shadow-sm transition-transform group-hover:scale-110" style="color: {{ $log->getColor() }}; border: 1px solid {{ $log->getColor() }}20">
                <i class="fa-solid {{ $log->getIcon() }}"></i>
            </div>
            <div class="flex flex-col pt-1">
                <p class="text-sm font-bold text-slate-900 leading-tight">
                    {{ $log->action }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $log->user->name }}</span>
                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $log->created_at->format('H:i') }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-20 text-slate-300">
            <i class="fa-solid fa-ghost text-5xl mb-4 opacity-20"></i>
            <p class="text-sm font-bold uppercase tracking-widest">No activity recorded for this day</p>
        </div>
    @endforelse

    @if(!$isFullList && $totalCount > 3)
        <div class="pt-4 pl-14">
            <button @click="selectDate(selectedDate, true)" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-700 transition-colors flex items-center gap-2 group">
                See all {{ $totalCount }} actions
                <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
            </button>
        </div>
    @endif
</div>
