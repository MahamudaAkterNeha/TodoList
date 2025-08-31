<?php
// The file where we store our tasks
$file = 'tasks.json';

// Function to get all tasks from the JSON file
function get_tasks($filename)
{
    if (!file_exists($filename)) {
        return []; // Return empty array if file doesn't exist
    }
    $json_data = file_get_contents($filename);
    return json_decode($json_data, true);
}

// Function to save tasks back to the JSON file
function save_tasks($filename, $tasks)
{
    $json_data = json_encode($tasks, JSON_PRETTY_PRINT);
    file_put_contents($filename, $json_data);
}

// --- HANDLE ACTIONS (ADD, TOGGLE, DELETE) ---

// Handle ADDING a new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task_content = trim($_POST['task_content']);
    if (!empty($task_content)) {
        $tasks = get_tasks($file);
        $tasks[] = ['content' => $task_content, 'done' => false];
        save_tasks($file, $tasks);
    }
    header("Location: index.php"); // Redirect to prevent re-submission
    exit;
}

// Handle TOGGLING (done/undone) or DELETING a task
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $tasks = get_tasks($file);
    $task_id = (int)$_GET['id'];

    if (array_key_exists($task_id, $tasks)) {
        if (isset($_GET['action']) && $_GET['action'] == 'toggle') {
            $tasks[$task_id]['done'] = !$tasks[$task_id]['done'];
        }
        if (isset($_GET['action']) && $_GET['action'] == 'delete') {
            array_splice($tasks, $task_id, 1);
        }
        save_tasks($file, $tasks);
    }
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #1f2937;
            --text-muted: #6b7280;
            --border-light: #e5e7eb;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
        }

        body {
            background: var(--bg-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .main-container {
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .todo-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            margin: 0 auto;
        }

        .todo-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--border-light);
            margin-bottom: 0;
        }

        .todo-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .add-form {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-light);
        }

        .form-control {
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #fafbfc;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .btn-add {
            background: var(--accent);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .btn-add:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .task-list {
            padding: 1rem 2rem 2rem;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s ease;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-item:hover {
            background: #fafbfc;
            margin: 0 -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: 8px;
        }

        .task-content {
            flex: 1;
            font-size: 0.95rem;
            color: var(--text-primary);
            margin: 0;
            transition: all 0.3s ease;
        }

        .task-content.done {
            text-decoration: line-through;
            color: var(--text-muted);
            opacity: 0.6;
        }

        .task-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-toggle {
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .btn-toggle:hover {
            background: var(--success);
            color: white;
            transform: translateY(-1px);
            text-decoration: none;
        }

        .btn-delete {
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-1px);
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 576px) {
            .main-container {
                padding: 1rem 0.5rem;
            }

            .todo-header,
            .add-form,
            .task-list {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="todo-card">
            <div class="todo-header">
                <h1 class="todo-title">Tasks</h1>
            </div>

            <div class="add-form">
                <form action="index.php" method="POST" class="d-flex gap-3">
                    <input type="text" class="form-control" name="task_content" placeholder="Add a new task..." required>
                    <button type="submit" name="add_task" class="btn btn-primary btn-add">Add</button>
                </form>
            </div>

            <div class="task-list">
                <?php
                $all_tasks = get_tasks($file);
                if (empty($all_tasks)) {
                    echo '<div class="empty-state">';
                    echo '    <div class="empty-icon">✓</div>';
                    echo '    <div>No tasks yet.<br>Add your first task above.</div>';
                    echo '</div>';
                } else {
                    foreach ($all_tasks as $id => $task) {
                        $content = htmlspecialchars($task['content']);
                        $done_class = $task['done'] ? 'done' : '';
                        echo "<div class='task-item'>";
                        echo "    <p class='task-content {$done_class}'>{$content}</p>";
                        echo "    <div class='task-actions'>";
                        echo "        <a href='?action=toggle&id={$id}' class='btn-action btn-toggle'>✓</a>";
                        echo "        <a href='?action=delete&id={$id}' class='btn-action btn-delete' onclick='return confirm(\"Delete this task?\");'>×</a>";
                        echo "    </div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>