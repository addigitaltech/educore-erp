-- EduCore ERP — MySQL Initialization
CREATE DATABASE IF NOT EXISTS educore_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON educore_db.* TO 'educore'@'%';
FLUSH PRIVILEGES;
