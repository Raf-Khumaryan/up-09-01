<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); // Переход на страницу логина, если не админ
    exit();
}

// Подключение к базе данных
$conn = new mysqli("localhost", "root", "", "up-09-01");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Добавление пользователя
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
}

// Изменение пользователя
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $conn->query("UPDATE users SET username='$username', email='$email' WHERE id=$id");
}

// Удаление пользователя
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM users WHERE id=$id");
}

// Получение пользователей
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <h1>Админ-панель</h1>
    <h2>Добавить пользователя</h2>
    <form action="" method="POST">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit" name="add_user">Добавить</button>
    </form>

    <h2>Существующие пользователи</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя пользователя</th>
            <th>Email</th>
            <th>Действия</th>
        </tr>
        <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['username'] ?></td>
            <td><?= $user['email'] ?></td>
            <td>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <input type="text" name="username" value="<?= $user['username'] ?>" required>
                    <input type="email" name="email" value="<?= $user['email'] ?>" required>
                    <button type="submit" name="edit_user">Изменить</button>
                </form>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete_user" onclick="return confirm('Вы уверены?')">Удалить</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <footer>
        <a href="logout.php">Выход</a>
    </footer>
</body>
</html>
