# CAMPUS EVENTS – College Event Management Website

**Academic Web Technology Implementation Project**  
**Team Leader:** Patrick  
**Project Type:** Full-Stack Web Application  
**Technologies:** HTML5, CSS3, JavaScript (ES6), PHP 8+, MySQL (InnoDB / MariaDB), Bootstrap 5  

---

## 📌 Project Overview

**CAMPUS EVENTS** is a modern, centralized university event discovery and registration platform designed to bridge students, faculty coordinators, and campus administrators. It eliminates manual paper-based notices and queue-based event sign-ups by delivering a streamlined, automated, and secure digital management system.

### Tagline
> **Discover. Participate. Celebrate.**

---

## 🎨 Visual Identity & UI/UX Design

The platform adheres to a modern university technology aesthetic:
- **Primary Navy:** `#111827` (Command bars, branding, deep headers)
- **Secondary Royal Blue:** `#2563EB` (Primary buttons, links, highlights)
- **Accent Purple:** `#7C3AED` (Categories, key badges)
- **Background Light Grey:** `#F8FAFC` (Clean page canvas)
- **Cards & Surfaces:** `#FFFFFF` (Elevated card containers with subtle borders and shadows)
- **Typography:** Dark Grey `#1F2937` with Inter font family

---

## 🚀 Key Modules & System Features

### 1. Student Portal & Public Interface
- **Home Page (`index.php`)**:
  - Hero section: *"Where Campus Life Comes Alive"*.
  - Live statistics dynamically pulled from database records (Total Events, Registered Students, Completed Events).
  - Domain Category Directory: Technical Events, Cultural Events, Sports Events, Workshops, Symposiums, College Festivals.
  - Featured Upcoming Highlights and schedule spotlight.
  - "Why Choose Campus Events" benefit cards.
  - Testimonial carousel from student leaders.
- **Events Directory (`events.php`)**:
  - Live search by title, topic, or venue.
  - Category filtering and date horizon filters (Today, This Month, Upcoming, Past).
  - Multi-criteria sorting (Date ascending/descending, Seats available, Newest).
  - Real-time seat occupancy indicators.
  - Empty state with reset filters.
- **Event Details & Registration (`event-details.php`)**:
  - High-resolution event banner, guidelines, and schedule.
  - Faculty coordinator contact cards.
  - Atomic 1-click registration with transaction rollback and seat decrement.
  - Deadline validation and duplicate-booking prevention.
- **Digital Entry Pass / E-Ticket (`ticket.php`)**:
  - Printable verification ticket with unique tracking code (e.g., `REG-2026-X8B9Q`).
  - Simulated high-precision QR code pattern for gate check-in.
  - Attendee verification details (Roll No, Department, Timestamp).
  - Print / Save as PDF capability (`window.print()`).
- **Student Dashboard (`my-registrations.php`)**:
  - View all confirmed and attended event passes.
  - 1-click pass cancellation before event start date (automatically refunds available seats).
- **Profile Management (`profile.php`)**:
  - View and update roll number, phone, department, and academic year.
  - Secure password change with verification of current password.
- **About (`about.php`) & Contact (`contact.php`)**:
  - Academic background, platform architecture, FAQs.
  - Working contact inquiry form storing messages into the database.

---

### 2. Administrator Command Center (`/admin/`)
- **Dashboard Overview (`admin/index.php`)**:
  - Real-time KPI summary cards (Total Events, Active Passes, Registered Students, Seat Occupancy Rate %).
  - Recent student registrations feed.
  - Upcoming events capacity monitoring bars.
- **Event CRUD Operations (`admin/events.php`, `event-create.php`, `event-edit.php`, `event-delete.php`)**:
  - Create new events with title, category, description, venue, seats, deadlines, and image upload.
  - Edit existing event logistics and recalculate available seats.
  - Safe event deletion with cascading registration removal.
  - Toggle published/draft/completed status and featured flags.
- **Registrations & Attendance Roster (`admin/registrations.php`)**:
  - Master attendee registry filterable by event and status.
  - Quick attendance logger: toggle between "Confirmed", "Attended", and "Cancelled".
  - **Export to CSV**: Instant spreadsheet download for gate coordinators.
  - **Print Attendance Sheet**: Clean printable roster.
- **Student Directory (`admin/students.php`)**:
  - View enrolled students, roll numbers, departments, and total event bookings.
  - Activate or suspend student accounts.
- **Inquiries & Support (`admin/messages.php`)**:
  - View inquiries submitted via `contact.php`.
  - Mark inquiries as read/unread or delete them.

---

## 🗄️ Database Architecture

The application uses a relational schema in `campus_events_db`:

