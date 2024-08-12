﻿--
-- Script was generated by Devart dbForge Studio 2022 for MySQL, Version 9.1.21.0
-- Product home page: http://www.devart.com/dbforge/mysql/studio
-- Script date 12.08.2024 22:19:20
-- Server version: 10.4.32
-- Client version: 4.1
--

-- 
-- Disable foreign keys
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Set SQL mode
-- 
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 
-- Set character set the client will use to send SQL statements to the server
--
SET NAMES 'utf8';

--
-- Set default database
--
USE `web-player`;

--
-- Drop table `check_video`
--
DROP TABLE IF EXISTS check_video;

--
-- Drop table `comments`
--
DROP TABLE IF EXISTS comments;

--
-- Drop table `history_views`
--
DROP TABLE IF EXISTS history_views;

--
-- Drop table `marker`
--
DROP TABLE IF EXISTS marker;

--
-- Drop table `type_video`
--
DROP TABLE IF EXISTS type_video;

--
-- Drop table `video_likes`
--
DROP TABLE IF EXISTS video_likes;

--
-- Drop table `video_views`
--
DROP TABLE IF EXISTS video_views;

--
-- Drop table `video`
--
DROP TABLE IF EXISTS video;

--
-- Drop table `themes_video`
--
DROP TABLE IF EXISTS themes_video;

--
-- Drop table `bookmarks`
--
DROP TABLE IF EXISTS bookmarks;

--
-- Drop table `subscriptions`
--
DROP TABLE IF EXISTS subscriptions;

--
-- Drop table `users`
--
DROP TABLE IF EXISTS users;

--
-- Set default database
--
USE `web-player`;

--
-- Create table `users`
--
CREATE TABLE users (
  idUsers bigint(20) NOT NULL AUTO_INCREMENT,
  nameUsers varchar(50) NOT NULL,
  passUsers varchar(255) NOT NULL,
  roleUsers varchar(255) NOT NULL DEFAULT 'Пользователь',
  PRIMARY KEY (idUsers)
)
ENGINE = INNODB,
AUTO_INCREMENT = 13,
AVG_ROW_LENGTH = 4096,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `nameUsers` on table `users`
--
ALTER TABLE users
ADD UNIQUE INDEX nameUsers (nameUsers);

--
-- Create table `subscriptions`
--
CREATE TABLE subscriptions (
  idSubscriptions bigint(20) NOT NULL AUTO_INCREMENT,
  idUsers bigint(20) NOT NULL,
  subscribedId bigint(20) NOT NULL,
  subscriptionDate date NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (idSubscriptions)
)
ENGINE = INNODB,
AUTO_INCREMENT = 6,
AVG_ROW_LENGTH = 4096,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create foreign key
--
ALTER TABLE subscriptions
ADD CONSTRAINT FK_subscriptions_idUsers FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE subscriptions
ADD CONSTRAINT FK_subscriptions_subscribedId FOREIGN KEY (subscribedId)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create table `bookmarks`
--
CREATE TABLE bookmarks (
  idBookmarks bigint(20) NOT NULL AUTO_INCREMENT,
  idUsers bigint(20) NOT NULL,
  video_path varchar(255) NOT NULL,
  bookmark float DEFAULT NULL,
  PRIMARY KEY (idBookmarks)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `FK_bookmarks_idUsers` on table `bookmarks`
--
ALTER TABLE bookmarks
ADD INDEX FK_bookmarks_idUsers (idUsers);

--
-- Create foreign key
--
ALTER TABLE bookmarks
ADD CONSTRAINT FK_bookmarks_idUsers2 FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create table `themes_video`
--
CREATE TABLE themes_video (
  id_themes_video int(11) NOT NULL AUTO_INCREMENT,
  name_themes varchar(255) NOT NULL,
  PRIMARY KEY (id_themes_video)
)
ENGINE = INNODB,
AUTO_INCREMENT = 10,
AVG_ROW_LENGTH = 2048,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `video`
--
CREATE TABLE video (
  idVideo bigint(20) NOT NULL AUTO_INCREMENT,
  nameVideo varchar(150) NOT NULL,
  urlVideo varchar(50) NOT NULL,
  authorsID bigint(20) NOT NULL,
  uploadDate datetime NOT NULL DEFAULT current_timestamp,
  id_themes_video int(11) DEFAULT NULL,
  description text DEFAULT NULL,
  PRIMARY KEY (idVideo)
)
ENGINE = INNODB,
AUTO_INCREMENT = 15,
AVG_ROW_LENGTH = 3276,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `FK_video_author's ID` on table `video`
--
ALTER TABLE video
ADD INDEX `FK_video_author's ID` (authorsID);

--
-- Create foreign key
--
ALTER TABLE video
ADD CONSTRAINT FK_video_authorsID FOREIGN KEY (authorsID)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE video
ADD CONSTRAINT `FK_video_id_themes_ video` FOREIGN KEY (id_themes_video)
REFERENCES themes_video (id_themes_video) ON DELETE CASCADE;

--
-- Create table `video_views`
--
CREATE TABLE video_views (
  idVideoViews bigint(20) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) NOT NULL,
  idUsers bigint(20) DEFAULT NULL,
  ip_address varchar(45) NOT NULL,
  viewed_at timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (idVideoViews)
)
ENGINE = INNODB,
AUTO_INCREMENT = 45,
AVG_ROW_LENGTH = 176,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create foreign key
--
ALTER TABLE video_views
ADD CONSTRAINT FK_video_views_idVideo FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `video_likes`
--
CREATE TABLE video_likes (
  idvideo_likes bigint(20) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) NOT NULL,
  idUsers bigint(20) NOT NULL,
  likeType enum ('like', 'dislike') NOT NULL,
  dateTime datetime DEFAULT current_timestamp,
  PRIMARY KEY (idvideo_likes)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create foreign key
