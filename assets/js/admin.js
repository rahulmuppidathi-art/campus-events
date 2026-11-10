/**
 * CAMPUS EVENTS - Admin Dashboard Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Image Preview on File Input Change
    const imageInput = document.getElementById('event_banner');
    const imagePreview = document.getElementById('banner_preview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Generic Delete Confirmation
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const itemName = btn.getAttribute('data-name') || 'this item';
            if (!confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // 3. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('show');
        });
    }
});

