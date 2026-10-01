// This only confirms destructive forms. All permissions are checked in PHP.
document.addEventListener('submit', function (event) {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) event.preventDefault();
});

const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
function closeSidebar() {
    sidebar?.classList.remove('is-open');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
}
sidebarToggle?.addEventListener('click', () => {
    const open = sidebar.classList.toggle('is-open');
    sidebarToggle.setAttribute('aria-expanded', String(open));
});
document.addEventListener('click', (event) => {
    document.querySelectorAll('.user-menu[open]').forEach(menu => {
        if (!menu.contains(event.target)) menu.open = false;
    });
    if (!sidebar?.contains(event.target) && !sidebarToggle?.contains(event.target)) closeSidebar();
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.user-menu[open]').forEach(menu => {
            menu.open = false;
            menu.querySelector('summary').focus();
        });
        if (sidebar?.classList.contains('is-open')) {
            closeSidebar();
            sidebarToggle.focus();
        }
    }
});
