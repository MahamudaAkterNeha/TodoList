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
    <link rel="stylesheet" href="style.css">
    <title>Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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