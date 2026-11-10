-- ========================================================
-- CAMPUS EVENTS - College Event Management System
-- Academic Web Technology Implementation Project
-- Team Leader: Patrick
-- Database Schema and Initial Seed Data
-- ========================================================

CREATE DATABASE IF NOT EXISTS `campus_events_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campus_events_db`;

-- Drop tables in reverse order if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Users Table (Administrators and Students)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `roll_no` VARCHAR(50) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `year_of_study` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Categories Table
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `color` VARCHAR(20) NOT NULL DEFAULT '#2563EB',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Events Table
-- --------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `short_description` VARCHAR(300) NOT NULL,
  `description` TEXT NOT NULL,
  `banner_image` VARCHAR(255) DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME DEFAULT NULL,
  `venue` VARCHAR(150) NOT NULL,
  `registration_deadline` DATE NOT NULL,
  `total_seats` INT NOT NULL DEFAULT 100,
  `available_seats` INT NOT NULL DEFAULT 100,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('published', 'draft', 'completed', 'cancelled') NOT NULL DEFAULT 'published',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `coordinator_name` VARCHAR(100) NOT NULL,
  `coordinator_email` VARCHAR(150) NOT NULL,
  `coordinator_phone` VARCHAR(20) NOT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_events_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_event_date` (`event_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Registrations Table
-- --------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `registration_code` VARCHAR(50) NOT NULL UNIQUE,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('confirmed', 'cancelled', 'attended') NOT NULL DEFAULT 'confirmed',
  `notes` TEXT DEFAULT NULL,
  `attended_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unique_user_event` UNIQUE (`user_id`, `event_id`),
  INDEX `idx_reg_code` (`registration_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Testimonials Table
-- --------------------------------------------------------
CREATE TABLE `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `quote` TEXT NOT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Contact Messages Table
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `color`) VALUES
(1, 'Technical Events', 'technical-events', 'bi-code-slash', 'Coding competitions, hackathons, robotics, and tech showcases.', '#2563EB'),
(2, 'Cultural Events', 'cultural-events', 'bi-palette', 'Music, dance, theatre, fine arts, and literary performances.', '#7C3AED'),
(3, 'Sports Events', 'sports-events', 'bi-trophy', 'Inter-department and inter-college athletics and team sports tournaments.', '#059669'),
(4, 'Workshops', 'workshops', 'bi-tools', 'Hands-on training, industry masterclasses, and skill development sessions.', '#D97706'),
(5, 'Symposiums', 'symposiums', 'bi-mortarboard', 'Academic research conferences, paper presentations, and keynote talks.', '#DC2626'),
(6, 'College Festivals', 'college-festivals', 'bi-stars', 'Grand annual university celebrations, star nights, and college galas.', '#4F46E5');

-- Insert Users (Admin password is 'Admin@123', Student password is 'Student@123')
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `roll_no`, `phone`, `department`, `year_of_study`, `avatar`, `status`) VALUES
(1, 'System Administrator', 'admin@campusevents.edu', '$2y$10$yuwe5xwq56KrjDJx6SFuT.OysKXU/hWoozGQIQe3MW4rgqOAD7RAC', 'admin', 'ADM-001', '+1 (555) 019-2831', 'Administration', 'Staff', 'assets/images/avatars/admin.png', 'active'),
(2, 'Patrick (Team Leader)', 'patrick@student.edu', '$2y$10$hlRc7aR7tH1Tm/HCUNt/geDDVzEpOuNQiT5ljkWqASBCghreWZg.C', 'student', 'CS2023-042', '+1 (555) 012-3456', 'Computer Science & Engineering', '3rd Year', 'assets/images/avatars/student1.png', 'active'),
(3, 'Elena Rodriguez', 'elena.r@student.edu', '$2y$10$hlRc7aR7tH1Tm/HCUNt/geDDVzEpOuNQiT5ljkWqASBCghreWZg.C', 'student', 'EC2023-018', '+1 (555) 014-9988', 'Electronics & Communication', '3rd Year', 'assets/images/avatars/student2.png', 'active'),
(4, 'Marcus Chen', 'marcus.c@student.edu', '$2y$10$hlRc7aR7tH1Tm/HCUNt/geDDVzEpOuNQiT5ljkWqASBCghreWZg.C', 'student', 'ME2024-105', '+1 (555) 018-7722', 'Mechanical Engineering', '2nd Year', 'assets/images/avatars/student3.png', 'active'),
(5, 'Aisha Patel', 'aisha.p@student.edu', '$2y$10$hlRc7aR7tH1Tm/HCUNt/geDDVzEpOuNQiT5ljkWqASBCghreWZg.C', 'student', 'BA2022-009', '+1 (555) 016-4433', 'Business Administration', '4th Year', 'assets/images/avatars/student4.png', 'active');

