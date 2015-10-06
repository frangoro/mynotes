-- Scripts de MySQL para la aplicación misNotas
-- DDL
CREATE DATABASE IF NOT EXISTS misNotas;

CREATE TABLE notes (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, title TINYTEXT, note TEXT, priority INT(1) UNSIGNED, start_date TIMESTAMP, end_date TIMESTAMP, estimation INT(3) UNSIGNED);

-- DML
INSERT INTO notes (title, note, priority, start_date, end_date, estimation) VALUES ("Welcome!", "This is my first note.", 0, NOW(), NULL, 0);
