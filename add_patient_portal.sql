-- =========================================================
-- Migration: add patient portal login (username + password) and
-- diagnosis/outcome tracking. Run this once in phpMyAdmin's SQL
-- tab if you already have an existing harbor_general database.
-- (Skip this if you're importing database.sql fresh — it already
-- includes these columns.)
-- =========================================================

-- If you previously ran an older version of this migration that
-- only added a "password" column (no username), this still works —
-- it will simply add the missing username column on top.
ALTER TABLE patients ADD COLUMN username VARCHAR(50) NULL UNIQUE AFTER emergency_contact;
ALTER TABLE patients ADD COLUMN password VARCHAR(255) NULL AFTER username;

ALTER TABLE appointments
  ADD COLUMN diagnosis TEXT NULL AFTER status,
  ADD COLUMN outcome ENUM('Pending','Treatment Successful','Referred to Another Hospital','Not Resolved') NOT NULL DEFAULT 'Pending' AFTER diagnosis,
  ADD COLUMN referral_hospital VARCHAR(150) NULL AFTER outcome;
