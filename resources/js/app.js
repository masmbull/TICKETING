import './bootstrap';

window.MITO = window.MITO || {};

/**
 * Global toast notification helper.
 *
 * Usage: window.MITO.toast('Ticket updated', 'success' | 'error' | 'info')
 */
window.MITO.toast = function (message, type = 'success') {
    let container = document.getElementById('mito-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'mito-toast-container';
        container.className = 'fixed bottom-4 right-4 z-[100] flex flex-col gap-2';
        document.body.appendChild(container);
    }

    const icons = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    const accents = {
        success: '#10b981',
        error: '#f43f5e',
        info: '#3b82f6',
    };
    const tile = {
        success: 'bg-emerald-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
    };

    const toast = document.createElement('div');
    toast.className = 'toast-item';
    toast.style.borderLeftColor = accents[type] || accents.info;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `
        <div class="w-8 h-8 rounded-lg ${tile[type] || tile.info} flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type] || icons.info}"/></svg>
        </div>
        <p class="text-sm font-medium text-slate-700 dark:text-slate-200 pr-5">${message}</p>
        <button type="button" aria-label="Dismiss notification" class="toast-close absolute top-2 right-2 p-1 text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>`;

    container.appendChild(toast);

    const dismiss = () => {
        if (toast.classList.contains('toast-item-leave')) return;
        toast.classList.add('toast-item-leave');
        setTimeout(() => toast.remove(), 200);
    };
    toast.querySelector('.toast-close').addEventListener('click', dismiss);
    setTimeout(dismiss, 3500);
};
