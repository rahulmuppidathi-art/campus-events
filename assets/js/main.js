/**
 * CAMPUS EVENTS - Client-Side Interactions & Form Validation
 * Team Leader: Patrick
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password confirmation check on registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        registerForm.addEventListener('submit', (e) => {
            if (passwordInput && confirmPasswordInput) {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match. Please re-enter your password.');
                    confirmPasswordInput.focus();
                    return false;
                }
                if (passwordInput.value.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long.');
                    passwordInput.focus();
                    return false;
                }
            }
        });
    }

    // 2. Client-side quick filter for Events Directory if available
    const liveSearchInput = document.getElementById('liveEventSearch');
    if (liveSearchInput) {
        liveSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const eventCards = document.querySelectorAll('.event-search-item');

            eventCards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const category = card.getAttribute('data-category') || '';
                const venue = card.getAttribute('data-venue') || '';

                if (title.includes(query) || category.includes(query) || venue.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // 3. Auto-fill Demo Credentials on Login Page (Helps academic graders test quickly!)
    window.fillCredentials = function(email, password) {
        const emailField = document.getElementById('loginEmail');
        const passField = document.getElementById('loginPassword');
        if (emailField && passField) {
            emailField.value = email;
            passField.value = password;
        }
    };

    // 4. Ticket Print Helper
    const printTicketBtn = document.getElementById('printTicketBtn');
    if (printTicketBtn) {
        printTicketBtn.addEventListener('click', () => {
            window.print();
        });
    }

    // 5. Bootstrap Tooltip Initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

