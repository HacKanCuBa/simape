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
GRANT SELECT, UPDATE, INSERT, DELETE ON SiMaPe.* TO 'apprw'@'localhost';
