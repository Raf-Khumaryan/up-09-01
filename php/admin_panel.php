<?php
session_start();

// Проверяем, что пользователь авторизован и является администратором
if (!isset($_SESSION['user_logged_in']) || $_SESSION['role'] != 1) {
    header('Location: login.php'); // Переход на страницу логина, если не админ
    exit();
}

// Подключение к базе данных
$conn = new mysqli("localhost", "root", "", "up-09-01");
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Добавление пользователя
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo "Пользователь добавлен.";
    } else {
        echo "Ошибка при добавлении пользователя.";
    }
    $stmt->close();
}

// Изменение пользователя
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $username, $email, $id);

    if ($stmt->execute()) {
        echo "Пользователь обновлен.";
    } else {
        echo "Ошибка при обновлении пользователя.";
    }
    $stmt->close();
}

// Удаление пользователя (только для админов)
if (isset($_POST['delete_user']) && $_SESSION['role'] == 1) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Пользователь удален.";
    } else {
        echo "Ошибка при удалении пользователя.";
    }
    $stmt->close();
}

// Добавление услуги
if (isset($_POST['add_service'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO services (name, price) VALUES (?, ?)");
    $stmt->bind_param("sd", $name, $price);

    if ($stmt->execute()) {
        echo "Услуга добавлена.";
    } else {
        echo "Ошибка при добавлении услуги.";
    }
    $stmt->close();
}

// Изменение услуги
if (isset($_POST['edit_service'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("UPDATE services SET name=?, price=? WHERE service_id=?");
    $stmt->bind_param("sdi", $name, $price, $id);

    if ($stmt->execute()) {
        echo "Услуга обновлена.";
    } else {
        echo "Ошибка при обновлении услуги.";
    }
    $stmt->close();
}

// Удаление услуги (только для админов)
if (isset($_POST['delete_service']) && $_SESSION['role'] == 1) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM services WHERE service_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Услуга удалена.";
    } else {
        echo "Ошибка при удалении услуги.";
    }
    $stmt->close();
}

// Получение пользователей
$users_result = $conn->query("SELECT * FROM users");

// Получение услуг
$services_result = $conn->query("SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="admin_panel.css">
    <script src="script.js"></script>
</head>
<body>
    <h1>Админ-панель</h1>

    <!-- Форма для добавления пользователя -->
    <h2>Добавить пользователя</h2>
    <form action="" method="POST">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit" name="add_user">Добавить</button>
    </form>

    <!-- Таблица с существующими пользователями -->
    <h2>Существующие пользователи</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя пользователя</th>
            <th>Email</th>
            <th>Действия</th>
        </tr>
        <?php while ($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?= $user['user_id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $user['user_id'] ?>">
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    <button type="submit" name="edit_user">Изменить</button>
                </form>
                <?php if ($_SESSION['role'] == 1): ?>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $user['user_id'] ?>">
                    <button type="submit" name="delete_user" onclick="return confirm('Вы уверены?')">Удалить</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Форма для добавления услуги -->
    <h2>Добавить услугу</h2>
    <form action="" method="POST">
        <input type="text" name="name" placeholder="Название услуги" required>
        <input type="number" step="0.01" name="price" placeholder="Цена" required>
        <button type="submit" name="add_service">Добавить услугу</button>
    </form>

    <!-- Таблица с существующими услугами -->
    <h2>Существующие услуги</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Название услуги</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
        <?php while ($service = $services_result->fetch_assoc()): ?>
        <tr>
            <td><?= $service['service_id'] ?></td>
            <td><?= htmlspecialchars($service['name']) ?></td>
            <td><?= htmlspecialchars($service['price']) . ' ₽' ?></td>
            <td>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $service['service_id'] ?>">
                    <input type="text" name="name" value="<?= htmlspecialchars($service['name']) ?>" required>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($service['price']) ?>" required>
                    <button type="submit" name="edit_service">Изменить</button>
                </form>
                <?php if ($_SESSION['role'] == 1): ?>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $service['service_id'] ?>">
                    <button type="submit" name="delete_service" onclick="return confirm('Вы уверены?')">Удалить</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <footer>
        <a href="logout.php">Выход</a>
    </footer>
</body>
</html>
