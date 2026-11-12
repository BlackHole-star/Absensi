import './bootstrap';

/**
 * Toast notifikasi client-side — pengganti alert() native browser.
 * type: 'success' | 'danger' | 'warning'
 */
window.showToast = function showToast(message, type = 'success') {
    const stack = document.getElementById('toast-stack');
    if (!stack) return;

    const icons = {
        success: 'bi-check-circle-fill',
        danger: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
    };

    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0`;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');
    el.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi ${icons[type] || icons.success}"></i>
                <span></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
    `;
    el.querySelector('.toast-body span').textContent = message;

    stack.appendChild(el);
    const toast = bootstrap.Toast.getOrCreateInstance(el, { delay: 4000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
};

document.addEventListener('DOMContentLoaded', () => {
    // Tutup flash alert (sukses/gagal) otomatis setelah beberapa detik
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach((alert) => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        });
    }, 3000);

    // Tutup menu navigasi mobile otomatis setelah salah satu link diklik
    const navbarCollapseEl = document.getElementById('mainNavbar');
    if (navbarCollapseEl) {
        navbarCollapseEl.querySelectorAll('.nav-link').forEach((link) => {
            link.addEventListener('click', () => {
                if (navbarCollapseEl.classList.contains('show')) {
                    bootstrap.Collapse.getOrCreateInstance(navbarCollapseEl).hide();
                }
            });
        });
    }
});
