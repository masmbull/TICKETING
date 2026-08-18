import './bootstrap';
import Swal from 'sweetalert2';

window.MITO = window.MITO || {};

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function toastTheme() {
    if (isDarkMode()) {
        return {
            background: '#1e293b',
            color: '#f1f5f9',
            titleColor: '#f8fafc',
            titleText: { color: '#f8fafc' },
            text: { color: '#cbd5e1' },
            confirmButtonColor: '#E30613',
            iconColor: '#10b981',
            popup: {
                background: '#1e293b',
                border: '1px solid #334155',
                boxShadow: '0 10px 25px -5px rgba(0,0,0,.4)',
            },
            timerProgressBarStyle: { background: 'rgba(227,6,19,.6)' },
        };
    }
    return {
        background: '#ffffff',
        color: '#334155',
        titleColor: '#0f172a',
        text: { color: '#64748b' },
        confirmButtonColor: '#E30613',
        iconColor: '#10b981',
        popup: {
            background: '#ffffff',
            border: '1px solid #e2e8f0',
            boxShadow: '0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1)',
        },
        timerProgressBarStyle: { background: '#E30613' },
    };
}

/**
 * Reusable SweetAlert2 toast notification.
 *
 * Usage: window.MITO.toast('Ticket created!', 'ITSUP-...')
 *        window.MITO.toast('Error', 'msg', 'error')
 */
window.MITO.toast = function (title, text = '', type = 'success') {
    const theme = toastTheme();

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title,
        text,
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        background: theme.background,
        color: theme.color,
        iconColor: theme.iconColor,
        customClass: {
            popup: 'mito-swal-popup',
            title: 'mito-swal-title',
            htmlContainer: 'mito-swal-text',
            timerProgressBar: 'mito-swal-progress',
        },
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
            const popup = toast.closest('.swal2-popup');
            if (popup) {
                popup.style.background = theme.popup.background;
                popup.style.border = theme.popup.border;
                popup.style.boxShadow = theme.popup.boxShadow;
            }
            const titleEl = popup?.querySelector('.swal2-title');
            if (titleEl) titleEl.style.color = theme.titleColor;
            const textEl = popup?.querySelector('.swal2-html-container');
            if (textEl) textEl.style.color = theme.text.color;
        },
    });
};

window.MITO.alertError = function (title, text = '') {
    Swal.fire({ icon: 'error', title, text, confirmButtonColor: '#E30613' });
};
