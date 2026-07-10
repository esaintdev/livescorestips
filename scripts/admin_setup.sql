-- Admin Login SQL Script for livescorestips.com
-- This script creates a super_admin user in the admin_users table.

INSERT INTO
    `admin_users` (
        `name`,
        `email`,
        `password`,
        `role`,
        `created_at`,
        `updated_at`
    )
VALUES (
        'Admin User',
        'info@livescorestips.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This hashes to the password: 'password'
        'super_admin',
        NOW(),
        NOW()
    );