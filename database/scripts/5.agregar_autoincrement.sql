-- Script para agregar AUTO_INCREMENT a las tablas que lo necesitan
-- Fecha: 21-10-2025
-- Ejecutar en orden (si alguna PRIMARY KEY ya existe, omitir ese ALTER TABLE)

-- 1. Tabla proyecto
-- Primero agregar PRIMARY KEY si no existe
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id`);
-- Luego agregar AUTO_INCREMENT
ALTER TABLE `proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 2. Tabla proyecto_usuario
-- Primero agregar PRIMARY KEY si no existe
ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`id`);
-- Luego agregar AUTO_INCREMENT
ALTER TABLE `proyecto_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 3. Tabla cliente_fe_info
-- Primero agregar PRIMARY KEY si no existe
ALTER TABLE `cliente_fe_info`
  ADD PRIMARY KEY (`id`);
-- Luego agregar AUTO_INCREMENT
ALTER TABLE `cliente_fe_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Verificación (descomentar para ejecutar)
-- SHOW CREATE TABLE proyecto;
-- SHOW CREATE TABLE proyecto_usuario;
-- SHOW CREATE TABLE cliente_fe_info;

