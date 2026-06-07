<?php
include_once 'conexao.php';
include_once 'funcoes.php';

// Ativa a exibição de erros na tela para garantir que nada fique em branco
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['novo_aluguel'])) {
    $id_cliente = $_POST['id_cliente'];
    $id_veiculo = $_POST['id_veiculo'];
    $data_retirada = $_POST['data_retirada'];
    $data_prevista = $_POST['data_prevista'];

    if (!verificarDisponibilidade($id_veiculo, $conn)) {
        echo "<script>alert('Este veículo não está disponível para aluguel.'); window.location='alugueislista.php';</script>";
        exit;
    }

    $d1 = new DateTime($data_retirada);
    $d2 = new DateTime($data_prevista);
    $dias_contratados = $d1->diff($d2)->days;
    if($dias_contratados <= 0) $dias_contratados = 1;

    $valores = calcularValoresIniciais($id_cliente, $id_veiculo, $dias_contratados, $conn);

    $sql = "INSERT INTO ALUGA (FK_CLIENTE_ID_CLIENTE, FK_VEICULOS_ID_VEICULOS, DATA_RETIRADA, DATA_PREVISTA, DIAS_CONTRATADOS, VALOR_BRUTO, DESCONTO, MULTA, VALOR_FINAL, STATUS) 
            VALUES (:id_c, :id_v, :dt_r, :dt_p, :dias, :bruto, :desc, 0.00, :final, 'Em andamento')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_c' => $id_cliente, 'id_v' => $id_veiculo, 'dt_r' => $data_retirada, 'dt_p' => $data_prevista,
        'dias' => $dias_contratados, 'bruto' => $valores['bruto'], 'desc' => $valores['desconto'], 'final' => $valores['final']
    ]);

    $conn->prepare("UPDATE VEICULOS SET STATUS = 'alugado' WHERE ID_VEICULOS = :id_v")->execute(['id_v' => $id_veiculo]);
    
    header("Location: alugueislista.php");
    exit;
}

if (isset($_POST['devolver_carro'])) {
    $id_locacao = $_POST['id_locacao'];
    $id_cliente = $_POST['fk_cliente'];
    $id_veiculo = $_POST['fk_veiculo'];
    $dt_retirada = $_POST['dt_retirada'];
    $dt_prevista = $_POST['dt_prevista'];
    $dt_real = $_POST['data_devolucao'];

    $baixa = processarDevolucao($id_cliente, $id_veiculo, $dt_retirada, $dt_prevista, $dt_real, $conn);

    // Corrigido para atualizar usando a chave primária correta ID_LOCACAO
    $sql = "UPDATE ALUGA SET DATA_DEVOLUCAO = :dt_dev, DIAS_REAIS = :dias_r, MULTA = :multa, VALOR_FINAL = (VALOR_BRUTO - DESCONTO + :multa), STATUS = :st 
            WHERE ID_LOCACAO = :id_loc";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'dt_dev' => $dt_real, 'dias_r' => $baixa['dias_reais'], 'multa' => $baixa['multa'], 
        'st' => $baixa['status'], 'id_loc' => $id_locacao
    ]);

    $conn->prepare("UPDATE VEICULOS SET STATUS = 'disponivel' WHERE ID_VEICULOS = :id_v")->execute(['id_v' => $id_veiculo]);

    header("Location: alugueislista.php");
    exit;
}

$clientes = $conn->query("SELECT * FROM CLIENTE")->fetchAll(PDO::FETCH_ASSOC);
$veiculos = $conn->query("SELECT * FROM VEICULOS WHERE STATUS = 'disponivel'")->fetchAll(PDO::FETCH_ASSOC);
$locacoes = $conn->query("SELECT l.*, c.NOME, v.NOME_VEICULO FROM ALUGA l JOIN CLIENTE c ON l.FK_CLIENTE_ID_CLIENTE = c.ID_CLIENTE JOIN VEICULOS v ON l.FK_VEICULOS_ID_VEICULOS = v.ID_VEICULOS")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Locações</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .bloco { background: #eee; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #333; color: white; }
    </style>
</head>
<body>
    <h1>Painel de Aluguéis</h1>
    <a href="index.php" style="font-weight: bold; color: #0056b3;">Voltar ao Menu Principal</a><br><br>

    <div class="bloco">
        <h3>Registrar Nova Locação</h3>
        <form method="POST">
            <label>Cliente:</label>
            <select name="id_cliente" required>
                <option value="">-- Selecione --</option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['ID_CLIENTE'] ?>"><?= $c['NOME'] ?></option>
                <?php endforeach; ?>
            </select>

            <label style="margin-left: 15px;">Veículo Disponível:</label>
            <select name="id_veiculo" required>
                <option value="">-- Selecione --</option>
                <?php foreach($veiculos as $v): ?>
                    <option value="<?= $v['ID_VEICULOS'] ?>"><?= $v['NOME_VEICULO'] ?> (R$ <?= $v['VALOR_DIARIA'] ?>)</option>
                <?php endforeach; ?>
            </select>

            <br><br>
            <label>Data Retirada:</label>
            <input type="date" name="data_retirada" required>

            <label style="margin-left: 15px;">Data Prevista:</label>
            <input type="date" name="data_prevista" required>

            <button type="submit" name="novo_aluguel" style="margin-left: 15px; background: #28a745; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius:3px;">Confirmar Locação</button>
        </form>
    </div>

    <h3>Histórico Geral de Movimentações</h3>
    <table>
        <tr>
            <th>ID</th><th>Cliente</th><th>Veículo</th><th>Retirada</th><th>Prevista</th><th>Devolução</th><th>Valor Final</th><th>Status</th><th>Ações de Baixa</th>
        </tr>
        <?php foreach($locacoes as $l): ?>
        <tr>
            <td><?= $l['ID_LOCACAO'] ?></td>
            <td><?= $l['NOME'] ?></td>
            <td><?= $l['NOME_VEICULO'] ?></td>
            <td><?= date('d/m/Y', strtotime($l['DATA_RETIRADA'])) ?></td>
            <td><?= date('d/m/Y', strtotime($l['DATA_PREVISTA'])) ?></td>
            <td><?= $l['DATA_DEVOLUCAO'] ? date('d/m/Y', strtotime($l['DATA_DEVOLUCAO'])) : 'Com o cliente' ?></td>
            <td>R$ <?= number_format($l['VALOR_FINAL'], 2, ',', '.') ?></td>
            <td><strong><?= $l['STATUS'] ?></strong></td>
            <td>
                <?php if($l['STATUS'] === 'Em andamento'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_locacao" value="<?= $l['ID_LOCACAO'] ?>">
                        <input type="hidden" name="fk_cliente" value="<?= $l['FK_CLIENTE_ID_CLIENTE'] ?>">
                        <input type="hidden" name="fk_veiculo" value="<?= $l['FK_VEICULOS_ID_VEICULOS'] ?>">
                        <input type="hidden" name="dt_retirada" value="<?= $l['DATA_RETIRADA'] ?>">
                        <input type="hidden" name="dt_prevista" value="<?= $l['DATA_PREVISTA'] ?>">
                        <input type="date" name="data_devolucao" required>
                        <button type="submit" name="devolver_carro" style="background: #dc3545; color: white; border: none; padding: 3px 8px; cursor: pointer; border-radius:3px;">Dar Baixa</button>
                    </form>
                <?php else: ?>
                    <span style="color: green; font-weight: bold;">✔ Concluído</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>