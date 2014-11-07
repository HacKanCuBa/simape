-- -----------------------------------------------------
-- Usuarios
-- -----------------------------------------------------

-- Admin
CREATE USER 'admin'@'localhost' IDENTIFIED BY PASSWORD '<encrypted_pass: SELECT PASSWORD('mipassword')>';
GRANT ALL PRIVILEGES ON SiMaPe.* TO 'admin'@'localhost' WITH GRANT OPTION;

-- Application Read Only
CREATE USER 'appro'@'localhost' IDENTIFIED BY PASSWORD '<encrypted_pass: SELECT PASSWORD('mipassword')>';
GRANT SELECT ON SiMaPe.* TO 'appro'@'localhost';

-- Application ReadWrite
CREATE USER 'apprw'@'localhost' IDENTIFIED BY PASSWORD '<encrypted_pass: SELECT PASSWORD('mipassword')>';
GRANT SELECT, UPDATE, INSERT, DELETE, CREATE TEMPORARY TABLES ON SiMaPe.* TO 'apprw'@'localhost';

-- Chat User
CREATE USER 'chat'@'localhost' IDENTIFIED BY PASSWORD '<encrypted_pass: SELECT PASSWORD('mipassword')>';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_banned_users` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_chat` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_config` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_rooms` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_session` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_smileys` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_video_session` TO 'chat'@'localhost';
GRANT SELECT, UPDATE, INSERT, DELETE ON `SiMaPe`.`frei_webrtc` TO 'chat'@'localhost';
GRANT SELECT ON `SiMaPe`.`frei_users` TO 'chat'@'localhost';