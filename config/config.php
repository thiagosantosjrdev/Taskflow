<?php

declare(strict_types=1);

function loadConfig(): array
{
  $path = dirname(__DIR__, 6) . '/config/task-manager.env';
  $config = parse_ini_file($path);

  if ($config === false) {
    throw new RuntimeException('Arquivo não encontrado.');
  }
  return $config;
}