1. **`users`**: User authentication, roles (`admin`, `student`), roll numbers, departments, password hashes (`BCRYPT`).
2. **`categories`**: Six university domains with custom slugs, icons, and colors.
3. **`events`**: Event logistics, dates, timings, venue, total/available seats, banner image, coordinator info, status.
4. **`registrations`**: Student-event registrations, unique tracking codes (`REG-2026-XXXXX`), statuses (`confirmed`, `attended`, `cancelled`).
5. **`testimonials`**: Student and faculty feedback.
6. **`contact_messages`**: Contact form inquiries with status tracking.

---

## 🔑 Default Credentials

For quick evaluation and grading:

| Role | Email | Password | Access Area |
|---|---|---|---|
| **Administrator** | `admin@campusevents.edu` | `Admin@123` | Full Admin Command Center (`/admin/`) & Public Site |
| **Student (Patrick)** | `patrick@student.edu` | `Student@123` | Event Registration, My Passes, Profile |
| **Student (Elena)** | `elena.r@student.edu` | `Student@123` | Event Registration, My Passes |

*Tip: The login page includes quick 1-click demo filler buttons to instantly load these credentials.*

---

## 💻 Installation & Setup Guide (XAMPP)

### Step 1: Copy Project to XAMPP
Copy or move the `project` folder to your XAMPP web root:
```
C:\xampp\htdocs\project\
```

### Step 2: Start Apache & MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Initialize Database (Choose Option A or Option B)

#### Option A: One-Click Web Installer (Recommended)
1. Open your web browser and visit:
   ```
   http://localhost/project/setup.php
   ```
2. Click the **"Initialize Database Now"** button. The script will automatically create `campus_events_db`, build all 6 tables, and populate realistic seed events and student accounts.

#### Option B: phpMyAdmin Import
1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Click the **Import** tab.
3. Select the file: `database/schema.sql` located inside the project.
4. Click **Go**.

### Step 4: Open the Website
Navigate to:
```
http://localhost/project/
```

---

## 🛡️ Security Implementation
- **SQL Injection Prevention:** 100% prepared statements using PDO.
- **Cross-Site Scripting (XSS) Prevention:** Output sanitization through `htmlspecialchars()`.
- **Cross-Site Request Forgery (CSRF):** Synchronizer token pattern implemented across all POST forms.
- **Password Security:** One-way password hashing using `password_hash()` and `password_verify()` with `PASSWORD_BCRYPT`.
- **Role-Based Authorization:** `require_login()` and `require_admin()` middlewares protect student dashboards and admin panels.
- **Session Security:** `session.cookie_httponly = 1` and `session.use_only_cookies = 1` enabled.

---

## 📁 Directory Structure
```
c:\xampp\htdocs\project\
├── assets/
│   ├── css/
│   │   ├── style.css          # Main university design stylesheet
│   │   └── admin.css          # Admin panel layout & sidebar
│   ├── js/
│   │   ├── main.js            # Frontend validation, live search, print
│   │   └── admin.js           # Admin interactions, image preview
│   ├── images/
│   │   ├── logo.svg           # Vector branding logo
│   │   ├── events/            # Event banners and posters
│   │   └── avatars/           # User profile avatars
│   ├── uploads/
│   │   └── events/            # Directory for uploaded event posters
│   └── sessions/              # Local session storage
├── admin/
│   ├── includes/
│   │   ├── admin_header.php
│   │   ├── admin_sidebar.php
│   │   └── admin_footer.php
│   ├── index.php              # Admin dashboard & analytics
│   ├── events.php             # Event CRUD list
│   ├── event-create.php       # Add new event
│   ├── event-edit.php         # Modify event details
│   ├── event-delete.php       # Event deletion handler
│   ├── registrations.php      # Attendee roster & CSV export
│   ├── students.php           # Student directory & management
│   └── messages.php           # Inquiries & support messages
├── config/
│   ├── config.php             # Base URL, app constants, session setup
│   └── database.php           # PDO database connection singleton
├── database/
│   └── schema.sql             # SQL schema + comprehensive seed data
├── includes/
│   ├── functions.php          # Core helper functions & auth guards
│   ├── header.php             # Public HTML header & flash alerts
│   ├── navbar.php             # Responsive navigation with user menu
│   └── footer.php             # Footer with links & office details
├── index.php                  # Homepage with hero, stats, & highlights
├── events.php                 # Events directory with live filters
├── event-details.php          # Event details & registration submission
├── login.php                  # Student & Admin sign-in
├── register.php               # Student sign-up with roll no & department
├── logout.php                 # Secure session destroyer
├── my-registrations.php       # Student registrations dashboard
├── ticket.php                 # Printable digital E-Pass with QR code
├── profile.php                # Student profile & password settings
├── about.php                  # Platform mission & technology stack
├── contact.php                # Contact & support inquiry form
├── setup.php                  # One-click database installer & reset
└── README.md                  # Complete project documentation
```

---

## 👨‍💻 Academic Project Acknowledgements
- **Team Leader:** Patrick
- **Course:** Web Technology Implementation
- **Focus Areas:** Full-Stack Web Development, UI/UX Design, Relational Database Modeling, System Testing, and Web Security.

