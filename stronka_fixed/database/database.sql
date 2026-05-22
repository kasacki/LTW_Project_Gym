-- Ustawienia SQLite
PRAGMA foreign_keys = ON;

-- 1. GŁÓWNA TABELA UŻYTKOWNIKÓW I AUTH
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR NOT NULL UNIQUE,
    email VARCHAR NOT NULL UNIQUE,
    password_hash VARCHAR NOT NULL,
    role VARCHAR NOT NULL CHECK (role IN ('member', 'trainer', 'admin')),
    profile_photo VARCHAR DEFAULT 'pfp.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. PROFILE (ROZSZERZENIE TABELI USERS)
CREATE TABLE members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE,
    full_name VARCHAR NOT NULL,
    membership_tier VARCHAR DEFAULT 'basic' CHECK (membership_tier IN ('basic', 'premium')), -- Nowe: Membership Plans
    membership_status VARCHAR DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE trainers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE,
    full_name VARCHAR NOT NULL,
    bio TEXT,
    specializations VARCHAR,
    certifications VARCHAR,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE,
    created_by INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- 3. HARMONOGRAM (SCHEDULE)
CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL,
    type VARCHAR, -- Yoga, HIIT, itp.
    trainer_id INTEGER,
    room VARCHAR,
    capacity INTEGER,
    scheduled_at DATETIME NOT NULL,
    duration_min INTEGER,
    is_featured BOOLEAN DEFAULT 0, -- Nowe: Promotional Features
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

CREATE TABLE enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER,
    class_id INTEGER,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR DEFAULT 'confirmed',
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    UNIQUE(member_id, class_id) -- Blokada podwójnego zapisu
);

-- 4. SPRZĘT (GYM FLOOR)
CREATE TABLE equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL,
    category VARCHAR,
    total_count INTEGER,
    available_count INTEGER,
    status VARCHAR DEFAULT 'operational',
    FOREIGN KEY (id) REFERENCES equipment(id)
);

-- 5. OPINIE (FEEDBACK)
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER,
    class_id INTEGER,
    rating INTEGER CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- 6. REZERWACJE PERSONALNE (EXTRA FEATURE)
CREATE TABLE personal_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER,
    trainer_id INTEGER,
    booking_date DATETIME,
    status VARCHAR DEFAULT 'pending',
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

-- DANE TESTOWE (Hasło: '1234' zahashowane)
INSERT INTO users (username, email, password_hash, role) VALUES 
('admin', 'admin@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('trainer1', 'trainer@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('member1', 'member@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

INSERT INTO admins (user_id) VALUES (1);
INSERT INTO trainers (user_id, full_name, specializations) VALUES (2, 'John Doe', 'Yoga, Pilates');
INSERT INTO members (user_id, full_name, membership_tier) VALUES (3, 'Anna Kowalska', 'basic');

INSERT INTO classes (name, type, trainer_id, capacity, scheduled_at, is_featured) VALUES 
('Power HIIT', 'HIIT', 1, 20, '2024-06-01 18:00:00', 1),
('Morning Zen', 'Yoga', 1, 15, '2024-06-02 08:00:00', 0);

INSERT INTO equipment (name, category, total_count, available_count) VALUES 
('Treadmill X5', 'Cardio', 10, 7),
('Bench Press', 'Weights', 5, 2);