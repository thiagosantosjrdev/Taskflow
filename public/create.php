<?php
declare(strict_types=1);

function create_task(PDO $pdo, string $task_title, string $task_desc): bool
{
  try {
    $stmt = $pdo->prepare('INSERT INTO tasks (titulo, descricao) VALUES (:title, :desc)');
    $stmt->execute(['title'=>$task_title, 'desc'=>$task_desc]);
    return true;
  } catch(Exception $e) {
    error_log("Erro : {$e}");
    return false;
  }
}