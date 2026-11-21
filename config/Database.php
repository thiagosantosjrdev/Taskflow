<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
  public function __construct(
    private readonly string $host,
    private readonly string $dbname,
    private readonly string $username,
    private readonly string $passwd
  ) {}

  public function connect(): PDO
  {
    return new PDO(
      "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
      $this->username,
      $this->passwd,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]
    );
  }

  public static function config(): self
  {
    $c = loadConfig();
    return new self($c['DB_HOST'], $c['DB_NAME'], $c['DB_USER'], $c['DB_PASS']);
  }
}