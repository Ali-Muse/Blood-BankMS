// Sidebar JavaScript
function toggleSubmenu(element) {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('collapsed')) {
        return; // Don't toggle submenu when collapsed
    }
    const submenu = element.nextElementSibling;
    if (submenu && submenu.classList.contains('submenu')) {
        submenu.classList.toggle('active');
    }
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    sidebar.classList.toggle('mobile-open');
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleBtn = document.querySelector('.sidebar-toggle svg');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');

    // Rotate arrow
    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.innerHTML = '<polyline points="9 18 15 12 9 6"></polyline>'; // Point right
    } else {
        toggleBtn.innerHTML = '<polyline points="15 18 9 12 15 6"></polyline>'; // Point left
    }
}

// Auto-refresh notifications
setInterval(async function () {
    try {
        const response = await fetch('../../includes/get-notifications.php');
        const data = await response.json();

        if (data.success) {
            const badge = document.getElementById('badge-notifications');
            if (badge) {
                badge.textContent = data.count > 0 ? data.count : '';
            }
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}, 30000);
