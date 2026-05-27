PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS private_sessions;
DROP TABLE IF EXISTS trainer_reviews;
DROP TABLE IF EXISTS personal_bookings;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS trainers;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('member', 'trainer', 'admin')),
    profile_photo TEXT DEFAULT 'pfp.png',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    membership_tier TEXT NOT NULL DEFAULT 'basic' CHECK (membership_tier IN ('basic', 'premium')),
    membership_status TEXT NOT NULL DEFAULT 'active' CHECK (membership_status IN ('active', 'inactive')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE trainers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    bio TEXT,
    specializations TEXT,
    certifications TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    created_by INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT NOT NULL,
    trainer_id INTEGER NOT NULL,
    room TEXT,
    capacity INTEGER NOT NULL DEFAULT 20 CHECK (capacity > 0),
    scheduled_at DATETIME NOT NULL,
    duration_min INTEGER NOT NULL DEFAULT 60 CHECK (duration_min > 0),
    is_featured INTEGER NOT NULL DEFAULT 0 CHECK (is_featured IN (0, 1)),
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

CREATE TABLE enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TEXT NOT NULL DEFAULT 'confirmed' CHECK (status IN ('confirmed', 'cancelled', 'waitlisted')),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE(member_id, class_id)
);

CREATE TABLE equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT,
    total_count INTEGER NOT NULL DEFAULT 1 CHECK (total_count >= 0),
    available_count INTEGER NOT NULL DEFAULT 0 CHECK (available_count >= 0),
    status TEXT NOT NULL DEFAULT 'operational' CHECK (status IN ('operational', 'maintenance', 'retired')),
    CHECK (available_count <= total_count)
);

CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE(member_id, class_id)
);

CREATE TABLE trainer_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    trainer_id INTEGER NOT NULL,
    member_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE(trainer_id, member_id)
);

CREATE TABLE private_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    trainer_id INTEGER NOT NULL,
    member_id INTEGER NOT NULL,
    requested_at DATETIME NOT NULL,
    duration_min INTEGER NOT NULL DEFAULT 60 CHECK (duration_min > 0),
    notes TEXT,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'rejected', 'cancelled')),
    trainer_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

INSERT INTO users (username, email, password_hash, role, profile_photo) VALUES
('admin', 'admin@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'pfp.png'),
('trainer1', 'trainer@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'pfp.png'),
('member1', 'member@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'pfp.png');

INSERT INTO admins (user_id) VALUES (1);

INSERT INTO trainers (user_id, full_name, bio, specializations, certifications) VALUES
(2, 'John Doe', 'Certified trainer focused on mobility, strength, and beginner-friendly programming.', 'Yoga, Pilates', 'ACE, NASM');

INSERT INTO members (user_id, full_name, membership_tier, membership_status) VALUES
(3, 'Anna Kowalska', 'basic', 'active');

INSERT INTO classes (name, type, trainer_id, room, capacity, scheduled_at, duration_min, is_featured) VALUES
('Power HIIT', 'HIIT', 1, 'Studio A', 20, '2026-06-01 18:00:00', 45, 1),
('Morning Zen', 'Yoga', 1, 'Studio B', 15, '2026-06-02 08:00:00', 60, 0);

INSERT INTO equipment (name, category, total_count, available_count, status) VALUES
('Treadmill X5', 'Cardio', 10, 7, 'operational'),
('Bench Press', 'Weights', 5, 2, 'operational'),
('Leg Press Machine', 'Strength', 3, 2, 'maintenance');
