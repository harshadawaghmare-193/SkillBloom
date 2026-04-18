-- ════════════════════════════════════════════
-- SkillBloom Database — Fixed for WAMP/MySQL 5.x
-- Fix: VARCHAR lengths reduced to stay under 767 byte key limit
-- Use ROW_FORMAT=DYNAMIC to support longer keys if needed
-- ════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS skillbloom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbloom;

-- Allow longer index keys (run this if you get key-too-long errors)
SET GLOBAL innodb_large_prefix = ON;
SET GLOBAL innodb_file_format = Barracuda;
SET GLOBAL innodb_file_per_table = ON;

-- ── Categories ──
CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(100) UNIQUE NOT NULL,
  icon        VARCHAR(20)  DEFAULT '📚',
  description TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Users ──
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  email      VARCHAR(191) NOT NULL,          -- 191 = max safe for utf8mb4 unique index
  password   VARCHAR(255) NOT NULL,
  phone      VARCHAR(20),
  bio        TEXT,
  status     ENUM('active','banned') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Admins ──
CREATE TABLE admins (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  email      VARCHAR(191) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Courses ──
CREATE TABLE courses (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  title       VARCHAR(255) NOT NULL,
  slug        VARCHAR(255) NOT NULL,
  description TEXT,
  short_desc  VARCHAR(500),
  thumbnail   VARCHAR(255),
  instructor  VARCHAR(150),
  price       DECIMAL(10,2) DEFAULT 0,
  old_price   DECIMAL(10,2) DEFAULT 0,
  hours       DECIMAL(5,1)  DEFAULT 0,
  level       ENUM('Beginner','Intermediate','Advanced','All Levels') DEFAULT 'All Levels',
  status      ENUM('draft','published') DEFAULT 'draft',
  featured    TINYINT(1) DEFAULT 0,
  students    INT DEFAULT 0,
  rating      DECIMAL(3,2) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_slug (slug),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Videos ──
CREATE TABLE videos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  course_id    INT NOT NULL,
  title        VARCHAR(255) NOT NULL,
  description  TEXT,
  video_url    VARCHAR(500),
  duration_min INT DEFAULT 0,
  sort_order   INT DEFAULT 0,
  is_preview   TINYINT(1) DEFAULT 0,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Enrollments ──
CREATE TABLE enrollments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  course_id   INT NOT NULL,
  progress    INT DEFAULT 0,
  status      ENUM('active','completed') DEFAULT 'active',
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enroll (user_id, course_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Payments ──
CREATE TABLE payments (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  course_id  INT NOT NULL,
  amount     DECIMAL(10,2) NOT NULL,
  method     VARCHAR(50),
  txn_id     VARCHAR(191),
  status     ENUM('pending','success','failed') DEFAULT 'pending',
  paid_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Certificates ──
CREATE TABLE certificates (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  course_id  INT NOT NULL,
  cert_no    VARCHAR(60) NOT NULL,
  issued_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cert_no   (cert_no),
  UNIQUE KEY uq_cert      (user_id, course_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Video Progress ──
CREATE TABLE video_progress (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT NOT NULL,
  video_id  INT NOT NULL,
  completed TINYINT(1) DEFAULT 0,
  UNIQUE KEY uq_vp (user_id, video_id),
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ── Feedback / Messages ──
CREATE TABLE IF NOT EXISTS feedback (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  name       VARCHAR(150),
  subject    VARCHAR(191),
  type       VARCHAR(60)  DEFAULT 'General',
  rating     TINYINT      DEFAULT 5,
  message    TEXT,
  status     ENUM('new','read','resolved') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ════════════════════════════════════════════
-- SEED DATA
-- ════════════════════════════════════════════

INSERT INTO categories (name, slug, icon, description) VALUES
('Web Development',  'web-development',  '💻', 'HTML, CSS, JavaScript, PHP & MySQL'),
('Data Science',     'data-science',     '📊', 'Python, Machine Learning, AI & Analytics'),
('UI/UX Design',     'ui-ux-design',     '🎨', 'Figma, Adobe XD, Prototyping & User Research'),
('Business',         'business',         '💼', 'Digital Marketing, Sales & Management'),
('Mobile Apps',      'mobile-apps',      '📱', 'Flutter, React Native, iOS & Android'),
('Cybersecurity',    'cybersecurity',    '🔐', 'Ethical Hacking & Network Security');

-- Default admin (password: password)
INSERT INTO admins (name, email, password) VALUES
('Admin', 'admin@skillbloom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO courses (category_id, title, slug, short_desc, instructor, price, old_price, hours, level, status, featured, students, rating) VALUES
(1, 'Complete Web Development Bootcamp',   'complete-web-dev-bootcamp',  'Master HTML, CSS, JS, PHP & MySQL from scratch',           'Dr. Sarah Johnson', 999,  4999, 65, 'Beginner',     'published', 1, 1250, 4.8),
(2, 'Python for Data Science & ML',        'python-data-science-ml',     'Learn Python, Pandas, Scikit-learn & Matplotlib',          'Prof. Michael Chen',1199, 5999, 42, 'Intermediate', 'published', 1,  980, 4.7),
(3, 'UI/UX Design Masterclass',            'ui-ux-design-masterclass',   'Design beautiful interfaces using Figma & Adobe XD',       'Emily Rodriguez',   799,  2999, 28, 'Beginner',     'published', 1,  750, 4.9),
(4, 'Digital Marketing A-Z',               'digital-marketing-az',       'SEO, Social Media, Email Marketing & Google Ads',          'James Williams',    699,  2499, 35, 'All Levels',   'published', 0, 1100, 4.6),
(5, 'Flutter Mobile App Development',      'flutter-mobile-development', 'Build iOS & Android apps with Flutter & Dart',             'Priya Patel',      1099,  3999, 48, 'Intermediate', 'published', 1,  620, 4.8),
(6, 'Ethical Hacking & Cybersecurity',     'ethical-hacking-security',   'Penetration testing & network security fundamentals',      'Alex Thompson',    1299,  5499, 55, 'Advanced',     'published', 0,  890, 4.7);

INSERT INTO videos (course_id, title, duration_min, sort_order, is_preview, video_url) VALUES
(1, 'Introduction to Web Development',    8,  1, 1, 'https://www.youtube.com/watch?v=qz0aGYrrlhU'),
(1, 'HTML Basics - Structure & Tags',    15,  2, 0, 'https://www.youtube.com/watch?v=pQN-pnXPaVg'),
(1, 'CSS Styling & Selectors',           18,  3, 0, 'https://www.youtube.com/watch?v=1Rs2ND1ryYc'),
(1, 'JavaScript Fundamentals',           25,  4, 0, 'https://www.youtube.com/watch?v=W6NZfCO5SIk'),
(1, 'PHP for Beginners',                 30,  5, 0, 'https://www.youtube.com/watch?v=BUCiSSyIGGU'),
(1, 'MySQL Database Basics',             22,  6, 0, 'https://www.youtube.com/watch?v=7S_tz1z_5bA'),
(2, 'Python Setup & Basics',             15,  1, 1, 'https://www.youtube.com/watch?v=_uQrJ0TkZlc'),
(2, 'Python Data Structures',            20,  2, 0, 'https://www.youtube.com/watch?v=R-HLU9Fl5ug'),
(2, 'NumPy & Pandas Tutorial',           30,  3, 0, 'https://www.youtube.com/watch?v=vmEHCJofslg'),
(2, 'Data Visualization - Matplotlib',   25,  4, 0, 'https://www.youtube.com/watch?v=a9UrKTVEeZA'),
(2, 'Machine Learning Introduction',     35,  5, 0, 'https://www.youtube.com/watch?v=NWONeJKn9Kc'),
(3, 'Design Principles & Theory',        10,  1, 1, 'https://www.youtube.com/watch?v=wIuVvCuiOo0'),
(3, 'Figma Getting Started',             25,  2, 0, 'https://www.youtube.com/watch?v=FTFaQWZBqQ8'),
(3, 'Wireframing & Prototyping',         20,  3, 0, 'https://www.youtube.com/watch?v=8-vTd7GRk-w'),
(4, 'Digital Marketing Overview',        12,  1, 1, 'https://www.youtube.com/watch?v=bixR-KIJKYM'),
(4, 'SEO Fundamentals',                  28,  2, 0, 'https://www.youtube.com/watch?v=DvwS7cV9GmQ'),
(4, 'Google Ads Tutorial',               22,  3, 0, 'https://www.youtube.com/watch?v=CpDhXbXj4sY'),
(5, 'Flutter & Dart Setup',              15,  1, 1, 'https://www.youtube.com/watch?v=1ukSR1GRtMU'),
(5, 'Dart Programming Basics',           20,  2, 0, 'https://www.youtube.com/watch?v=Ej_Pcr4uC2Q'),
(5, 'Building Your First Flutter App',   30,  3, 0, 'https://www.youtube.com/watch?v=x0uinJvhNxI'),
(6, 'Intro to Ethical Hacking',          18,  1, 1, 'https://www.youtube.com/watch?v=3Kq1MIfTWCE'),
(6, 'Network Security Basics',           25,  2, 0, 'https://www.youtube.com/watch?v=qiQR5rTSshw'),
(6, 'Penetration Testing 101',           32,  3, 0, 'https://www.youtube.com/watch?v=WnN6dbos5u8');
