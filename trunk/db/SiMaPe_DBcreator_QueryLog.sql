-- -----------------------------------------------------
-- Habilitar QueryLog
-- -----------------------------------------------------

SET GLOBAL log_output = 'FILE';
-- ATENCION: chown mysql root y 0640
SET GLOBAL general_log_file = '/var/log/simape/SiMaPe-DB.log';
SET GLOBAL general_log = 'ON';
