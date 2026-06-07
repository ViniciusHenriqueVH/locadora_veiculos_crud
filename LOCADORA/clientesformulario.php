<?php
include_once 'conexao.php';

// Ativa exibição de erros total para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = $_GET['id'] ?? null;
$nome = ''; $data_nasc = ''; $cidade = '';
$mensagem_sucesso = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM CLIENTE WHERE ID_CLIENTE = :id");
    $stmt->execute(['id' => $id]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($c) {
        $nome = $c['NOME'];
        $data_nasc = $c['DATA_NASCIMENTO'];
        $cidade = $c['CIDADE'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $data_nasc = $_POST['data_nascimento'];
    $cidade = $_POST['cidade'];

    if (empty($nome) || empty($data_nasc) || empty($cidade)) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE CLIENTE SET NOME = :n, DATA_NASCIMENTO = :d, CIDADE = :c WHERE ID_CLIENTE = :id");
                $stmt->execute(['n' => $nome, 'd' => $data_nasc, 'c' => $cidade, 'id' => $id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO CLIENTE (NOME, DATA_NASCIMENTO, CIDADE) VALUES (:n, :d, :c)");
                $stmt->execute(['n' => $nome, 'd' => $data_nasc, 'c' => $cidade]);
            }
            
            // Mensagem de segurança caso o redirecionamento falhe
            echo "<script>alert('Cliente salvo com sucesso!'); window.location.href='clienteslista.php';</script>";
            exit;
            
        } catch (PDOException $e) {
            $erro = "Erro no Banco de Dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f8f9fa; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], input[type="date"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%; }
        .btn-save:hover { background: #218838; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= $id ? 'Alterar Cliente' : 'Incluir Novo Cliente' ?></h1>
        <hr><br>
        
        <?php if (isset($erro)): ?>
            <div class="error-msg"><?= $erro ?></div>
        <?php endif; ?>

        <form action="clientesformulario.php<?= isset($id) ? '?id='.$id : '' ?>" method="POST">
            <div class="form-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
            </div>
            <div class="form-group">
                <label>Data de Nascimento:</label>
                <input type="date" name="data_nascimento" value="<?= $data_nasc ?>" required>
            </div>
            <div class="form-group">
                <label>Cidade:</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($cidade) ?>" required>
            </div>
            <button type="submit" class="btn-save">Salvar Registro</button>
            <p style="text-align: center; margin-top: 15px;"><a href="clienteslista.php" style="color: #0056b3; text-decoration: none; font-weight: bold;">← Cancelar e Voltar</a></p>
        </form>
    </div>
</body>
</html>