--
ALTER TABLE video_likes
ADD CONSTRAINT FK_video_likes_idUsers FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE video_likes
ADD CONSTRAINT FK_video_likes_idVideo FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `type_video`
--
CREATE TABLE type_video (
  idtype_video int(11) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) NOT NULL,
  nameType varchar(255) NOT NULL DEFAULT 'Открытый доступ',
  PRIMARY KEY (idtype_video)
)
ENGINE = INNODB,
AUTO_INCREMENT = 20,
AVG_ROW_LENGTH = 2048,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create foreign key
--
ALTER TABLE type_video
ADD CONSTRAINT FK_type_video_idVideo FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `marker`
--
CREATE TABLE marker (
  id_marker int(11) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) DEFAULT NULL,
  time_marker time DEFAULT NULL,
  name_marker varchar(255) DEFAULT NULL,
  image_name varchar(255) DEFAULT NULL,
  PRIMARY KEY (id_marker)
)
ENGINE = INNODB,
AUTO_INCREMENT = 8,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create foreign key
--
ALTER TABLE marker
ADD CONSTRAINT FK_marker_idVideo FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `history_views`
--
CREATE TABLE history_views (
  idhistory_views bigint(20) NOT NULL AUTO_INCREMENT,
  idUsers bigint(20) NOT NULL,
  idVideo bigint(20) NOT NULL,
  viewed_at datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (idhistory_views)
)
ENGINE = INNODB,
AUTO_INCREMENT = 15,
AVG_ROW_LENGTH = 2048,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `FK_history_views_idUsers` on table `history_views`
--
ALTER TABLE history_views
ADD INDEX FK_history_views_idUsers (idUsers);

--
-- Create index `FK_history_views_idVideo` on table `history_views`
--
ALTER TABLE history_views
ADD INDEX FK_history_views_idVideo (idVideo);

--
-- Create foreign key
--
ALTER TABLE history_views
ADD CONSTRAINT FK_history_views_idUsers2 FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE history_views
ADD CONSTRAINT FK_history_views_idVideo2 FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `comments`
--
CREATE TABLE comments (
  idComments bigint(20) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) NOT NULL,
  idUsers bigint(20) NOT NULL,
  comment text NOT NULL,
  datecomments datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (idComments)
)
ENGINE = INNODB,
AUTO_INCREMENT = 11,
AVG_ROW_LENGTH = 8192,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `FK_comments_idVideo` on table `comments`
--
ALTER TABLE comments
ADD INDEX FK_comments_idVideo (idVideo);

--
-- Create foreign key
--
ALTER TABLE comments
ADD CONSTRAINT FK_comments_idUsers FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE comments
ADD CONSTRAINT FK_comments_idVideo2 FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

--
-- Create table `check_video`
--
CREATE TABLE check_video (
  id_checkVideo bigint(20) NOT NULL AUTO_INCREMENT,
  idVideo bigint(20) NOT NULL,
  idUsers bigint(20) DEFAULT NULL,
  result varchar(50) NOT NULL DEFAULT 'На рассмотрении',
  reason text DEFAULT NULL,
  PRIMARY KEY (id_checkVideo)
)
ENGINE = INNODB,
AUTO_INCREMENT = 20,
AVG_ROW_LENGTH = 2048,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `FK_check_video_idUsers` on table `check_video`
--
ALTER TABLE check_video
ADD INDEX FK_check_video_idUsers (idUsers);

--
-- Create index `FK_check_video_idVideo` on table `check_video`
--
ALTER TABLE check_video
ADD INDEX FK_check_video_idVideo (idVideo);

--
-- Create foreign key
--
ALTER TABLE check_video
ADD CONSTRAINT FK_check_video_idUsers2 FOREIGN KEY (idUsers)
REFERENCES users (idUsers) ON DELETE CASCADE;

--
-- Create foreign key
--
ALTER TABLE check_video
ADD CONSTRAINT FK_check_video_idVideo2 FOREIGN KEY (idVideo)
REFERENCES video (idVideo) ON DELETE CASCADE;

