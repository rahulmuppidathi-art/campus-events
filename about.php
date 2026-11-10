<?php
/**
 * CAMPUS EVENTS - About the Platform
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$pageTitle = 'About Us | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<!-- About Hero Header -->
<div class="bg-white border-bottom py-5">
    <div class="container text-center max-w-700 mx-auto">
        <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fw-semibold mb-2">Our Mission</span>
        <h1 class="display-5 fw-bold mb-3 text-dark">Connecting Campus Communities Through Memorable Events</h1>
        <p class="lead text-muted fs-6">
            Campus Events is a modern university web application engineered to simplify collegiate event discovery, registration, and attendance tracking for students, faculties, and event organizers.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Project Background & Architecture -->
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="badge bg-dark text-white font-monospace px-2.5 py-1 mb-2">ACADEMIC IMPLEMENTATION</span>
            <h2 class="fw-bold mb-3">Engineered for Academic Excellence &amp; Practical Utility</h2>
            <p class="text-muted">
                Traditional college event management frequently relied on printed posters, paper registrations, and manual attendance sheets at venue gates. This caused administrative delays, lost participant records, and limited visibility into seat capacity.
            </p>
            <p class="text-muted">
                <strong>Campus Events</strong> eliminates these friction points by providing a centralized database-driven architecture with secure authentication, real-time seat inventory, and verifiable digital entry passes.
            </p>
            <div class="row g-3 pt-2">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <span class="fw-semibold small">Secure Role-Based Access</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-database-check text-primary fs-5"></i>
                        <span class="fw-semibold small">Transactional Seat Management</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-qr-code text-primary fs-5"></i>
                        <span class="fw-semibold small">Verifiable Digital Passcodes</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-line text-primary fs-5"></i>
                        <span class="fw-semibold small">Comprehensive Admin Analytics</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border rounded-4 p-4 shadow-sm bg-white">
                <h5 class="fw-bold mb-3"><i class="bi bi-layers-half text-primary me-2"></i>Technology Implementation Stack</h5>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">Frontend Architecture</span>
                        <span class="badge bg-light text-dark border">HTML5 • CSS3 • JavaScript (ES6)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">UI Framework &amp; Icons</span>
                        <span class="badge bg-light text-dark border">Bootstrap 5 • Bootstrap Icons</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">Backend Engine</span>
                        <span class="badge bg-primary text-white">PHP 8+ (PDO Prepared Statements)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">Relational Database</span>
                        <span class="badge bg-dark text-white">MySQL / MariaDB</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">Runtime Environment</span>
                        <span class="badge bg-light text-dark border">Apache Web Server (XAMPP)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="fw-semibold">Project Leader</span>
                        <span class="badge bg-success-subtle text-success border fw-bold"><?= htmlspecialchars(TEAM_LEADER) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Core Pillars -->
    <div class="text-center max-w-700 mx-auto mb-5">
        <h3 class="fw-bold">Platform Objectives &amp; Values</h3>
        <p class="text-muted small">Built around four fundamental design pillars.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="feature-box text-center">
                <div class="feature-box-icon mx-auto">
                    <i class="bi bi-search"></i>
                </div>
                <h5 class="fw-bold fs-6 mb-2">Discovery</h5>
                <p class="text-muted small mb-0">Allowing students from all faculties to easily uncover workshops and contests that align with their goals.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box text-center">
                <div class="feature-box-icon mx-auto">
                    <i class="bi bi-cursor-fill"></i>
                </div>
                <h5 class="fw-bold fs-6 mb-2">Simplicity</h5>
                <p class="text-muted small mb-0">Streamlined 1-click registration workflow without repetitive data entry or confusing paperwork.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box text-center">
                <div class="feature-box-icon mx-auto">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-bold fs-6 mb-2">Integrity</h5>
                <p class="text-muted small mb-0">Strong database transactions, CSRF validation, password encryption, and verified attendee tracking.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box text-center">
                <div class="feature-box-icon mx-auto">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h5 class="fw-bold fs-6 mb-2">Productivity</h5>
                <p class="text-muted small mb-0">Providing faculty and student coordinators with live statistics, participant rosters, and CSV exports.</p>
            </div>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="card border rounded-4 p-4 p-md-5 bg-white max-w-800 mx-auto shadow-sm">
        <h4 class="fw-bold mb-4 text-center">Frequently Asked Questions</h4>
        <div class="accordion accordion-flush" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq1">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                        How do I register for an upcoming event?
                    </button>
                </h2>
                <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small">
                        Simply sign in to your student account, browse the <a href="events.php">Events Directory</a>, select any published event, and click <strong>Confirm Registration</strong>. Your digital pass will be generated instantly.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq2">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                        Can I cancel a registration if my schedule changes?
                    </button>
                </h2>
                <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small">
                        Yes! Navigate to your <a href="my-registrations.php">My Registrations</a> dashboard and click "Cancel Pass" before the event begins. The seat is immediately released back to the event pool for other students.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq3">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                        How do organizers verify entry passes at the venue?
                    </button>
                </h2>
                <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small">
                        Every pass includes a unique tracking code (e.g., <code>REG-2026-XXXXX</code>) and a digital barcode. Event administrators can look up attendees by code or student roll number in the Admin Command Center to mark attendance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