-- Insert Events (Mix of upcoming, featured, and past)
INSERT INTO `events` (`id`, `category_id`, `title`, `slug`, `short_description`, `description`, `banner_image`, `event_date`, `start_time`, `end_time`, `venue`, `registration_deadline`, `total_seats`, `available_seats`, `price`, `status`, `is_featured`, `coordinator_name`, `coordinator_email`, `coordinator_phone`, `created_by`) VALUES
(1, 1, 'HackInnovate 2026: 36-Hour National Hackathon', 'hackinnovate-2026-national-hackathon', 'Build groundbreaking AI, web, and hardware solutions in a high-intensity 36-hour sprint.', 'HackInnovate 2026 is our flagship inter-university hackathon bringing together over 200 passionate student developers, designers, and innovators. Tackle real-world challenges in AI/ML, FinTech, Clean Energy, and Cyber Security. Winners receive cash prizes exceeding $10,000, mentorship with industry leaders, and direct interview opportunities.\n\nKey Highlights:\n• 36 hours of non-stop innovation\n• Cloud credits and hardware test kits provided\n• Industry mentors from Google, Microsoft, and AWS\n• Complimentary meals, beverages, and sleeping zones\n• Exciting swag and certification for all finalists.', 'assets/images/events/hackathon.jpg', '2026-11-20', '09:00:00', '21:00:00', 'Innovation Center & Tech Lab Hall A', '2026-11-18', 120, 117, 0.00, 'published', 1, 'Dr. Sarah Jenkins', 'sjenkins@campusevents.edu', '+1 (555) 901-2233', 1),

(2, 2, 'Tarang 2026: Annual Inter-College Cultural Gala', 'tarang-2026-annual-cultural-gala', 'The most vibrant celebration of university music, classical dance, drama, and fine arts.', 'Tarang 2026 represents the pinnacle of campus creative expression. Spanning 3 electric days, the festival showcases the greatest collegiate talent in solo and band music, contemporary & classical choreography, theatrical skits, and live photography competitions.\n\nSchedule Overview:\n• Day 1: Classical Dance Ensemble & Battle of the Bands\n• Day 2: Theatrical Drama, Poetry Slams & Street Play Showcase\n• Day 3: Grand Celebrity Star Night and Awards Ceremony\n\nDress Code: Ethnic / Creative Festive wear encouraged.', 'assets/images/events/cultural.jpg', '2026-11-25', '17:00:00', '22:30:00', 'University Open Air Amphitheatre', '2026-11-23', 500, 498, 0.00, 'published', 1, 'Prof. Robert Taylor', 'rtaylor@campusevents.edu', '+1 (555) 901-4455', 1),

(3, 3, 'Campus Clash: Inter-Departmental Basketball Tournament', 'campus-clash-basketball-tournament', 'Witness intense court rivalries as 16 department teams compete for championship glory.', 'Get ready for fast-paced action at the annual Campus Clash Basketball Championship! Departments go head-to-head in a knockout bracket tournament. Cheer on your classmates, enjoy half-time performances by the cheer squad, and experience college athletic spirit at its highest level.\n\nFormat:\n• 16 Teams Knockout Tournament\n• Official FIBA rules enforced by certified referees\n• Trophies, medals, and Most Valuable Player honors\n• Live score tracking and streaming on the campus portal.', 'assets/images/events/sports.jpg', '2026-11-15', '15:30:00', '20:00:00', 'Sports Arena - Main Indoor Court', '2026-11-14', 150, 149, 0.00, 'published', 1, 'Coach David Vance', 'dvance@campusevents.edu', '+1 (555) 901-7788', 1),

