-- SQL script to add photo column to sponsor table
ALTER TABLE sponsor
ADD COLUMN photo VARCHAR(255) NULL COMMENT 'Path to the sponsor photo'; 