<?php
/**
 * CAMPUS EVENTS - Global Page Footer
 */
?>
</main>

<footer class="footer-campus mt-auto">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Mission -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon bg-primary">
                        <i class="bi bi-calendar4-event"></i>
                    </div>
                    <span class="fs-5 fw-bold text-white"><?= htmlspecialchars(APP_NAME) ?></span>
                </div>
                <p class="text-white-50 small mb-3">
                    The official centralized event discovery and registration portal for college students. Empowering campus life through seamless event coordination and student participation.
                </p>
                <div class="mb-3">
                    <span class="badge bg-dark border border-secondary text-light px-2.5 py-1.5 font-monospace small">
                        <?= htmlspecialchars(APP_TAGLINE) ?>
                    </span>
                </div>
                <div>
                    <a href="#" class="footer-social-icon" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="footer-social-icon" aria-label="GitHub"><i class="bi bi-github"></i></a>
                    <a href="#" class="footer-social-icon" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="footer-social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="col-lg-2 col-md-6">
                <h5>Navigation</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>events.php">All Events</a></li>
                    <li><a href="<?= BASE_URL ?>about.php">About Platform</a></li>
                    <li><a href="<?= BASE_URL ?>contact.php">Contact & Support</a></li>
                    <li><a href="<?= BASE_URL ?>setup.php">Database Setup</a></li>
                </ul>
            </div>

            <!-- Event Categories -->
            <div class="col-lg-3 col-md-6">
                <h5>Event Categories</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>events.php?category=technical-events">Technical Events & Hackathons</a></li>
                    <li><a href="<?= BASE_URL ?>events.php?category=cultural-events">Cultural Fests & Arts</a></li>
                    <li><a href="<?= BASE_URL ?>events.php?category=sports-events">Inter-Department Sports</a></li>
                    <li><a href="<?= BASE_URL ?>events.php?category=workshops">Hands-on Workshops</a></li>
                    <li><a href="<?= BASE_URL ?>events.php?category=symposiums">National Symposiums</a></li>
                    <li><a href="<?= BASE_URL ?>events.php?category=college-festivals">College Festivals</a></li>
                </ul>
            </div>

            <!-- University Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h5>Campus Office</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2 text-white-50">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
                        <span>Student Affairs & Innovation Wing, Block A, Central Campus</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-primary"></i>
                        <a href="mailto:events@campusevents.edu" class="text-white-50">events@campusevents.edu</a>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span>+1 (555) 019-4820</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-fill text-primary"></i>
                        <span>Mon – Fri: 09:00 AM – 05:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(APP_NAME) ?></strong>. Academic Web Technology Project.
            </div>
            <div class="text-white-50">
                Team Leader: <span class="text-white fw-semibold"><?= htmlspecialchars(TEAM_LEADER) ?></span> • Built with PHP &amp; MySQL
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>

