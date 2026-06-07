<?php
include_once 'conexao.php';

if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    try {
        $stmt = $conn->prepare("DELETE FROM CLIENTE WHERE ID_CLIENTE = :id");
        $stmt->execute(['id' => $id]);
        header("Location: clienteslista.php");
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Não é possível excluir! Este cliente possui histórico de locação.'); window.location='clienteslista.php';</script>";
    }
}

$dados = $conn->query("SELECT * FROM CLIENTE")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Clientes Cadastrados</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #0056b3; color: white; }
        .btn { padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px; font-size: 13px; }
        .add { background: #28a745; padding: 10px 15px; }
        .edit { background: #ffc107; color: black; }
        .del { background: #dc3545; }
    </style>
</head>
<body>
    <h1>Controle de Clientes</h1>
    <a href="clientesformulario.php" class="btn add">Novo Cliente</a>
    <a href="index.php" style="margin-left: 15px;">Voltar ao Menu</a>

    <table>
        <tr>
            <th>ID</th><th>Nome</th><th>Data Nascimento</th><th>Cidade</th><th>Ações</th>
        </tr>
        <?php foreach ($dados as $c): ?>
        <tr>
            <td><?= $c['ID_CLIENTE'] ?></td>
            <td><?= $c['NOME'] ?></td>
            <td><?= date('d/m/Y', strtotime($c['DATA_NASCIMENTO'])) ?></td>
            <td><?= $c['CIDADE'] ?></td>
            <td>
                <a href="clientesformulario.php?id=<?= $c['ID_CLIENTE'] ?>" class="btn edit">Alterar</a>
                <a href="clienteslista.php?excluir=<?= $c['ID_CLIENTE'] ?>" class="btn del" onclick="return confirm('Deseja excluir este cliente?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>