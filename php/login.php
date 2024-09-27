<?php
require 'db.php';
require 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Подключение к базе данных
    $conn = new mysqli("localhost", "root", "", "up-09-01");
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получение данных из формы
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка reCAPTCHA
    $recaptchaSecret = '6Lea21AqAAAAACTOJeczJqaILh0crnkHd-BknbGK';  // Ваш секретный ключ
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    // Отправка запроса на проверку reCAPTCHA
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $responseData = json_decode($verifyResponse);
    
    if (!$responseData->success) {
        echo "Проверка reCAPTCHA не пройдена.";
    } else {
        // Подготовленный запрос для поиска пользователя
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Использование password_verify для хэшированных паролей
            if (password_verify($password, $user['password'])) {
                // Вход выполнен, сохраняем сессию
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user['role']; // Сохраняем роль пользователя в сессии
                
                // Перенаправление в зависимости от роли
                if ($user['role'] == 1) { // Если админ
                    $_SESSION['admin_logged_in'] = true;
                    header('Location: admin_panel.php');
                } else { // Если обычный пользователь
                    header('Location: index.html');
                }
                exit();
            } else {
                echo "Неверный пароль.";
            }
        } else {
            echo "Пользователь не найден.";
        }

        // Закрытие подготовленного выражения
        $stmt->close();
    }

    // Закрытие соединения с базой данных
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="login.css"> <!-- Путь к вашему CSS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Google reCAPTCHA -->
</head>
<body>
    <div class="login-container">
        <h2>Авторизация</h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="6Lea21AqAAAAAJVS5AD9Q6TwtbtneZ0fSPeifUGm"></div> <!-- Ваш сайт-ключ reCAPTCHA -->
            </div>

            <div class="form-group">
                <input type="submit" value="Войти">
            </div>
        </form>

        <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</body>
</html>