(4, 4, 'Full-Stack Web Architectures & Cloud Deployment Workshop', 'full-stack-web-cloud-workshop', 'Practical hands-on workshop covering production PHP, REST APIs, Docker, and CI/CD.', 'Step beyond the basics in this intensive 4-hour hands-on workshop designed for aspiring software engineers. Learn how modern enterprise web applications are structured, secured, and deployed to production cloud environments.\n\nPrerequisites & Requirements:\n• Bring a laptop with VS Code and Git installed\n• Familiarity with basic HTML, CSS, and backend concepts\n• Docker Desktop installed prior to arrival\n• Digital verified certificate issued upon project completion.', 'assets/images/events/workshop.jpg', '2026-11-18', '10:00:00', '14:00:00', 'Computer Lab 3, Turing Block', '2026-11-17', 60, 58, 0.00, 'published', 1, 'Prof. Alan Turing Jr.', 'aturing@campusevents.edu', '+1 (555) 901-3321', 1),

(5, 5, 'National Symposium on Artificial Intelligence & Ethics', 'national-symposium-ai-ethics-2026', 'Distinguished scholars and research engineers discuss the responsible frontiers of generative AI.', 'The Department of Computer Science invites researchers, undergraduate, and postgraduate scholars to the 2026 National Symposium on AI & Ethics. Featuring keynote presentations by prominent AI ethicists, accepted research paper presentations, and an interactive town hall session.\n\nTracks Covered:\n• Bias mitigation in Large Language Models\n• Autonomous Systems and Safety Verification\n• Copyright, IP, and AI generated works\n• Published symposium proceedings indexed with ISBN.', 'assets/images/events/symposium.jpg', '2026-12-05', '09:30:00', '16:30:00', 'Sir C.V. Raman Auditorium', '2026-12-01', 250, 249, 0.00, 'published', 0, 'Dr. Kimberly Adams', 'kadams@campusevents.edu', '+1 (555) 901-8899', 1),

(6, 6, 'AuraFest 2026: The Grand Spring Carnival', 'aurafest-2026-grand-spring-carnival', 'Food trucks, arcade challenges, art exhibits, student flea markets, and live acoustic music.', 'AuraFest is our college community celebration bringing together students, faculty, alumni, and local culinary partners. Explore over 40 student-run pop-up shops, gourmet food trucks, gaming arenas, escape rooms, and a twilight acoustic stage featuring campus indie artists.\n\nEntry is open to all university ID cardholders. Bring your peers and celebrate the vibrancy of student campus life!', 'assets/images/events/carnival.jpg', '2026-12-12', '11:00:00', '21:00:00', 'Central Quadrangle & Student Union Lawn', '2026-12-10', 800, 799, 0.00, 'published', 1, 'Emily Parker', 'eparker@campusevents.edu', '+1 (555) 901-5566', 1),

(7, 1, 'CyberGuard: CTF (Capture The Flag) Challenge', 'cyberguard-ctf-ethical-hacking-challenge', 'Test your ethical hacking and cybersecurity prowess in reverse engineering, cryptography, and forensics.', 'Compete in an intense jeopardy-style cybersecurity tournament. Participants will decrypt encrypted communications, exploit web vulnerabilities in sandboxed containers, analyze binary vulnerabilities, and recover forensic artifacts.\n\nCategories:\n• Web Exploitation\n• Cryptography & Steganography\n• Reverse Engineering & Binary Exploitation\n• Digital Forensics & OSINT', 'assets/images/events/cybersecurity.jpg', '2026-11-28', '13:00:00', '18:00:00', 'Advanced Cyber Security Lab', '2026-11-27', 75, 75, 0.00, 'published', 0, 'Dr. Samuel Vance', 'svance@campusevents.edu', '+1 (555) 901-6677', 1),

