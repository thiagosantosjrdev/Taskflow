<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../public/functions.php';
require_once __DIR__ . '/create.php';
require_once __DIR__ . '/delete.php';
require_once __DIR__ . '/functions.php';

$pdo = (Database::config())->connect();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['operation'])){
        if ($_POST['operation'] === 'create') {
            $taskTitle = trim((string) $_POST['titulo']);
            $taskDescription = trim((string) $_POST['descricao']);
            if ($taskDescription === '') {$taskDescription = "Sem descrição.";}
            if (create_task($pdo, $taskTitle, $taskDescription)) {
                $_SESSION['success'] = "A task \"{$taskTitle}\" foi criada com sucesso!";
            } else {
                $_SESSION['error'] = "A task \"{$taskTitle}\" não foi criada.";
            }
        } elseif ($_POST['operation'] === 'delete') {
            $idTask = (int) $_POST['task_id'];
            $task = searchByID($pdo, $idTask);
            if ($task !== null){
                $titulo = $task['titulo'];
                if (delete_task($pdo, $idTask)) {
                    $_SESSION['success'] = "A task \"{$titulo}\" foi apagada com sucesso!";
                }
            }
        } elseif ($_POST['operation'] === 'check') {
            $idTask = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
            changeStatus($pdo, $idTask, 'concluída');
        } elseif ($_POST['operation'] === 'uncheck') {
            $idTask = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
            changeStatus($pdo, $idTask, 'pendente');
        }
    }
}


$pageTitle = 'Minhas tarefas';
$currentPage = 'tasks';


$tasks = returnTasks($pdo);

$totalTasks = count($tasks);
$completedTasks = count(array_filter(
    $tasks,
    fn(array $task): bool => $task['status'] === 'concluída'
));
$pendingTasks = $totalTasks - $completedTasks;
$completion = $totalTasks > 0
    ? (int) round(($completedTasks / $totalTasks) * 100)
    : 0;
$showIntro = empty($_SESSION['intro_seen']) || isset($_GET['intro']);
$_SESSION['intro_seen'] = true;
require __DIR__ . '/../views/header.php';
?>

<dialog class="intro" id="intro" aria-label="Taskflow">
    <div class="intro-brand">
        <div class="intro-mark">T</div>
        <div class="intro-name">
            <div class="intro-clip"><span>TASKFLOW</span></div>
        </div>
    </div>
</dialog>

<script>
(() => {
    const intro = document.getElementById('intro');
    if (!intro) return;

    // ?intro no final do endereço força a animação (útil para testar)
    const forced = new URLSearchParams(location.search).has('intro');

    // cada aba tem a sua própria memória; ela some quando a aba é fechada
    let seen = false;
    try { seen = sessionStorage.getItem('taskflowIntro') === '1'; } catch (e) {}

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if ((seen && !forced) || reduceMotion || typeof intro.showModal !== 'function') {
        intro.remove();
        return;
    }

    try { sessionStorage.setItem('taskflowIntro', '1'); } catch (e) {}

    intro.showModal();
    intro.addEventListener('animationend', (e) => {
        if (e.target === intro && e.animationName === 'introOut') intro.close();
    });
    intro.addEventListener('click', () => intro.classList.add('skip'));
    intro.addEventListener('cancel', (e) => { e.preventDefault(); intro.classList.add('skip'); });
})();
</script>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <span class="alert-icon">!</span>
        <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<section class="page-heading">
    <div>
        <p class="eyebrow">VISÃO GERAL</p>
        <h1>Minhas tarefas</h1>
        <p class="subtitle">Organize suas atividades em um só lugar.</p>
    </div>
    <button class="primary-button" type="button" id="openTaskModal">
        ＋ Nova tarefa
    </button>
</section>

<section class="stats-grid" aria-label="Resumo das tarefas">
    <article class="stat-card">
        <div class="stat-top"><span>Total de tarefas</span><span class="stat-icon">▤</span></div>
        <div class="stat-number"><?= $totalTasks ?></div>
        <div class="stat-note">Tarefas cadastradas</div>
    </article>

    <article class="stat-card">
        <div class="stat-top"><span>Pendentes</span><span class="stat-icon">◷</span></div>
        <div class="stat-number"><?= $pendingTasks ?></div>
        <div class="stat-note">Aguardando execução</div>
    </article>

    <article class="stat-card">
        <div class="stat-top"><span>Concluídas</span><span class="stat-icon">✓</span></div>
        <div class="stat-number"><?= $completedTasks ?></div>
        <div class="stat-note">Tarefas finalizadas</div>
    </article>
