<?php
include_once 'conexao.php';

if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    try {
        $stmt = $conn->prepare("DELETE FROM VEICULOS WHERE ID_VEICULOS = :id");
        $stmt->execute(['id' => $id]);
        header("Location: veiculoslista.php");
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Erro ao excluir! Este veículo está vinculado a um histórico de aluguel.'); window.location='veiculoslista.php';</script>";
    }
}

$dados = $conn->query("SELECT * FROM VEICULOS")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Frota de Veículos</title>
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
    <h1>Controle da Frota (Veículos)</h1>
    <a href="veiculosformulario.php" class="btn add">Novo Veículo</a>
    <a href="index.php" style="margin-left: 15px;">Voltar ao Menu</a>

    <table>
        <tr>
            <th>ID</th><th>Modelo/Nome</th><th>Placa</th><th>Categoria</th><th>Valor Diária</th><th>Status Atual</th><th>Ações</th>
        </tr>
        <?php foreach ($dados as $v): ?>
        <tr>
            <td><?= $v['ID_VEICULOS'] ?></td>
            <td><?= $v['NOME_VEICULO'] ?></td>
            <td><?= $v['PLACA'] ?></td>
            <td><?= $v['CATEGORIA'] ?></td>
            <td>R$ <?= number_format($v['VALOR_DIARIA'], 2, ',', '.') ?></td>
            <td><strong><?= ucfirst($v['STATUS']) ?></strong></td>
            <td>
                <a href="veiculosformulario.php?id=<?= $v['ID_VEICULOS'] ?>" class="btn edit">Alterar</a>
                <a href="veiculoslista.php?excluir=<?= $v['ID_VEICULOS'] ?>" class="btn del" onclick="return confirm('Deseja excluir este veículo?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>