(8, 4, 'Robotics & Embedded Systems with ESP32 & IoT', 'robotics-embedded-systems-iot-workshop', 'Hands-on hardware lab building real-time telemetry robots connected to MQTT cloud broker.', 'An immersive workshop providing every participant with a dual-core ESP32 microcontroller kit, sensor modules, and motor drivers. Learn embedded C programming, sensor fusion, wireless communication protocols, and cloud IoT dashboard integration.', 'assets/images/events/robotics.jpg', '2026-12-02', '10:00:00', '15:30:00', 'Mechatronics Workshop Bay 2', '2026-11-30', 50, 50, 0.00, 'published', 0, 'Prof. Neil Armstrong', 'narmstrong@campusevents.edu', '+1 (555) 901-1122', 1),

(9, 1, 'Autumn Algorithmic Invitational (Past Event)', 'autumn-algorithmic-invitational-2026', 'Inter-collegiate speed programming competition following ICPC format.', 'Annual competitive programming contest with 8 complex algorithmic challenges across graphs, dynamic programming, and computational geometry.', 'assets/images/events/coding.jpg', '2026-10-15', '14:00:00', '17:00:00', 'Central Computing Facility', '2026-10-14', 100, 12, 0.00, 'completed', 0, 'Dr. Sarah Jenkins', 'sjenkins@campusevents.edu', '+1 (555) 901-2233', 1);

-- Insert Sample Registrations
INSERT INTO `registrations` (`id`, `event_id`, `user_id`, `registration_code`, `registered_at`, `status`, `notes`) VALUES
(1, 1, 2, 'REG-2026-HACK01', '2026-11-01 10:15:00', 'confirmed', 'Team lead for Apollo 11 Devs. Dietary preference: Vegetarian.'),
(2, 2, 2, 'REG-2026-TARG02', '2026-11-02 14:22:00', 'confirmed', 'Attending acoustic evening session.'),
(3, 4, 2, 'REG-2026-WS03', '2026-11-03 09:40:00', 'confirmed', 'Interested in cloud deployment pipelines.'),
(4, 1, 3, 'REG-2026-HACK04', '2026-11-02 11:10:00', 'confirmed', 'Interested in hardware IoT track.'),
(5, 3, 4, 'REG-2026-SPRT05', '2026-11-04 16:05:00', 'confirmed', 'Mechanical Engineering forward player.'),
(6, 6, 5, 'REG-2026-FEST06', '2026-11-05 18:30:00', 'confirmed', 'Volunteer coordinator for culinary stalls.');

-- Insert Testimonials
INSERT INTO `testimonials` (`id`, `student_name`, `department`, `quote`, `rating`, `avatar`, `is_active`) VALUES
(1, 'Patrick', 'Computer Science & Engineering', 'Campus Events completely transformed how we find and participate in collegiate hackathons. Everything from instant digital passes to real-time seat alerts is seamless!', 5, 'assets/images/avatars/student1.png', 1),
(2, 'Elena Rodriguez', 'Electronics & Communication', 'Registering for university workshops used to mean standing in line at the department notice board. Now I can discover upcoming technical symposiums with one click.', 5, 'assets/images/avatars/student2.png', 1),
(3, 'Marcus Chen', 'Mechanical Engineering', 'The sports clash registrations and schedule notifications kept our department team organized. A truly professional platform worthy of our university.', 5, 'assets/images/avatars/student3.png', 1),
(4, 'Aisha Patel', 'Business Administration', 'As an event organizer, tracking attendee check-ins and managing seats through the admin portal saved our team dozens of hours of manual paperwork.', 5, 'assets/images/avatars/student4.png', 1);

-- Insert Sample Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`) VALUES
('1', 'Jessica Miller', 'jmiller@student.edu', 'Query about HackInnovate team composition', 'Hello, can students from different academic years form a combined team for the 36-hour hackathon? Thank you!', 'unread'),
('2', 'Dr. Kenneth Cole', 'kcole@partnerorg.org', 'Sponsorship proposal for Tarang 2026', 'Good day, we represent a tech venture fund interested in sponsoring the upcoming college cultural fest prizes.', 'read');

