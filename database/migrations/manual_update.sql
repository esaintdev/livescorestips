-- SQL script to add league_id column and backfill existing records
-- Run this in your PHPMyAdmin or SQL Terminal

-- 1. Add the column (if not already added)
-- ALTER TABLE predictions ADD league_id INT UNSIGNED NULL AFTER fixture_id;

-- 2. Backfill Tier 1 (Elite)
UPDATE predictions SET league_id = 39 WHERE league_name = 'Premier League' AND league_country = 'England';
UPDATE predictions SET league_id = 140 WHERE league_name = 'La Liga' AND league_country = 'Spain';
UPDATE predictions SET league_id = 135 WHERE league_name = 'Serie A' AND league_country = 'Italy';
UPDATE predictions SET league_id = 78 WHERE league_name = 'Bundesliga' AND league_country = 'Germany';
UPDATE predictions SET league_id = 61 WHERE league_name = 'Ligue 1' AND league_country = 'France';
UPDATE predictions SET league_id = 2 WHERE league_name = 'Champions League';
UPDATE predictions SET league_id = 3 WHERE league_name = 'Europa League';
UPDATE predictions SET league_id = 848 WHERE league_name = 'Conference League';

-- 3. Backfill Tier 2 (Secondary)
UPDATE predictions SET league_id = 45 WHERE league_name = 'FA Cup' AND league_country = 'England';
UPDATE predictions SET league_id = 40 WHERE league_name = 'Championship' AND league_country = 'England';
UPDATE predictions SET league_id = 253 WHERE league_name = 'Major League Soccer';
UPDATE predictions SET league_id = 31 WHERE league_name = 'Eredivisie' AND league_country = 'Netherlands';
UPDATE predictions SET league_id = 94 WHERE league_name = 'Primeira Liga' AND league_country = 'Portugal';
