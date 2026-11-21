<?php
// Este arquivo contém apenas o início do HTML e a navegação compartilhada.
$pageTitle = $pageTitle ?? 'Meu espaço';
$currentPage = $currentPage ?? 'tasks';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171923">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Taskflow</title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php" aria-label="Taskflow - início">
            <span class="brand-mark">T</span>
            <span class="brand-name">TASKFLOW</span>
        </a>

        <nav class="nav-list" aria-label="Navegação principal">
            <a class="nav-link <?= $currentPage === 'tasks' ? 'active' : '' ?>" href="index.php">
                <span class="nav-icon">☷</span><span class="nav-label">MINHAS TAREFAS</span>
            </a>
        </nav>

        <div class="sidebar-bottom">Organize suas tarefas.<br>Um passo de cada vez.</div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-actions">
                <button class="icon-button" type="button" aria-label="Notificações">♧</button>
                <div class="avatar" aria-label="Perfil">T</div>
            </div>
        </header>
