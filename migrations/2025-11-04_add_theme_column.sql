-- Add theme column to boutique_settings
ALTER TABLE boutique_settings 
ADD COLUMN theme VARCHAR(50) NOT NULL DEFAULT 'theme-default';

-- Update existing rows to use default theme if any exist
UPDATE boutique_settings SET theme = 'theme-default' WHERE theme IS NULL OR theme = '';