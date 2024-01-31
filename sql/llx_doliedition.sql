-- Copyright (C) 2023-2024 Anthony Damhet <contact@progiseize.fr>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

CREATE TABLE IF NOT EXISTS llx_doliedition (
  rowid int AUTO_INCREMENT PRIMARY KEY NOT NULL,
  entity int DEFAULT 1,
  edition varchar(16) NOT NULL,
  numero int NOT NULL,
  debut date NOT NULL,
  fin date NOT NULL,
  note text,
  active tinyint(1) DEFAULT 0,
  current tinyint(1) DEFAULT 0,
  tms datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);