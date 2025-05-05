CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(255),
  `role` varchar(255) COMMENT 'student or admin',
  `created_at` timestamp
);

CREATE TABLE `courses` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255),
  `description` text,
  `language` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `modules` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `course_id` int,
  `title` varchar(255),
  `description` text,
  `position` int
);

CREATE TABLE `lessons` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `module_id` int,
  `title` varchar(255),
  `description` text,
  `theory_content` text,
  `chat_config` json,
  `ct_phase` varchar(255) COMMENT 'Decomposition, Abstraction, Pattern Recognition, Algorithm Design',
  `position` int
);

CREATE TABLE `progress` (
  `user_id` int,
  `lesson_id` int,
  `theory_read` boolean,
  `chat_started_at` timestamp,
  `chat_ended_at` timestamp,
  `completed_at` timestamp,
  `status` varchar(255) COMMENT 'not_started, in_progress, completed'
);

CREATE TABLE `reflections` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `lesson_id` int,
  `question` text,
  `type` varchar(255) COMMENT 'likert or freetext'
);

CREATE TABLE `reflection_answers` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `reflection_id` int,
  `user_id` int,
  `answer_text` text,
  `scale` tinyint COMMENT 'If Likert, value 1–6',
  `created_at` timestamp
);

CREATE TABLE `chats` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `lesson_id` int,
  `created_at` timestamp
);

CREATE TABLE `chat_messages` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `chat_id` int,
  `sender` varchar(255) COMMENT 'user or ai or system',
  `message` text,
  `created_at` timestamp
);

CREATE TABLE `module_feedback` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `module_id` int,
  `submitted_at` timestamp,
  `feedback_json` json COMMENT 'Likert scale + freetext combined'
);

CREATE UNIQUE INDEX `progress_index_0` ON `progress` (`user_id`, `lesson_id`);

ALTER TABLE `modules` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

ALTER TABLE `lessons` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

ALTER TABLE `progress` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `progress` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `reflections` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `reflection_answers` ADD FOREIGN KEY (`reflection_id`) REFERENCES `reflections` (`id`);

ALTER TABLE `reflection_answers` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `chats` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `chats` ADD FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `chat_messages` ADD FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`);

ALTER TABLE `module_feedback` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `module_feedback` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);


CREATE TABLE plattform_feedback (
  id CHAR(36) PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  helpful ENUM('yes', 'no') NOT NULL,
  reasons TEXT,
  improved TEXT,
  learn_effective TEXT,
  general_feedback TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);