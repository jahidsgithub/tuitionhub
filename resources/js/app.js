document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-app-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButtons = document.querySelectorAll('[data-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    const openSidebar = () => {
        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');

        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
        });

        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.add('-translate-x-full');

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');

        window.setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 200);

        document.body.classList.remove('overflow-hidden');
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openSidebar);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeSidebar);
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    const userMenuButton = document.querySelector(
        '[data-user-menu-button]'
    );

    const userMenu = document.querySelector(
        '[data-user-menu]'
    );

    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', (event) => {
            event.stopPropagation();
            userMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (
                !userMenu.contains(event.target) &&
                !userMenuButton.contains(event.target)
            ) {
                userMenu.classList.add('hidden');
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();

            if (userMenu) {
                userMenu.classList.add('hidden');
            }
        }
    });
});