const sidebar = document.getElementById('app-sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggle = document.getElementById('sidebar-toggle');

if (sidebar && backdrop && toggle) {
    const setSidebarState = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setSidebarState(sidebar.classList.contains('-translate-x-full')));
    backdrop.addEventListener('click', () => setSidebarState(false));
}