-- 
-- Dumping data for table users
--
INSERT INTO users VALUES
(1, 'RedOcean', '$2y$10$Ut64W4Sm34I5tieeRIpBj.jNWb6IlrlZ/FvGIsNKsBi0qE6JYksGS', 'Администратор'),
(2, 'Moderator', '$2y$10$Ut64W4Sm34I5tieeRIpBj.jNWb6IlrlZ/FvGIsNKsBi0qE6JYksGS', 'Модератор'),
(3, 'nikita', '$2y$10$Ut64W4Sm34I5tieeRIpBj.jNWb6IlrlZ/FvGIsNKsBi0qE6JYksGS', 'Пользователь'),
(4, 'test', '$2y$10$/bag9fzWegQDTcSZp12U4.iAhKEQkFON5ZShPYcpsMEeVoyV.gSA2', 'Пользователь'),
(12, 'LiRey', '$2y$10$pznfcUk7V6IUWxVWPpEsZOxkm5gkg.q.Iq0oku5l.Rjy3zqKhGmWK', 'Пользователь');

-- 
-- Dumping data for table themes_video
--
INSERT INTO themes_video VALUES
(1, 'Введение в языки\nпрограммирования'),
(2, 'Знакомство с ООП'),
(3, 'Уроки HTML'),
(4, 'Уроки C++'),
(5, 'Уроки C#'),
(6, 'Уроки Unity'),
(7, 'Уроки JavaScript'),
(8, 'Уроки Java'),
(9, 'Уроки C');

-- 
-- Dumping data for table video
--
INSERT INTO video VALUES
(7, 'Java: что нужно знать новичку?', '5d2eaf350c48bd2808c559b4ec114ced', 1, '2024-05-15 17:29:30', 8, NULL),
(9, 'HTML с нуля: урок 1 - как работает Интернет и что такое сайт', '97db5d2aae07482f764db4d14b4acc06', 1, '2024-05-15 17:37:46', 3, NULL);

-- 
-- Dumping data for table video_views
--
INSERT INTO video_views VALUES
(29, 9, NULL, '::1', '2024-05-23 00:27:21'),
(30, 9, 1, '::1', '2024-05-23 00:27:34'),
(33, 9, 4, '::1', '2024-05-23 19:32:10'),
(36, 9, NULL, '::1', '2024-05-23 20:27:26'),
(39, 9, 1, '::1', '2024-05-23 20:27:41');

-- 
-- Dumping data for table video_likes
--
-- Table `web-player`.video_likes does not contain any data (it is empty)

-- 
-- Dumping data for table type_video
--
INSERT INTO type_video VALUES
(7, 7, 'Открытый доступ'),
(9, 9, 'Открытый доступ');

-- 
-- Dumping data for table subscriptions
--
INSERT INTO subscriptions VALUES
(1, 2, 1, '2024-05-03'),
(2, 1, 2, '2024-05-08'),
(3, 3, 1, '2024-05-14'),
(4, 3, 2, '2024-05-14'),
(5, 4, 1, '2024-05-23');

-- 
-- Dumping data for table marker
--
-- Table `web-player`.marker does not contain any data (it is empty)

-- 
-- Dumping data for table history_views
--
INSERT INTO history_views VALUES
(11, 1, 9, '2024-05-23 20:31:02'),
(12, 4, 9, '2024-05-23 19:32:26');

-- 
-- Dumping data for table comments
--
INSERT INTO comments VALUES
(8, 9, 1, 'Всем привет!', '2024-05-23 00:27:41'),
(9, 9, 4, 'Мой первый комментарий', '2024-05-23 19:32:24');

-- 
-- Dumping data for table check_video
--
INSERT INTO check_video VALUES
(7, 7, NULL, 'Одобрено', ''),
(9, 9, NULL, 'Одобрено', '');

-- 
-- Dumping data for table bookmarks
--
-- Table `web-player`.bookmarks does not contain any data (it is empty)

--
-- Set default database
--
USE `web-player`;

--
-- Drop trigger `after_video_delete`
--
DROP TRIGGER IF EXISTS after_video_delete;

--
-- Drop trigger `after_video_insert`
--
DROP TRIGGER IF EXISTS after_video_insert;

--
-- Set default database
--
USE `web-player`;

DELIMITER $$

--
-- Create trigger `after_video_insert`
--
CREATE
DEFINER = 'root'@'localhost'
TRIGGER after_video_insert
AFTER INSERT
ON video
FOR EACH ROW
BEGIN
  INSERT INTO check_video (idVideo, result)
    VALUES (NEW.idVideo, 'На рассмотрении');
  INSERT INTO type_video (idVideo, nameType)
    VALUES (NEW.idVideo, 'Закрыто');
END
$$

--
-- Create trigger `after_video_delete`
--
CREATE
DEFINER = 'root'@'localhost'
TRIGGER after_video_delete
BEFORE DELETE
ON video
FOR EACH ROW
BEGIN
  DELETE
    FROM check_video
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM type_video
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM comments
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM video_views
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM history_views
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM marker
  WHERE idVideo = OLD.idVideo;
  DELETE
    FROM video_likes
  WHERE idVideo = OLD.idVideo;
END
$$

DELIMITER ;

-- 
-- Restore previous SQL mode
-- 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

-- 
-- Enable foreign keys
-- 
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;