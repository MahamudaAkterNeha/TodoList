<?php
// --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "todolist");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// --- HANDLE ACTIONS ---

// Add a new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task_content = trim($_POST['task_content']);
    if (!empty($task_content)) {
        $stmt = $conn->prepare("INSERT INTO tasks (task) VALUES (?)");
        $stmt->bind_param("s", $task_content);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit;
}

// Toggle task status or delete
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $task_id = (int)$_GET['id'];

    if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
        $result = $conn->query("SELECT status FROM tasks WHERE id = $task_id");
        if ($row = $result->fetch_assoc()) {
            $new_status = ($row['status'] === 'pending') ? 'done' : 'pending';
            $conn->query("UPDATE tasks SET status = '$new_status' WHERE id = $task_id");
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $conn->query("DELETE FROM tasks WHERE id = $task_id");
    }

    header("Location: index.php");
    exit;
}

// Fetch all tasks
$result = $conn->query("SELECT * FROM tasks ORDER BY id ASC");
$all_tasks = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <link rel="stylesheet" href="style.css">


</head>

<body>
    <div class="container">
        <h1>Tasks</h1>
        <form action="index.php" method="post">
            <input type="text" name="task_content" placeholder="Enter new task..." required>
            <button type="submit" name="add_task">Add</button>
        </form>

        <?php if (empty($all_tasks)): ?>
            <p>No tasks yet! Add your first task above.</p>
        <?php else: ?>
            <?php foreach ($all_tasks as $task):
                $id = $task['id'];
                $content = htmlspecialchars($task['task']);
                $done_class = ($task['status'] === 'done') ? 'done' : '';
            ?>
                <div class="task-item">
                    <span class="task-content <?php echo $done_class; ?>"><?php echo $content; ?></span>
                    <div class="task-actions">
                        <a href="?action=toggle&id=<?php echo $id; ?>" class="btn-toggle">✓</a>
                        <a href="?action=delete&id=<?php echo $id; ?>" class="btn-delete" onclick="return confirm('Delete this task?');">×</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>