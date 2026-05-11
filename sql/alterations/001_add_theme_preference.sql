ALTER TABLE tab_users
    ADD COLUMN IF NOT EXISTS theme_preference VARCHAR(5) DEFAULT 'light';

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'chk_tab_users_theme_preference'
    ) THEN
        ALTER TABLE tab_users
            ADD CONSTRAINT chk_tab_users_theme_preference
            CHECK (theme_preference IN ('light', 'dark'));
    END IF;
END
$$;
