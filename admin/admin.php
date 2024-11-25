<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

$bolosFilePath = __DIR__ . '/../includes/bolos.json';
$bolos = json_decode(file_get_contents($bolosFilePath), true);
$bolos = is_array($bolos) ? $bolos : [];

function validarUrlImagem($url) {
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $extensoesValidas = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $extensao = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return in_array($extensao, $extensoesValidas);
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    if ($action === 'add') {
        $novoNome = trim($_POST['nome']);
        $novaImagem = trim($_POST['imagem']);

        if (!empty($novoNome) && validarUrlImagem($novaImagem)) {
            $bolos[] = ['nome' => $novoNome, 'imagem' => $novaImagem];
            file_put_contents($bolosFilePath, json_encode($bolos, JSON_PRETTY_PRINT));
            header('Location: admin.php');
            exit();
        } else {
            $erro = 'Por favor, insira um nome válido e uma URL válida de imagem. (Formatos suportados: .jpg .jpeg .png .gif .bmp .webp)';
        }
        
    }

    if ($action === 'delete') {
        $index = (int)$_POST['index'];
        if (isset($bolos[$index])) {
            unset($bolos[$index]);
            $bolos = array_values($bolos);
            file_put_contents($bolosFilePath, json_encode($bolos, JSON_PRETTY_PRINT));
            header('Location: admin.php');
            exit();
        }
    }

    if ($action === 'edit') {
        $index = (int)$_POST['index'];
        $novoNome = trim($_POST['nome']);
        $novaImagem = trim($_POST['imagem']);

        if (isset($bolos[$index]) && !empty($novoNome) && filter_var($novaImagem, FILTER_VALIDATE_URL)) {
            $bolos[$index]['nome'] = $novoNome;
            $bolos[$index]['imagem'] = $novaImagem;
            file_put_contents($bolosFilePath, json_encode($bolos, JSON_PRETTY_PRINT));
            header('Location: admin.php');
            exit();
        } else {
            $erro = 'Por favor, insira um nome válido e uma URL válida.';
        }
    }
}

$erro = $erro ?? null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../CSS/style.css" rel="stylesheet">
    <style>
        .card-body-tabela {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="body-admin">
    <div class="container mt-5">
        <h1 class="text-center">Painel de Administração</h1>
        <div class="text-center mb-4">
            <a href="/bolos/admin/signin.php" class="btn btn-primary btn-equal">Criar Novo Usuário</a>
            <a href="/bolos" class="btn btn-primary btn-equal">Catálogo</a>
        </div>


        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card card-personalizado-admin h-100">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Criar Bolo</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Bolo</label>
                                <input type="text" class="form-control card-personalizado-admin-form" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="imagem" class="form-label">URL da Imagem</label>
                                <input type="url" class="form-control card-personalizado-admin-form" id="imagem" name="imagem" required>
                            </div>
                            <button type="submit" class="btn btn-success">Adicionar Bolo</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card card-personalizado-admin h-100">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Bolos Cadastrados</h2>
                    </div>
                    <div class="card-body card-body-tabela">
                        <ul class="list-group">
                            <?php if (isset($bolos)): ?>
                                <?php foreach ($bolos as $index => $bolo): ?>
                                    <li class="list-group-item d-flex flex-column card-personalizado-admin">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?= htmlspecialchars($bolo['nome']) ?></span>
                                            <div>
                                            <form method="POST" style="display:inline;" id="delete-form-<?= $index ?>" onsubmit="return confirmDelete(<?= $index ?>)">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="index" value="<?= $index ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                                <button class="btn btn-primary btn-sm ms-2" onclick="mostrarFormularioEdicao(<?= $index ?>)">Editar</button>
                                            </div>
                                        </div>
                                        <form method="POST" id="form-edicao-<?= $index ?>" class="mt-2" style="display:none;">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="index" value="<?= $index ?>">
                                            <div class="mb-2">
                                                <input type="text" class="form-control" name="nome" placeholder="Novo nome do bolo" value="<?= htmlspecialchars($bolo['nome']) ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <input type="url" class="form-control" name="imagem" placeholder="Nova URL da imagem" value="<?= htmlspecialchars($bolo['imagem']) ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm">Salvar</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="esconderFormularioEdicao(<?= $index ?>)">Cancelar</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(index) {
            if (confirm('Tem certeza que deseja excluir este bolo?')) {
                document.getElementById(`delete-form-${index}`).submit();
            }
            return false;
        }   

        function mostrarFormularioEdicao(index) {
            document.getElementById(`form-edicao-${index}`).style.display = 'block';
        }

        function esconderFormularioEdicao(index) {
            document.getElementById(`form-edicao-${index}`).style.display = 'none';
        }
    </script>
</body>
</html>


