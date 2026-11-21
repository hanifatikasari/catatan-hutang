// Prevent accidental value changes when the mouse wheel is over a numeric input.
document.addEventListener('wheel', function (event) {
    if (event.target instanceof HTMLInputElement && event.target.type === 'number') {
        event.preventDefault();
    }
}, { passive: false });

// Responsive Sidebar Toggle & Management
(function () {
    function initSidebar() {
        const toggleBtn = document.getElementById('appSidebarToggle');
        const closeBtn = document.getElementById('appSidebarClose');
        const backdrop = document.getElementById('appSidebarBackdrop');
        const sidebar = document.getElementById('appSidebar');

        if (!toggleBtn || !sidebar) return;

        const isMobile = () => window.innerWidth <= 768;

        // Restore desktop collapse preference
        if (!isMobile() && localStorage.getItem('sidebar_collapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            if (isMobile()) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
            }
        }

        function closeMobileSidebar() {
            document.body.classList.remove('sidebar-mobile-open');
        }

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSidebar();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMobileSidebar);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeMobileSidebar);
        }

        // Close mobile drawer when a navigation link is clicked
        const navLinks = sidebar.querySelectorAll('.app-nav a');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) {
                    closeMobileSidebar();
                }
            });
        });

        // Close on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
                closeMobileSidebar();
            }
        });

        // Clean up mobile open state on resize to larger screens
        window.addEventListener('resize', function () {
            if (!isMobile() && document.body.classList.contains('sidebar-mobile-open')) {
                document.body.classList.remove('sidebar-mobile-open');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();
