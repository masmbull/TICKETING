{{--
    Tooltip onboarding tour "Cara buat tiket ke IT".
    Enabled + steps configured by admin in Settings → Tooltip. Shown once per
    user (localStorage key per user id); targets come from data-tour attributes.
--}}
@php
    $tourSetting = \App\Models\TooltipSetting::first();
    $tourSteps = ($tourSetting->enabled ?? false) ? ($tourSetting->steps ?: []) : [];
@endphp

@if(count($tourSteps) && auth()->check())
<div x-data="onboardingTour(@js(array_values($tourSteps)), 'mito_tour_seen_{{ auth()->id() }}')" x-cloak>
    <template x-if="open">
        <div>
            {{-- Dimmed backdrop: clicking it skips the tour --}}
            <div class="fixed inset-0 z-[110] bg-slate-900/65" @click="skip()"></div>

            {{-- Spotlight ring around the target element --}}
            <template x-if="rect">
                <div class="fixed z-[111] rounded-lg pointer-events-none transition-all duration-200 border-2 border-[#E30613]"
                     :style="`top:${rect.top}px; left:${rect.left}px; width:${rect.width}px; height:${rect.height}px;`"></div>
            </template>

            {{-- Card --}}
            <div class="fixed z-[112] w-80 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xl p-4 transition-all duration-200"
                 :style="`top:${card.top}px; left:${card.left}px;`"
                 role="dialog" aria-live="polite">
                {{-- Arrow --}}
                <template x-if="card.arrow === 'top'">
                    <div class="absolute -top-2 w-4 h-4 bg-white dark:bg-slate-800 border-l border-t border-slate-200 dark:border-slate-700 rotate-45"
                         :style="`left:${card.arrowLeft}px;`"></div>
                </template>
                <template x-if="card.arrow === 'bottom'">
                    <div class="absolute -bottom-2 w-4 h-4 bg-white dark:bg-slate-800 border-r border-b border-slate-200 dark:border-slate-700 rotate-45"
                         :style="`left:${card.arrowLeft}px;`"></div>
                </template>

                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold text-[#E30613] uppercase tracking-wider">
                        Langkah <span x-text="index + 1"></span> / <span x-text="steps.length"></span>
                    </span>
                    <button type="button" @click="skip()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" aria-label="Tutup">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="steps[index].title || 'Cara buat tiket ke IT'"></h3>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="steps[index].description"></p>

                {{-- Progress dots --}}
                <div class="flex items-center gap-1 mt-3">
                    <template x-for="(s, i) in steps" :key="i">
                        <span class="h-1.5 rounded-full transition-all"
                              :class="i === index ? 'w-4 bg-[#E30613]' : 'w-1.5 bg-slate-300 dark:bg-slate-600'"></span>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <button type="button" @click="skip()" class="text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">Lewati</button>
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="index > 0" @click="prev()"
                                class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Sebelumnya</button>
                        <button type="button" @click="next()"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors"
                                x-text="index >= steps.length - 1 ? 'Selesai' : 'Lanjut'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
window.onboardingTour = function (steps, storageKey) {
    return {
        steps: steps,
        storageKey: storageKey,
        index: 0,
        open: false,
        rect: null,
        card: { top: 0, left: 0, arrow: 'none', arrowLeft: 150 },
        pad: 8,

        init() {
            if (!this.steps.length) { return; }

            let seen = false;
            try { seen = localStorage.getItem(this.storageKey) === '1'; } catch (e) {}

            // Wait for the splash screen to fade out before showing the tour.
            if (!seen) { setTimeout(() => this.show(0), 1100); }

            window.addEventListener('resize', () => { if (this.open) { this.position(); } });
            window.addEventListener('scroll', () => { if (this.open) { this.position(); } }, true);
            document.addEventListener('keydown', e => {
                if (!this.open) { return; }
                if (e.key === 'Escape') { this.skip(); }
                if (e.key === 'ArrowRight') { this.next(); }
                if (e.key === 'ArrowLeft') { this.prev(); }
            });
        },

        show(i) {
            this.index = i;
            this.open = true;
            this.$nextTick(() => this.position());
        },

        position() {
            const step = this.steps[this.index] || {};
            const el = step.target ? document.querySelector('[data-tour="' + step.target + '"]') : null;
            const cw = 320;
            const ch = 200;

            if (!el) {
                this.rect = null;
                this.card = {
                    top: Math.max(16, window.innerHeight / 2 - ch / 2),
                    left: Math.max(16, window.innerWidth / 2 - cw / 2),
                    arrow: 'none',
                    arrowLeft: cw / 2 - 8,
                };
                return;
            }

            const r = el.getBoundingClientRect();
            this.rect = {
                top: r.top - this.pad,
                left: r.left - this.pad,
                width: r.width + this.pad * 2,
                height: r.height + this.pad * 2,
            };

            let top = r.bottom + 16;
            let arrow = 'top';
            if (top + ch > window.innerHeight - 16) {
                top = Math.max(16, r.top - ch - 16);
                arrow = 'bottom';
            }

            const left = Math.min(Math.max(16, r.left + r.width / 2 - cw / 2), Math.max(16, window.innerWidth - cw - 16));
            const arrowLeft = Math.min(Math.max(16, r.left + r.width / 2 - left - 8), cw - 32);

            this.card = { top: top, left: left, arrow: arrow, arrowLeft: arrowLeft };
        },

        next() {
            if (this.index >= this.steps.length - 1) { return this.finish(); }
            this.show(this.index + 1);
        },

        prev() {
            if (this.index > 0) { this.show(this.index - 1); }
        },

        skip() {
            this.finish();
        },

        finish() {
            this.open = false;
            try { localStorage.setItem(this.storageKey, '1'); } catch (e) {}
        },
    };
};
</script>
@endif