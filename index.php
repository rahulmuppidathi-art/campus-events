<?php
/**
 * CAMPUS EVENTS - Home Page
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$pageTitle = 'Campus Events | Where Campus Life Comes Alive';
require_once __DIR__ . '/includes/header.php';

// Fetch statistics from database with graceful fallback
$totalEvents = 0;
$registeredStudents = 0;
$completedEvents = 0;
$featuredEvents = [];
$categories = [];
$upcomingEvents = [];
$testimonials = [];

$db = getDB();
if ($db) {
    try {
        // 1. Database Statistics
        $stmt = $db->query("SELECT COUNT(*) FROM events");
        $totalEvents = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(DISTINCT user_id) FROM registrations");
        $registeredStudents = (int) $stmt->fetchColumn();
        if ($registeredStudents === 0) {
            // If no registrations yet, count student accounts
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
            $registeredStudents = (int) $stmt->fetchColumn();
        }

        $stmt = $db->query("SELECT COUNT(*) FROM events WHERE status = 'completed' OR event_date < CURRENT_DATE()");
        $completedEvents = (int) $stmt->fetchColumn();

        // 2. Featured Events
        $stmt = $db->query("
            SELECT e.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug 
            FROM events e 
            JOIN categories c ON e.category_id = c.id 
            WHERE e.status = 'published' AND e.is_featured = 1 
            ORDER BY e.event_date ASC 
            LIMIT 3
        ");
        $featuredEvents = $stmt->fetchAll();

        // 3. Categories with Event Counts
        $stmt = $db->query("
            SELECT c.*, COUNT(e.id) AS event_count 
            FROM categories c 
            LEFT JOIN events e ON c.id = e.category_id AND e.status = 'published' 
            GROUP BY c.id 
            ORDER BY c.id ASC
        ");
        $categories = $stmt->fetchAll();

        // 4. Upcoming Events
        $stmt = $db->query("
            SELECT e.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug 
            FROM events e 
            JOIN categories c ON e.category_id = c.id 
            WHERE e.status = 'published' AND e.event_date >= CURRENT_DATE() 
            ORDER BY e.event_date ASC 
            LIMIT 6
        ");
        $upcomingEvents = $stmt->fetchAll();

        // 5. Testimonials
        $stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id ASC LIMIT 4");
        $testimonials = $stmt->fetchAll();

    } catch (PDOException $e) {
        // Fallback gracefully if tables don't exist yet
    }
}

// Fallback dummy data if DB empty or offline
if (empty($categories)) {
    $categories = [
        ['name' => 'Technical Events', 'slug' => 'technical-events', 'icon' => 'bi-code-slash', 'color' => '#2563EB', 'event_count' => 3, 'description' => 'Coding competitions, hackathons, robotics, and tech showcases.'],
        ['name' => 'Cultural Events', 'slug' => 'cultural-events', 'icon' => 'bi-palette', 'color' => '#7C3AED', 'event_count' => 2, 'description' => 'Music, dance, theatre, fine arts, and literary performances.'],
        ['name' => 'Sports Events', 'slug' => 'sports-events', 'icon' => 'bi-trophy', 'color' => '#059669', 'event_count' => 1, 'description' => 'Inter-department and inter-college athletics and tournaments.'],
        ['name' => 'Workshops', 'slug' => 'workshops', 'icon' => 'bi-tools', 'color' => '#D97706', 'event_count' => 2, 'description' => 'Hands-on training, industry masterclasses, and skill building.'],
        ['name' => 'Symposiums', 'slug' => 'symposiums', 'icon' => 'bi-mortarboard', 'color' => '#DC2626', 'event_count' => 1, 'description' => 'Academic research conferences, paper presentations, and talks.'],
        ['name' => 'College Festivals', 'slug' => 'college-festivals', 'icon' => 'bi-stars', 'color' => '#4F46E5', 'event_count' => 1, 'description' => 'Grand annual university celebrations and campus galas.']
    ];
}
?>

<!-- 1. Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-tag">
                    <i class="bi bi-mortarboard-fill"></i> Official University Event Portal
                </div>
                <h1 class="hero-title">
                    Where Campus Life <br>
                    <span>Comes Alive</span>
                </h1>
                <p class="hero-lead">
                    Discover upcoming hackathons, sports championships, workshops, and cultural fests. Register with a single click, secure your digital entry passes, and never miss out on collegiate memories.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>events.php" class="btn btn-primary btn-lg px-4 py-2.5">
                        <i class="bi bi-compass me-2"></i> Explore Events
                    </a>
                    <?php if (!is_logged_in()): ?>
                        <a href="<?= BASE_URL ?>register.php" class="btn btn-outline-navy btn-lg px-4 py-2.5">
                            <i class="bi bi-person-plus me-2"></i> Student Sign Up
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>my-registrations.php" class="btn btn-outline-navy btn-lg px-4 py-2.5">
                            <i class="bi bi-ticket-perforated me-2"></i> My Passes
                        </a>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3 d-flex align-items-center gap-4 text-muted small border-top">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle text-success fs-6"></i> Instant E-Pass
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle text-success fs-6"></i> Zero Registration Fees
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle text-success fs-6"></i> Verified Certificates
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Highlight Hero Banner Card -->
                <div class="card border shadow-lg rounded-4 overflow-hidden">
                    <div class="position-relative">
                        <img src="<?= BASE_URL ?>assets/images/events/hackathon.jpg" class="card-img-top" alt="Featured Hackathon" style="height: 250px; object-fit: cover;">
                        <span class="position-absolute top-0 end-0 m-3 badge bg-danger text-white px-3 py-2 fw-semibold">
                            <i class="bi bi-fire me-1"></i> Featured Highlight
                        </span>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary-subtle text-primary fw-semibold">Technical Events</span>
                            <span class="text-muted small">• 36-Hour Sprint</span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">HackInnovate 2026</h4>
                        <p class="text-muted small mb-3">
                            Join over 200 developers and creators solving real-world challenges in AI, CleanTech, and Web3 with prizes over $10,000.
                        </p>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1 text-primary"></i> Nov 20, 2026
                            </span>
                            <a href="<?= BASE_URL ?>event-details.php?id=1" class="btn btn-navy btn-sm px-3">
                                Register Now <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Statistics Section (From actual DB records) -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalEvents ?: 9) ?>+</div>
                    <div class="stat-label"><i class="bi bi-calendar-check me-1 text-primary"></i> Total Events</div>
                    <div class="text-muted small mt-1">Hosted across departments</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($registeredStudents ?: 150) ?>+</div>
                    <div class="stat-label"><i class="bi bi-people-fill me-1 text-success"></i> Registered Students</div>
                    <div class="text-muted small mt-1">Active collegiate participants</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($completedEvents ?: 4) ?>+</div>
                    <div class="stat-label"><i class="bi bi-award-fill me-1 text-warning"></i> Completed Events</div>
                    <div class="text-muted small mt-1">Successfully organized sessions</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Event Categories Section -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fw-semibold mb-2">Browse By Domain</span>
            <h2 class="fw-bold">Explore Event Categories</h2>
            <p class="text-muted">Choose your passion and discover university programs tailored to your interests.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <a href="<?= BASE_URL ?>events.php?category=<?= urlencode($cat['slug']) ?>" class="category-card">
                        <div class="category-icon" style="background-color: <?= htmlspecialchars($cat['color']) ?>18; color: <?= htmlspecialchars($cat['color']) ?>;">
                            <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($cat['name']) ?></h5>
                        <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($cat['description']) ?></p>
                        <div class="d-flex align-items-center justify-content-between text-muted small pt-2 border-top">
                            <span><?= (int) ($cat['event_count'] ?? 0) ?> Available Events</span>
                            <span class="text-primary fw-semibold">View Domain <i class="bi bi-chevron-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. Featured Upcoming Events Section -->
<?php if (!empty($featuredEvents)): ?>
<section class="py-5 bg-white border-bottom">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 fw-semibold mb-2">Editor's Pick</span>
                <h2 class="fw-bold mb-1">Featured Campus Highlights</h2>
                <p class="text-muted mb-0">Don't miss our highest-demand inter-collegiate programs.</p>
            </div>
            <a href="<?= BASE_URL ?>events.php" class="btn btn-outline-navy mt-3 mt-md-0">
                View All Events <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredEvents as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-card-img-wrapper">
                            <img src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" class="event-card-img" alt="<?= htmlspecialchars($event['title']) ?>">
                            <span class="event-category-badge">
                                <?= htmlspecialchars($event['category_name']) ?>
                            </span>
                            <span class="event-status-badge badge bg-success">
                                Featured
                            </span>
                        </div>
                        <div class="event-card-body">
                            <h5 class="event-card-title">
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h5>
                            <p class="event-card-description">
                                <?= htmlspecialchars($event['short_description']) ?>
                            </p>
                            <ul class="event-meta-list">
                                <li>
                                    <i class="bi bi-calendar3"></i>
                                    <span><?= format_date($event['event_date']) ?></span>
                                </li>
                                <li>
                                    <i class="bi bi-clock"></i>
                                    <span><?= format_time($event['start_time']) ?></span>
                                </li>
                                <li>
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="text-truncate"><?= htmlspecialchars($event['venue']) ?></span>
                                </li>
                            </ul>
                            <div class="event-card-footer">
                                <span class="seats-badge">
                                    <i class="bi bi-people me-1"></i> <?= $event['available_seats'] ?> seats left
                                </span>
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Upcoming Events Section -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fw-semibold mb-2">Schedule</span>
                <h2 class="fw-bold mb-1">Upcoming Events</h2>
                <p class="text-muted mb-0">Plan your schedule and reserve seats before registration closes.</p>
            </div>
            <a href="<?= BASE_URL ?>events.php" class="btn btn-outline-navy mt-3 mt-md-0">
                Browse Full Schedule <i class="bi bi-calendar4-week ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php 
            $displayUpcoming = !empty($upcomingEvents) ? $upcomingEvents : $featuredEvents;
            if (!empty($displayUpcoming)):
                foreach ($displayUpcoming as $event): 
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card">
                        <div class="event-card-img-wrapper">
                            <img src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" class="event-card-img" alt="<?= htmlspecialchars($event['title']) ?>">
                            <span class="event-category-badge">
                                <?= htmlspecialchars($event['category_name'] ?? 'College Event') ?>
                            </span>
                        </div>
                        <div class="event-card-body">
                            <h5 class="event-card-title">
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h5>
                            <p class="event-card-description">
                                <?= htmlspecialchars($event['short_description']) ?>
                            </p>
                            <ul class="event-meta-list">
                                <li>
                                    <i class="bi bi-calendar3"></i>
                                    <span><?= format_date($event['event_date']) ?></span>
                                </li>
                                <li>
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="text-truncate"><?= htmlspecialchars($event['venue']) ?></span>
                                </li>
                            </ul>
                            <div class="event-card-footer">
                                <span class="seats-badge">
                                    <i class="bi bi-hourglass-split me-1"></i> Closes: <?= format_date($event['registration_deadline']) ?>
                                </span>
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-navy">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            else: 
            ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No upcoming events found at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 6. Why Choose Campus Events Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-3">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 fw-semibold mb-2">Key Advantages</span>
            <h2 class="fw-bold">Why Choose Campus Events</h2>
            <p class="text-muted">Designed specifically to bridge university students, faculties, and event organizers.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">1-Click Fast Registration</h5>
                    <p class="text-muted small mb-0">
                        No manual paperwork or queueing at department notice boards. Registered students can sign up for competitions and workshops in seconds.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Digital Pass &amp; Verification</h5>
                    <p class="text-muted small mb-0">
                        Every confirmed registration generates a verifiable digital pass with a unique tracking code for seamless campus venue entry.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Real-Time Seat Tracking</h5>
                    <p class="text-muted small mb-0">
                        Live seat counters show available capacity instantly, preventing accidental overbooking and keeping students informed.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Verified University Organizers</h5>
                    <p class="text-muted small mb-0">
                        All events are reviewed, approved, and maintained by department faculty coordinators and officially recognized student clubs.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Automated Deadline Tracking</h5>
                    <p class="text-muted small mb-0">
                        Stay ahead of time with explicit registration cutoff indicators, schedule timings, and venue updates.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-box">
                    <div class="feature-box-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Administrator Command Center</h5>
                    <p class="text-muted small mb-0">
                        Organizers enjoy comprehensive analytics, student rosters, instant CSV export, and attendance status check-ins.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fw-semibold mb-2">Student Voices</span>
            <h2 class="fw-bold">What Our Campus Says</h2>
            <p class="text-muted">Hear from students and student leaders who participate in university events.</p>
        </div>

        <div class="row g-4">
            <?php 
            $testiList = !empty($testimonials) ? $testimonials : [
                ['student_name' => 'Patrick', 'department' => 'Computer Science & Engineering', 'quote' => 'Campus Events completely transformed how we find and participate in collegiate hackathons. Everything from instant digital passes to real-time seat alerts is seamless!'],
                ['student_name' => 'Elena Rodriguez', 'department' => 'Electronics & Communication', 'quote' => 'Registering for university workshops used to mean standing in line at the department notice board. Now I can discover upcoming technical symposiums with one click.'],
                ['student_name' => 'Marcus Chen', 'department' => 'Mechanical Engineering', 'quote' => 'The sports clash registrations and schedule notifications kept our department team organized. A truly professional platform worthy of our university.'],
                ['student_name' => 'Aisha Patel', 'department' => 'Business Administration', 'quote' => 'As an event organizer, tracking attendee check-ins and managing seats through the admin portal saved our team dozens of hours of manual paperwork.']
            ];

            foreach ($testiList as $testi): 
            ?>
                <div class="col-lg-3 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-quote">
                            "<?= htmlspecialchars($testi['quote']) ?>"
                        </p>
                        <div class="testimonial-author mt-auto">
                            <div class="brand-icon" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                <?= strtoupper(substr($testi['student_name'], 0, 2)) ?>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold fs-6 text-dark"><?= htmlspecialchars($testi['student_name']) ?></h6>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($testi['department']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center py-4">
        <h2 class="text-white fw-bold mb-3">Ready to Be Part of the Next Big Event?</h2>
        <p class="text-white-50 max-w-700 mx-auto mb-4">
            Join thousands of active students experiencing university life to the fullest. Create your profile or explore what's happening this week.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>events.php" class="btn btn-primary px-4 py-2.5">
                Browse All Events <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= BASE_URL ?>register.php" class="btn btn-outline-light px-4 py-2.5">
                    Create Student Account
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

