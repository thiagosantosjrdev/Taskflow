<?php
declare(strict_types=1);

function delete_task(PDO $pdo, int $id): bool
{
  try {
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute(['id'=>$id]);
    return true;
  } catch(Exception $e) {
    error_log("Erro : {$e}");
    return false;
  }
}