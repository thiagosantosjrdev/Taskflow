<?php
declare(strict_types=1);

function returnTasks(PDO $pdo): ?array
{
  try {
    $stmt = $pdo->prepare('SELECT * FROM tasks');
    $stmt->execute();
    return $stmt->fetchAll();
  } catch(Exception $e) {
    error_log("Erro : {$e}");
    return null;
  }
}

function searchByID(PDO $pdo, int $id): ?array
{
  try {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id');
    $stmt->execute(['id'=>$id]);
    $data = $stmt->fetch();
    return $data === false ? null : $data;
  } catch(Exception $e) {
    error_log("Erro : {$e}");
    return null;
  }
}

function changeStatus(PDO $pdo, int $id, string $status): bool
{
  try {
    $stmt = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :id");
    $stmt->execute(['status'=>$status, 'id'=>$id]);
    return true;
  } catch(Exception $e) {
    error_log("Erro : {$e}");
    return false;
  }
}

