-- UTMC Dashboard database schema
-- MySQL 8.0.45
-- Run once to initialise tables. Safe to re-run; CREATE TABLE uses IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `warning_states` (
    `id`          INT           NOT NULL AUTO_INCREMENT,
    `site_idx`    INT           NOT NULL,
    `field_name`  VARCHAR(50)   NOT NULL,
    `status`      ENUM('pending','resolved','issue') NOT NULL,
    `note`        TEXT          DEFAULT NULL,
    `tech_name`   VARCHAR(100)  DEFAULT NULL,
    `recorded_at` DATETIME      NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_warning_states_site_field` (`site_idx`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `warning_tech_notes` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `site_idx`   INT          NOT NULL,
    `note`       TEXT         DEFAULT NULL,
    `tech_name`  VARCHAR(100) DEFAULT NULL,
    `updated_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_warning_tech_notes_site` (`site_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `installation_outcomes` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `site_id`     INT          NOT NULL,
    `type`        ENUM('online','issue') NOT NULL,
    `note`        TEXT         DEFAULT NULL,
    `tech_name`   VARCHAR(100) DEFAULT NULL,
    `recorded_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_installation_outcomes_site` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_visit_outcomes` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `scn`         VARCHAR(20)  NOT NULL,
    `type`        ENUM('online','issue') NOT NULL,
    `note`        TEXT         DEFAULT NULL,
    `tech_name`   VARCHAR(100) DEFAULT NULL,
    `recorded_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_site_visit_outcomes_scn` (`scn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
