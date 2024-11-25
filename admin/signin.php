<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$file_path = "users.json";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!file_exists($file_path)) {
        file_put_contents($file_path, json_encode([]));
    }

    $users = json_decode(file_get_contents($file_path), true);

    foreach ($users as $user) {
        if ($user['username'] === $username) {
            echo "Erro: Nome de usuário já existe!";
            exit();
        }
    }

    $new_user = [
        "username" => $username,
        "password" => $password,
        "is_admin" => true
    ];
    $users[] = $new_user;

    file_put_contents($file_path, json_encode($users, JSON_PRETTY_PRINT));
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Novo Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link href="../CSS/style.css" rel="stylesheet">
</head>
<body class="body-admin">
    <div class="container d-flex justify-content-center align-items-center vh-100 " >
        <div class="card p-4 shadow-lg card-personalizado-admin" style="width: 100%; max-width: 500px;">
            <h1 class="text-center mb-4">Criar Novo Usuário</h1>
            <form action="signin.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Nome de Usuário</label>
                    <input type="text" id="username" name="username" class="form-control card-personalizado-admin-form" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="form-control card-personalizado-admin-form" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Criar Usuário</button>
            </form>
            <div class="text-center mt-3">
                <a href="/bolos/admin/" class="btn btn-primary w-100">Gerenciar</a>
            </div>
        </div>
    </div>
</body>
</html>