</section>

<section class="panel" id="task-list">
    <div class="panel-heading">
        <div>
            <h2>Lista de tarefas</h2>
            <p>Suas atividades aparecerão aqui.</p>
        </div>
        <span class="panel-count"><?= $totalTasks ?> itens</span>
    </div>

    <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th>TÍTULO</th>
                    <th>DESCRIÇÃO</th>
                    <th>DATA DE CRIAÇÃO</th>
                    <th>STATUS</th>
                    <th>AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tasks === []): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            Nenhuma tarefa por enquanto.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <div class="task-name">
                                    <?php if ($task['status'] !== 'concluída'): ?>
                                        <form method="POST" action="" class="task-check-form">
                                            <input type="hidden" name="operation" value="check">
                                            <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">

                                            <button
                                                type="submit"
                                                class="task-check"
                                                title="Concluir tarefa"
                                                aria-label="Concluir tarefa"
                                            ></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="" class="task-check">
                                            <input type="hidden" name="operation" value="uncheck">
                                            <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">

                                            <button
                                                type="submit"
                                                class="task-check"
                                                title="Concluir tarefa"
                                                aria-label="Concluir tarefa"
                                            ></button>
                                        </form>
                                    <?php endif; ?>

                                    <?= htmlspecialchars($task['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </td>

                            <td><?= htmlspecialchars($task['descricao'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td>
                                <span class="tag">
                                    <?= htmlspecialchars($task['criado_em'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Deseja realmente excluir esta tarefa?');">
                                    <input type="hidden" name="operation" value="delete">
                                    <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">

                                    <button type="submit" class="delete-button">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="bottom-grid">
    <section class="panel" id="projects">
        <div class="panel-heading">
            <div>
                <h2>Atividade recente</h2>
                <p>Suas atualizações aparecerão aqui.</p>
            </div>
        </div>
        <div class="empty-state">Nenhuma atividade registrada.</div>
    </section>

    <section class="panel" id="reports">
        <div class="panel-heading">
            <div>
                <h2>Seu progresso</h2>
                <p>Percentual de tarefas concluídas.</p>
            </div>
        </div>
        <div class="progress-content">
            <div class="progress-big"><?= $completion ?>%</div>
            <p><?= $completedTasks ?> de <?= $totalTasks ?> tarefas concluídas</p>
            <div class="progress-track wide">
                <div class="progress-fill" style="width: <?= $completion ?>%"></div>
            </div>
        </div>
    </section>
</div>

<dialog class="task-modal" id="taskModal">

    <form method="POST" action="index.php">

        <div class="modal-header">
            <div>
                <p class="eyebrow">NOVA ATIVIDADE</p>
                <h2>Criar tarefa</h2>
            </div>

            <button type="button" id="closeTaskModal"
                    class="modal-close">&times;</button>
        </div>

        <div class="form-field">
            <input type="hidden" value="create" name="operation">
            <label for="titulo">Título da tarefa</label>
            <input
                type="text"
                name="titulo"
                id="titulo"
                placeholder="Ex.: Estudar PHP"
                maxlength="100"
                required
            >
        </div>

        <div class="form-field">
            <label for="descricao">Descrição</label>
            <textarea
                name="descricao"
                id="descricao"
                placeholder="Descreva sua tarefa..."
                rows="5"
            ></textarea>
        </div>

        <div class="modal-actions">
            <button type="button" class="cancel-button" id="cancelTaskModal">
                Cancelar
            </button>

            <button type="submit" name="create_task"
                    class="primary-button">
                Criar tarefa
            </button>
        </div>

    </form>

</dialog>

<script>
const modal = document.getElementById('taskModal');

document.getElementById('openTaskModal').addEventListener('click', () => {
    modal.showModal();
});

document.getElementById('closeTaskModal').addEventListener('click', () => {
    modal.close();
});

document.getElementById('cancelTaskModal').addEventListener('click', () => {
    modal.close();
});

modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        modal.close();
    }
});
</script>

<footer class="credits">
    <p>
        <span class="credits-role">Backend</span>
        <a href="https://github.com/thiagosantosjrdev" target="_blank" rel="noopener noreferrer">@thiagosantosjrdev</a>
    </p>
    <p>
        <span class="credits-role">Frontend</span>
        <a href="https://claude.ai" target="_blank" rel="noopener noreferrer">Claude (Anthropic)</a>
    </p>
</footer>

<?php require __DIR__ . '/../views/footer.php'; ?>
