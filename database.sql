
CREATE DATABASE viet_cinema
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE viet_cinema;

-- Bảng thể loại phim
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng phim
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    director VARCHAR(150),
    duration INT NOT NULL,
    release_date DATE,
    poster VARCHAR(255),
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movie_category
    FOREIGN KEY (category_id)
    REFERENCES categories(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- Dữ liệu mẫu thể loại
INSERT INTO categories (name, description) VALUES
('Hành động', 'Phim hành động'),
('Kinh dị', 'Phim kinh dị'),
('Tình cảm', 'Phim tình cảm'),
('Hoạt hình', 'Phim hoạt hình');

-- Dữ liệu mẫu phim
INSERT INTO movies
(title, description, director, duration, release_date, poster, category_id)
VALUES
('Avengers: Endgame', 'Biệt đội siêu anh hùng', 'Anthony Russo', 181, '2019-04-26', '', 1),
('Conjuring', 'Bộ phim kinh dị nổi tiếng', 'James Wan', 112, '2013-07-19', '', 2);
