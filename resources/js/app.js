import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Generate initials for a person's name (used by avatar placeholders).
 */
window.initials = function (name) {
    if (!name) return '?';
    return name
        .replace(/^Dr\.?\s+/i, '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
};

/**
 * Sidebar toggle that behaves differently on desktop and mobile:
 * - Mobile (< 992px): opens / closes the off-canvas drawer + overlay.
 * - Desktop: collapses / expands the fixed sidebar in place.
 */
window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (window.innerWidth <= 991.98) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
    }
};

// Close the mobile drawer whenever a navigation link is clicked.
document.addEventListener('click', function (event) {
    if (window.innerWidth <= 991.98) {
        const link = event.target.closest('.sidebar a');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (link && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    }
});

Alpine.start();