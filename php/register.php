<?php
require 'db.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Проверка на пустые поля
    if (empty($username) || empty($password) || empty($email) || empty($recaptchaResponse)) {
        echo "Все поля обязательны для заполнения!";
    } 
    // Проверка длины пароля
    elseif (strlen($password) < 6) {
        echo "Пароль должен быть не менее 6 символов!";
    } 
    else {
        // Проверка ReCAPTCHA
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".RECAPTCHA_SECRET_KEY."&response=".$recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (intval($responseKeys["success"]) !== 1) {
            echo "Пожалуйста, завершите CAPTCHA.";
        } else {
            // Проверка на наличие пользователя с таким же email или username
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            $user = $stmt->fetch();

            if ($user) {
                // Если пользователь с таким email или username уже существует
                echo "Пользователь с таким email или именем пользователя уже существует!";
            } else {
                // Хеширование пароля
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Вставка данных в базу
                $stmt = $pdo->prepare("INSERT INTO Users (username, password, email) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashedPassword, $email])) {
                    // Переадресация на главную страницу после успешной регистрации
                    header("Location: index.html");
                    exit(); // Останавливаем выполнение скрипта после переадресации
                } else {
                    echo "Ошибка при регистрации!";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="register.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <form method="post">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт <a href="login.php">Войти</a></p>
</body>
</html>
