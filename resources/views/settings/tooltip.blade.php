@extends('layouts.app')

@section('title', 'Tooltip - MITO IT Helpdesk')

@section('content')
<div class="space-y-6" x-data="{ on: {{ $setting->enabled ? 'true' : 'false' }}, steps: @js($setting->steps ?: \App\Models\TooltipSetting::defaultSteps()) }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Tooltip Onboarding</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Tour "Cara buat tiket ke IT" untuk user saat login</p>
        </div>
    </div>

    <form method="POST" action="{{ route('tooltip.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Status --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</h2>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" x-model="on" @change="$el.form.requestSubmit()" class="sr-only peer">
                    <span class="w-10 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer-checked:bg-[#E30613] relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5"></span>
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-300" x-text="on ? 'Aktif' : 'Nonaktif'"></span>
                </label>
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">Saat aktif, tour tampil sekali per user (disimpan di browser) sesudah login.</p>
            </div>
        </div>

        {{-- Steps --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Langkah <span x-text="steps.length"></span></h2>
                <button type="button" @click="steps.push({ title: '', description: '', target: '' })" class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">Tambah Langkah</button>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                <template x-for="(step, idx) in steps" :key="idx">
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Langkah <span x-text="idx + 1"></span></span>
                            <button type="button" @click="steps.splice(idx, 1)" class="text-xs text-[#E30613] hover:text-[#c4050f] font-medium">Hapus</button>
                        </div>
                        <input type="text" :name="`steps[${idx}][title]`" x-model="step.title" placeholder="Judul"
                               class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400">
                        <textarea :name="`steps[${idx}][description]`" x-model="step.description" rows="2" placeholder="Deskripsi"
                                  class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400"></textarea>
                        <input type="text" :name="`steps[${idx}][target]`" x-model="step.target" placeholder="Target (kosong = tengah layar)"
                               class="w-full px-3 py-2 text-xs font-mono bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400">
                    </div>
                </template>
                <template x-if="steps.length === 0">
                    <div class="px-4 py-8 text-center text-sm text-slate-400">Belum ada langkah.</div>
                </template>
            </div>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">Simpan</button>
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Target tersedia</h2>
        <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1 font-mono">
            <li>create-ticket-link &mdash; item sidebar Create Ticket</li>
            <li>ticket-category &mdash; dropdown Category di form tiket</li>
            <li>ticket-description &mdash; textarea deskripsi</li>
            <li>ticket-submit &mdash; tombol Submit Ticket</li>
        </ul>
    </div>
</div>
@endsection