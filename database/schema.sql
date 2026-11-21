-- Task Manager database schema (MySQL / MariaDB)

CREATE DATABASE IF NOT EXISTS task_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE task_manager;

CREATE TABLE IF NOT EXISTS tasks (
  id        INT          NOT NULL AUTO_INCREMENT,
  titulo    VARCHAR(100)  NOT NULL,
  descricao VARCHAR(255) NOT NULL DEFAULT 'Sem descrição',
  criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status    VARCHAR(20)  NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (id),
  UNIQUE KEY titulo (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;