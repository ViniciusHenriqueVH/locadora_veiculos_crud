<?php
include_once 'conexao.php';

$id = $_GET['id'] ?? null;
$nome_veiculo = ''; $placa = ''; $categoria = ''; $valor_diaria = ''; $status = 'disponivel';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM VEICULOS WHERE ID_VEICULOS = :id");
    $stmt->execute(['id' => $id]);
    $v = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($v) {
        $nome_veiculo = $v['NOME_VEICULO'];
        $placa = $v['PLACA'];
        $categoria = $v['CATEGORIA'];
        $valor_diaria = $v['VALOR_DIARIA'];
        $status = $v['STATUS'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_veiculo = $_POST['nome_veiculo'];
    $placa = $_POST['placa'];
    $categoria = $_POST['categoria'];
    $valor_diaria = $_POST['valor_diaria'];
    $status = $_POST['status'];

    if (empty($nome_veiculo) || empty($placa) || empty($valor_diaria)) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        if ($id) {
            $stmt = $conn->prepare("UPDATE VEICULOS SET NOME_VEICULO = :n, PLACA = :p, CATEGORIA = :c, VALOR_DIARIA = :v, STATUS = :s WHERE ID_VEICULOS = :id");
            $stmt->execute(['n' => $nome_veiculo, 'p' => $placa, 'c' => $categoria, 'v' => $valor_diaria, 's' => $status, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO VEICULOS (NOME_VEICULO, PLACA, CATEGORIA, VALOR_DIARIA, STATUS) VALUES (:n, :p, :c, :v, :s)");
            $stmt->execute(['n' => $nome_veiculo, 'p' => $placa, 'c' => $categoria, 'v' => $valor_diaria, 's' => $status]);
        }
        header("Location: veiculoslista.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Veículo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 300px; padding: 8px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <h1><?= $id ? 'Alterar Veículo' : 'Incluir Novo Veículo' ?></h1>

    <?php if (isset($erro)): ?>
        <p style="color: red; font-weight: bold;"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nome do Veículo (Modelo):</label>
            <input type="text" name="nome_veiculo" value="<?= htmlspecialchars($nome_veiculo) ?>" required>
        </div>
        <div class="form-group">
            <label>Placa:</label>
            <input type="text" name="placa" value="<?= htmlspecialchars($placa) ?>" required>
        </div>
        <div class="form-group">
            <label>Categoria:</label>
            <select name="categoria">
                <option value="Sedan" <?= $categoria == 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                <option value="Hatch" <?= $categoria == 'Hatch' ? 'selected' : '' ?>>Hatch</option>
                <option value="SUV" <?= $categoria == 'SUV' ? 'selected' : '' ?>>SUV</option>
            </select>
        </div>
        <div class="form-group">
            <label>Valor da Diária (R$):</label>
            <input type="number" name="valor_diaria" step="0.01" value="<?= $valor_diaria ?>" required>
        </div>
        <div class="form-group">
            <label>Status Operacional:</label>
            <select name="status">
                <option value="disponivel" <?= $status == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                <option value="alugado" <?= $status == 'alugado' ? 'selected' : '' ?>>Alugado</option>
                <option value="manutencao" <?= $status == 'manutencao' ? 'selected' : '' ?>>Em Manutenção</option>
            </select>
        </div>
        <button type="submit" class="btn-save">Salvar Veículo</button>
        <a href="veiculoslista.php" style="margin-left: 15px;">Cancelar</a>
    </form>
</body>
</html>