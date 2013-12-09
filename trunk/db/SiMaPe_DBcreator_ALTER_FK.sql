SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER TABLE `SiMaPe`.`Empleado` 
  ADD CONSTRAINT `fk_Empleado_Estudio`
  FOREIGN KEY (`NivelEstudioId` )
  REFERENCES `SiMaPe`.`NivelEstudio` (`NivelEstudioId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Empleado_Estado`
  FOREIGN KEY (`EstadoId` )
  REFERENCES `SiMaPe`.`Estado` (`EstadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Empleado_Foto`
  FOREIGN KEY (`FicheroId` )
  REFERENCES `SiMaPe`.`Fichero` (`FicheroId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Empleado_Estudio` (`NivelEstudioId` ASC) 
, ADD INDEX `fk_Empleado_Estado` (`EstadoId` ASC) 
, ADD INDEX `fk_Empleado_Foto` (`FicheroId` ASC) ;

ALTER TABLE `SiMaPe`.`Domicilio` 
  ADD CONSTRAINT `fk_Domicilio_Direccion`
  FOREIGN KEY (`DireccionId` )
  REFERENCES `SiMaPe`.`Direccion` (`DireccionId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Domicilio_Direccion` (`DireccionId` ASC) ;

ALTER TABLE `SiMaPe`.`DomicilioEmpleado` 
  ADD CONSTRAINT `fk_DomicilioEmpleado_Domicilio`
  FOREIGN KEY (`DomicilioId` )
  REFERENCES `SiMaPe`.`Domicilio` (`DomicilioId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_DomicilioEmpleado_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_DomicilioEmpleado_Domicilio` (`DomicilioId` ASC) 
, ADD INDEX `fk_DomicilioEmpleado_Empleado` (`EmpleadoId` ASC) ;

ALTER TABLE `SiMaPe`.`Cargo` 
  ADD CONSTRAINT `fk_Cargo_Escalafon`
  FOREIGN KEY (`EscalafonId` )
  REFERENCES `SiMaPe`.`Escalafon` (`EscalafonId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Cargo_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Cargo_Escalafon` (`EscalafonId` ASC) 
, ADD INDEX `fk_Cargo_Empleado` (`EmpleadoId` ASC) ;

ALTER TABLE `SiMaPe`.`Asistencia` 
  ADD CONSTRAINT `fk_Asistencia_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Asistencia_LicExt`
  FOREIGN KEY (`LicenciaExtraordinariaId` )
  REFERENCES `SiMaPe`.`LicenciaExtraordinaria` (`LicenciaExtraordinariaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Asistencia_LicOrd`
  FOREIGN KEY (`LicenciaOrdinariaId` )
  REFERENCES `SiMaPe`.`LicenciaOrdinaria` (`LicenciaOrdinariaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Asistencia_Empleado` (`EmpleadoId` ASC) 
, ADD INDEX `fk_Asistencia_LicExt` (`LicenciaExtraordinariaId` ASC) 
, ADD INDEX `fk_Asistencia_LicOrd` (`LicenciaOrdinariaId` ASC) ;

ALTER TABLE `SiMaPe`.`Fichaje` 
  ADD CONSTRAINT `fk_Fichaje_Asistencia`
  FOREIGN KEY (`AsistenciaId` )
  REFERENCES `SiMaPe`.`Asistencia` (`AsistenciaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Fichaje_Asistencia` (`AsistenciaId` ASC) ;

ALTER TABLE `SiMaPe`.`Reemplazo` 
  ADD CONSTRAINT `fk_Reemplazo_Reemplazante`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Reemplazo_Reemplazado`
  FOREIGN KEY (`OficinaId` )
  REFERENCES `SiMaPe`.`Oficina` (`OficinaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Reemplazo_Reemplazante` (`EmpleadoId` ASC) 
, ADD INDEX `fk_Reemplazo_Reemplazado` (`OficinaId` ASC) ;

ALTER TABLE `SiMaPe`.`Oficina` 
  ADD CONSTRAINT `fk_Oficina_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Oficina_Empleado` (`EmpleadoId` ASC) ;

ALTER TABLE `SiMaPe`.`Calificacion` 
  ADD CONSTRAINT `fk_Calificacion_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Calificacion_Empleado` (`EmpleadoId` ASC) ;

ALTER TABLE `SiMaPe`.`EntradaSalida` 
  ADD CONSTRAINT `fk_EntradaSalida_Asistencia`
  FOREIGN KEY (`AsistenciaId` )
  REFERENCES `SiMaPe`.`Asistencia` (`AsistenciaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_EntradaSalida_Desc`
  FOREIGN KEY (`EntradaSalidaDescId` )
  REFERENCES `SiMaPe`.`EntradaSalidaDesc` (`EntradaSalidaDescId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_EntradaSalida_Motivo`
  FOREIGN KEY (`EntradaSalidaMotivoId` )
  REFERENCES `SiMaPe`.`EntradaSalidaMotivo` (`EntradaSalidaMotivoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_EntradaSalida_Asistencia` (`AsistenciaId` ASC) 
, ADD INDEX `fk_EntradaSalida_Desc` (`EntradaSalidaDescId` ASC) 
, ADD INDEX `fk_EntradaSalida_Motivo` (`EntradaSalidaMotivoId` ASC) ;

ALTER TABLE `SiMaPe`.`Usuario` 
  ADD CONSTRAINT `fk_Usuario_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Usuario_Permisos`
  FOREIGN KEY (`UsuarioPerfilId` )
  REFERENCES `SiMaPe`.`UsuarioPerfil` (`UsuarioPerfilId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Usuario_Empleado` (`EmpleadoId` ASC) 
, ADD INDEX `fk_Usuario_Permisos` (`UsuarioPerfilId` ASC) ;

ALTER TABLE `SiMaPe`.`Direccion` 
  ADD CONSTRAINT `fk_Direccion_Pcia`
  FOREIGN KEY (`ProvinciaId` )
  REFERENCES `SiMaPe`.`Provincia` (`ProvinciaId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Direccion_Pcia` USING HASH (`ProvinciaId` ASC) ;

ALTER TABLE `SiMaPe`.`Mensaje` 
  ADD CONSTRAINT `fk_Mensaje_Emisor`
  FOREIGN KEY (`UsuarioId_Emisor` )
  REFERENCES `SiMaPe`.`Usuario` (`UsuarioId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_Mensaje_Receptor`
  FOREIGN KEY (`UsuarioId_Receptor` )
  REFERENCES `SiMaPe`.`Usuario` (`UsuarioId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Mensaje_Emisor` (`UsuarioId_Emisor` ASC) 
, ADD INDEX `fk_Mensaje_Receptor` (`UsuarioId_Receptor` ASC) ;

ALTER TABLE `SiMaPe`.`LicenciaExtraordinaria` 
  ADD CONSTRAINT `fk_LicenciaExtraordinaria_Desc`
  FOREIGN KEY (`LicenciaExtraordinariaDescId` )
  REFERENCES `SiMaPe`.`LicenciaExtraordinariaDesc` (`LicenciaExtraordinariaDescId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_LicenciaExtraordinaria_Desc` (`LicenciaExtraordinariaDescId` ASC) ;

ALTER TABLE `SiMaPe`.`PermisoAsignado` 
  ADD CONSTRAINT `fk_PermisoAsignado_Permiso`
  FOREIGN KEY (`PermisoId` )
  REFERENCES `SiMaPe`.`Permiso` (`PermisoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_PermisoAsignado_Perfil`
  FOREIGN KEY (`UsuarioPerfilId` )
  REFERENCES `SiMaPe`.`UsuarioPerfil` (`UsuarioPerfilId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_PermisoAsignado_Permiso` (`PermisoId` ASC) 
, ADD INDEX `fk_PermisoAsignado_Perfil` (`UsuarioPerfilId` ASC) ;

ALTER TABLE `SiMaPe`.`LicenciaOrdinaria` 
  ADD CONSTRAINT `fk_LicenciaOrdinaria_Desc`
  FOREIGN KEY (`LicenciaOrdinariaDescId` )
  REFERENCES `SiMaPe`.`LicenciaOrdinariaDesc` (`LicenciaOrdinariaDescId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_LicenciaOrdinaria_Desc` (`LicenciaOrdinariaDescId` ASC) ;

ALTER TABLE `SiMaPe`.`Fichero` 
  ADD CONSTRAINT `fk_Fichero_SubidoPor`
  FOREIGN KEY (`UsuarioId` )
  REFERENCES `SiMaPe`.`Usuario` (`UsuarioId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_Fichero_SubidoPor` (`UsuarioId` ASC) ;

ALTER TABLE `SiMaPe`.`HorarioLaboral` 
  ADD CONSTRAINT `fk_HorarioLaboral_Dia`
  FOREIGN KEY (`DiaLaboralDescId` )
  REFERENCES `SiMaPe`.`DiaLaboralDesc` (`DiaLaboralDescId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_HorarioLaboral_Empleado`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_HorarioLaboral_Dia` (`DiaLaboralDescId` ASC) 
, ADD INDEX `fk_HorarioLaboral_Empleado` (`EmpleadoId` ASC) ;

ALTER TABLE `SiMaPe`.`HorarioExtra` 
  ADD CONSTRAINT `fk_HorarioLaboral_Dia0`
  FOREIGN KEY (`DiaLaboralDescId` )
  REFERENCES `SiMaPe`.`DiaLaboralDesc` (`DiaLaboralDescId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `fk_HorarioLaboral_Empleado0`
  FOREIGN KEY (`EmpleadoId` )
  REFERENCES `SiMaPe`.`Empleado` (`EmpleadoId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, ADD INDEX `fk_HorarioLaboral_Dia` (`DiaLaboralDescId` ASC) 
, ADD INDEX `fk_HorarioLaboral_Empleado` (`EmpleadoId` ASC) ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
