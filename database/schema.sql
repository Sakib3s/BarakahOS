CREATE DATABASE IF NOT EXISTS productivity_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE productivity_tracker;

-- Store all DATETIME values in Asia/Dhaka local time from the PHP app layer.
-- Use the companion DATE columns for day/week/month reporting filters and grouping.

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NULL,
    display_name VARCHAR(120) NOT NULL,
    timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Dhaka',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE routine_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    category ENUM(
        'prayer',
        'health',
        'planning',
        'work',
        'trading',
        'trading_learning',
        'coding',
        'support',
        'class',
        'family',
        'rest',
        'other'
    ) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    is_fixed_task TINYINT(1) NOT NULL DEFAULT 0,
    active_days JSON NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_routine_templates_user_sort (user_id, sort_order),
    KEY idx_routine_templates_user_category (user_id, category),
    CONSTRAINT chk_routine_templates_time_range
        CHECK (end_time > start_time),
    CONSTRAINT fk_routine_templates_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE daily_routine_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    routine_template_id BIGINT UNSIGNED NOT NULL,
    entry_date DATE NOT NULL,
    status ENUM('done', 'partial', 'skipped') NOT NULL DEFAULT 'done',
    completion_percent TINYINT UNSIGNED NULL,
    note VARCHAR(500) NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_daily_routine_entries_user_template_date (user_id, routine_template_id, entry_date),
    KEY idx_daily_routine_entries_user_date (user_id, entry_date),
    KEY idx_daily_routine_entries_template_date (routine_template_id, entry_date),
    CONSTRAINT chk_daily_routine_entries_completion_percent
        CHECK (completion_percent IS NULL OR completion_percent BETWEEN 0 AND 100),
    CONSTRAINT fk_daily_routine_entries_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_daily_routine_entries_template
        FOREIGN KEY (routine_template_id) REFERENCES routine_templates (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE daily_checklist_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    task_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('pending', 'done', 'partial', 'missed') NOT NULL DEFAULT 'pending',
    estimated_duration_minutes SMALLINT UNSIGNED NULL,
    actual_duration_minutes SMALLINT UNSIGNED NULL,
    note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_daily_checklist_tasks_user_date (user_id, task_date),
    KEY idx_daily_checklist_tasks_user_status_date (user_id, status, task_date),
    KEY idx_daily_checklist_tasks_user_category_date (user_id, category, task_date),
    KEY idx_daily_checklist_tasks_user_priority_date (user_id, priority, task_date),
    CONSTRAINT chk_daily_checklist_tasks_estimated_duration
        CHECK (estimated_duration_minutes IS NULL OR estimated_duration_minutes > 0),
    CONSTRAINT chk_daily_checklist_tasks_actual_duration
        CHECK (actual_duration_minutes IS NULL OR actual_duration_minutes > 0),
    CONSTRAINT fk_daily_checklist_tasks_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    priority TINYINT UNSIGNED NOT NULL DEFAULT 3,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    due_date DATE NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tasks_user_status_due (user_id, status, due_date),
    KEY idx_tasks_user_created (user_id, created_at),
    CONSTRAINT chk_tasks_priority
        CHECK (priority BETWEEN 1 AND 5),
    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    action_type ENUM('created', 'status_changed', 'worked', 'commented', 'completed', 'reopened') NOT NULL,
    from_status ENUM('pending', 'in_progress', 'completed', 'cancelled') NULL,
    to_status ENUM('pending', 'in_progress', 'completed', 'cancelled') NULL,
    minutes_spent SMALLINT UNSIGNED NULL,
    note TEXT NULL,
    logged_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_task_logs_user_date (user_id, log_date),
    KEY idx_task_logs_task_date (task_id, log_date),
    KEY idx_task_logs_user_action_date (user_id, action_type, log_date),
    CONSTRAINT chk_task_logs_minutes_spent
        CHECK (minutes_spent IS NULL OR minutes_spent > 0),
    CONSTRAINT fk_task_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_task_logs_task
        FOREIGN KEY (task_id) REFERENCES tasks (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fixed_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    scheduled_time TIME NULL,
    expected_minutes SMALLINT UNSIGNED NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_fixed_tasks_user_active_sort (user_id, is_active, sort_order),
    CONSTRAINT fk_fixed_tasks_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fixed_task_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    routine_template_id BIGINT UNSIGNED NULL,
    fixed_task_id BIGINT UNSIGNED NULL,
    log_date DATE NOT NULL,
    planned_start_time TIME NULL,
    planned_end_time TIME NULL,
    status ENUM('done', 'partial', 'skipped_with_note', 'missed') NOT NULL,
    skip_note TEXT NULL,
    general_note TEXT NULL,
    logged_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fixed_task_logs_user_fixed_task_date (user_id, fixed_task_id, log_date),
    UNIQUE KEY uq_fixed_task_logs_user_routine_template_date (user_id, routine_template_id, log_date),
    KEY idx_fixed_task_logs_user_date_status (user_id, log_date, status),
    KEY idx_fixed_task_logs_task_date (fixed_task_id, log_date),
    KEY idx_fixed_task_logs_routine_template_date (routine_template_id, log_date),
    CONSTRAINT chk_fixed_task_logs_source
        CHECK (
            (routine_template_id IS NOT NULL AND fixed_task_id IS NULL)
            OR (routine_template_id IS NULL AND fixed_task_id IS NOT NULL)
        ),
    CONSTRAINT chk_fixed_task_logs_skipped_note
        CHECK (
            status <> 'skipped_with_note'
            OR (skip_note IS NOT NULL AND CHAR_LENGTH(TRIM(skip_note)) > 0)
        ),
    CONSTRAINT fk_fixed_task_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_fixed_task_logs_routine_template
        FOREIGN KEY (routine_template_id) REFERENCES routine_templates (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_fixed_task_logs_task
        FOREIGN KEY (fixed_task_id) REFERENCES fixed_tasks (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE focus_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color_hex CHAR(7) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_focus_categories_user_name (user_id, name),
    KEY idx_focus_categories_user_active (user_id, is_active),
    CONSTRAINT fk_focus_categories_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE focus_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    focus_category_id BIGINT UNSIGNED NOT NULL,
    pre_work_checklist_run_id BIGINT UNSIGNED NULL,
    session_date DATE NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_focus_sessions_user_date (user_id, session_date),
    KEY idx_focus_sessions_user_category_date (user_id, focus_category_id, session_date),
    KEY idx_focus_sessions_checklist_run (pre_work_checklist_run_id),
    CONSTRAINT chk_focus_sessions_duration
        CHECK (duration_minutes IS NULL OR duration_minutes > 0),
    CONSTRAINT chk_focus_sessions_time_range
        CHECK (end_time IS NULL OR end_time > start_time),
    CONSTRAINT fk_focus_sessions_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_focus_sessions_category
        FOREIGN KEY (focus_category_id) REFERENCES focus_categories (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pre_work_checklist_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    label VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pre_work_checklist_items_user_code (user_id, code),
    UNIQUE KEY uq_pre_work_checklist_items_user_label (user_id, label),
    KEY idx_pre_work_checklist_items_user_active_sort (user_id, is_active, sort_order),
    CONSTRAINT fk_pre_work_checklist_items_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pre_work_checklist_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    focus_session_id BIGINT UNSIGNED NULL,
    checklist_date DATE NOT NULL,
    status ENUM('completed', 'not_completed') NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pre_work_checklist_runs_user_date (user_id, checklist_date),
    KEY idx_pre_work_checklist_runs_user_status_date (user_id, status, checklist_date),
    KEY idx_pre_work_checklist_runs_focus_session (focus_session_id),
    CONSTRAINT fk_pre_work_checklist_runs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pre_work_checklist_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checklist_run_id BIGINT UNSIGNED NOT NULL,
    checklist_item_id BIGINT UNSIGNED NOT NULL,
    is_checked TINYINT(1) NOT NULL DEFAULT 0,
    checked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pre_work_checklist_logs_run_item (checklist_run_id, checklist_item_id),
    KEY idx_pre_work_checklist_logs_run (checklist_run_id),
    KEY idx_pre_work_checklist_logs_item (checklist_item_id),
    CONSTRAINT fk_pre_work_checklist_logs_run
        FOREIGN KEY (checklist_run_id) REFERENCES pre_work_checklist_runs (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_pre_work_checklist_logs_item
        FOREIGN KEY (checklist_item_id) REFERENCES pre_work_checklist_items (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE distraction_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    focus_session_id BIGINT UNSIGNED NULL,
    log_date DATE NOT NULL,
    distraction_type ENUM('mobile_used', 'phone_near', 'social_media_used', 'waste_time', 'too_many_breaks') NOT NULL,
    note TEXT NULL,
    occurred_at DATETIME NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_distraction_logs_user_date (user_id, log_date),
    KEY idx_distraction_logs_user_type_date (user_id, distraction_type, log_date),
    KEY idx_distraction_logs_user_occurred (user_id, occurred_at),
    KEY idx_distraction_logs_session_date (focus_session_id, log_date),
    CONSTRAINT chk_distraction_logs_occurred_at
        CHECK (DATE(occurred_at) = log_date),
    CONSTRAINT chk_distraction_logs_duration_minutes
        CHECK (duration_minutes >= 0),
    CONSTRAINT fk_distraction_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_distraction_logs_focus_session
        FOREIGN KEY (focus_session_id) REFERENCES focus_sessions (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trading_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    market VARCHAR(50) NOT NULL,
    instrument VARCHAR(50) NOT NULL,
    setup_name VARCHAR(120) NULL,
    side ENUM('long', 'short') NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    quantity DECIMAL(18,4) NULL,
    entry_price DECIMAL(18,8) NULL,
    exit_price DECIMAL(18,8) NULL,
    pnl_amount DECIMAL(18,2) NULL,
    traded_at DATETIME NOT NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trading_logs_user_date (user_id, log_date),
    KEY idx_trading_logs_market_date (market, log_date),
    KEY idx_trading_logs_instrument_date (instrument, log_date),
    CONSTRAINT chk_trading_logs_duration_minutes
        CHECK (duration_minutes IS NULL OR duration_minutes > 0),
    CONSTRAINT fk_trading_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coding_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NULL,
    log_date DATE NOT NULL,
    project_name VARCHAR(150) NOT NULL,
    repository_name VARCHAR(150) NULL,
    branch_name VARCHAR(120) NULL,
    minutes_spent SMALLINT UNSIGNED NOT NULL,
    started_at DATETIME NULL,
    ended_at DATETIME NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_coding_logs_user_date (user_id, log_date),
    KEY idx_coding_logs_task_date (task_id, log_date),
    KEY idx_coding_logs_user_project_date (user_id, project_name, log_date),
    CONSTRAINT chk_coding_logs_minutes_spent
        CHECK (minutes_spent > 0),
    CONSTRAINT chk_coding_logs_time_range
        CHECK (
            started_at IS NULL
            OR ended_at IS NULL
            OR ended_at > started_at
        ),
    CONSTRAINT fk_coding_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_coding_logs_task
        FOREIGN KEY (task_id) REFERENCES tasks (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prayer_definitions (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(50) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_prayer_definitions_code (code),
    UNIQUE KEY uq_prayer_definitions_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prayer_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    prayer_definition_id SMALLINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    status ENUM('on_time', 'delayed', 'missed') NOT NULL,
    prayed_at DATETIME NULL,
    note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_prayer_logs_user_prayer_date (user_id, prayer_definition_id, log_date),
    KEY idx_prayer_logs_user_date (user_id, log_date),
    KEY idx_prayer_logs_user_status_date (user_id, status, log_date),
    CONSTRAINT chk_prayer_logs_status_time
        CHECK (
            (status = 'missed' AND prayed_at IS NULL)
            OR (status IN ('on_time', 'delayed') AND prayed_at IS NOT NULL)
        ),
    CONSTRAINT fk_prayer_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_prayer_logs_definition
        FOREIGN KEY (prayer_definition_id) REFERENCES prayer_definitions (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO prayer_definitions (id, code, name, sort_order) VALUES
    (1, 'fajr', 'Fajr', 1),
    (2, 'dhuhr', 'Dhuhr', 2),
    (3, 'asr', 'Asr', 3),
    (4, 'maghrib', 'Maghrib', 4),
    (5, 'isha', 'Isha', 5)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    sort_order = VALUES(sort_order);

CREATE TABLE daily_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    review_date DATE NOT NULL,
    day_rating TINYINT UNSIGNED NOT NULL,
    what_went_well TEXT NOT NULL,
    what_failed TEXT NOT NULL,
    top_lesson TEXT NOT NULL,
    tomorrow_priority TEXT NOT NULL,
    sleep_note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_daily_reviews_user_date (user_id, review_date),
    KEY idx_daily_reviews_user_date (user_id, review_date),
    CONSTRAINT chk_daily_reviews_day_rating
        CHECK (day_rating BETWEEN 1 AND 10),
    CONSTRAINT fk_daily_reviews_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sleep_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sleep_date DATE NOT NULL,
    sleep_started_at DATETIME NOT NULL,
    woke_up_at DATETIME NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sleep_logs_user_date (user_id, sleep_date),
    KEY idx_sleep_logs_user_date (user_id, sleep_date),
    KEY idx_sleep_logs_user_woke (user_id, woke_up_at),
    CONSTRAINT chk_sleep_logs_duration_minutes
        CHECK (duration_minutes > 0),
    CONSTRAINT chk_sleep_logs_time_range
        CHECK (woke_up_at > sleep_started_at),
    CONSTRAINT fk_sleep_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
