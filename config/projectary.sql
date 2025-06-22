CREATE TABLE `commentables` (
  `comment_id` int(11) NOT NULL,
  `commentable_type` enum('task','project') NOT NULL,
  `commentable_id` int(11) NOT NULL,
  PRIMARY KEY (`comment_id`),
  CONSTRAINT `commentables_comments_FK` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commenter` int(11) NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT 1,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `commenter_user_comments_fk` (`commenter`),
  CONSTRAINT `commenter_user_comments_fk` FOREIGN KEY (`commenter`) REFERENCES `user` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabela de comentários para todos os tipos de comentários suportados'

CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` enum('create','update','delete') DEFAULT NULL,
  `type` enum('task','project') DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `author` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `history_user_iduser_fk` (`author`),
  CONSTRAINT `history_user_iduser_fk` FOREIGN KEY (`author`) REFERENCES `user` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT 'terá conteúdo json + base64',
  `notifyee` int(11) NOT NULL,
  `mailable` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('READ','UNREAD') NOT NULL DEFAULT 'UNREAD',
  `task` int(11) DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notifications_user_iduser_fk` (`notifyee`),
  KEY `notifications_tasks_id_fk` (`task`),
  CONSTRAINT `notifications_tasks_id_fk` FOREIGN KEY (`task`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notifications_user_iduser_fk` FOREIGN KEY (`notifyee`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `project_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assigned_to` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_assigned_to_user` (`assigned_to`),
  KEY `projects_status_fk` (`status_id`),
  CONSTRAINT `fk_assigned_to_user` FOREIGN KEY (`assigned_to`) REFERENCES `user` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projects_status_fk` FOREIGN KEY (`status_id`) REFERENCES `project_status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `task_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `final` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Determina se o estado marca a tarefa como feita',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(60) NOT NULL,
  `task_owner` int(11) NOT NULL,
  `task_status_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL COMMENT 'Projeto associado. Pode ser nulo e não ter projeto.',
  `task_description` varchar(255) NOT NULL,
  `task_priority` enum('P0','P1','P2','P3','P4') NOT NULL DEFAULT 'P0',
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finish_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time_spent` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tasks_task_status_id_fk` (`task_status_id`),
  KEY `tasks_project_id_fk` (`project_id`),
  CONSTRAINT `tasks_project_id_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tasks_task_status_id_fk` FOREIGN KEY (`task_status_id`) REFERENCES `task_status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_name` varchar(255) NOT NULL,
  `team_description` varchar(255) DEFAULT NULL,
  `team_status` tinyint(1) DEFAULT NULL,
  `open_invite` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `estado` int(11) DEFAULT 0,
  `email` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `user_has_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_assigned_id` int(11) NOT NULL,
  `team_assigned_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_has_team_teams_id_fk` (`team_assigned_id`),
  KEY `user_has_team_user_iduser_fk` (`user_assigned_id`),
  CONSTRAINT `user_has_team_teams_id_fk` FOREIGN KEY (`team_assigned_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_has_team_user_iduser_fk` FOREIGN KEY (`user_assigned_id`) REFERENCES `user` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='user (n-n) relationship table '

CREATE TABLE `user_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` int(11) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `status` enum('unused','used','expired') DEFAULT 'unused',
  `ttl` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `verification_code` (`verification_code`),
  KEY `fk_user_verification_user` (`iduser`),
  CONSTRAINT `fk_user_verification_user` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

