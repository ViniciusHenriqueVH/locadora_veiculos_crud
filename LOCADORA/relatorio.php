<?php
include_once 'conexao.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Desempenho Mensal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8f9fa; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; background: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #0056b3; color: white; }
        h2 { color: #333; border-bottom: 2px solid #0056b3; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Métricas e Relatórios do Período: <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?>/<?= $ano ?></h1>
    <a href="index.php" style="font-weight: bold; color: #0056b3;">Voltar ao Menu</a><br><br>

    <h2>Relatório 1 — Clientes</h2>
    <table>
        <tr>
            <th>Cliente</th><th>Idade</th><th>Cidade</th><th>Total de Locações</th><th>Dias Alugados</th><th>Valor Total Gasto</th><th>Desconto Recebido</th><th>Multas Pagas</th><th>Ticket Médio</th><th>Classificação</th>
        </tr>
        <?php
        $sql1 = "SELECT c.NOME, DATEDIFF(YEAR, c.DATA_NASCIMENTO, GETDATE()) as IDADE, c.CIDADE, COUNT(l.ID_LOCACAO) as TOTAL_LOCACOES, SUM(ISNULL(l.DIAS_REAIS, l.DIAS_CONTRATADOS)) as DIAS_ALUGADOS, SUM(l.VALOR_FINAL) as VALOR_TOTAL_GASTO, SUM(l.DESCONTO) as DESCONTO_RECEBIDO, SUM(l.MULTA) as MULTAS_PAGAS, AVG(l.VALOR_FINAL) as TICKET_MEDIO, CASE WHEN COUNT(l.ID_LOCACAO) >= 5 THEN 'Premium' WHEN COUNT(l.ID_LOCACAO) >= 3 THEN 'Fiel' ELSE 'Regular' END as CLASSIFICACAO FROM CLIENTE c JOIN ALUGA l ON c.ID_CLIENTE = l.FK_CLIENTE_ID_CLIENTE WHERE MONTH(l.DATA_RETIRADA) = :m AND YEAR(l.DATA_RETIRADA) = :a GROUP BY c.ID_CLIENTE, c.NOME, c.DATA_NASCIMENTO, c.CIDADE";
        $stmt1 = $conn->prepare($sql1); $stmt1->execute(['m' => $mes, 'a' => $ano]);
        while($r = $stmt1->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= $r['NOME'] ?></td><td><?= $r['IDADE'] ?></td><td><?= $r['CIDADE'] ?></td><td><?= $r['TOTAL_LOCACOES'] ?></td><td><?= $r['DIAS_ALUGADOS'] ?></td>
                <td>R$ <?= number_format($r['VALOR_TOTAL_GASTO'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['DESCONTO_RECEBIDO'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['MULTAS_PAGAS'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['TICKET_MEDIO'], 2, ',', '.') ?></td><td><strong><?= $r['CLASSIFICACAO'] ?></strong></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Relatório 2 — Veículos</h2>
    <table>
        <tr>
            <th>Veículo</th><th>Placa</th><th>Categoria</th><th>Valor Diária</th><th>Quantidade de Locações</th><th>Dias Alugados</th><th>Receita Bruta</th><th>Receita Líquida</th><th>Multas Geradas</th><th>Taxa de Ocupação</th><th>Status</th>
        </tr>
        <?php
        $sql2 = "SELECT v.NOME_VEICULO, v.PLACA, v.CATEGORIA, v.VALOR_DIARIA, COUNT(l.ID_LOCACAO) as QTD_LOCACOES, SUM(ISNULL(l.DIAS_REAIS, l.DIAS_CONTRATADOS)) as DIAS_ALUGADOS, SUM(l.VALOR_BRUTO) as RECEITA_BRUTA, SUM(l.VALOR_FINAL) as RECEITA_LIQUIDA, SUM(l.MULTA) as MULTAS_GERADAS, CAST((SUM(ISNULL(l.DIAS_REAIS, l.DIAS_CONTRATADOS)) * 100.0 / 30.0) AS INT) as TAXA_OCUPACAO FROM VEICULOS v LEFT JOIN ALUGA l ON v.ID_VEICULOS = l.FK_VEICULOS_ID_VEICULOS AND MONTH(l.DATA_RETIRADA) = :m AND YEAR(l.DATA_RETIRADA) = :a GROUP BY v.ID_VEICULOS, v.NOME_VEICULO, v.PLACA, v.CATEGORIA, v.VALOR_DIARIA";
        $stmt2 = $conn->prepare($sql2); $stmt2->execute(['m' => $mes, 'a' => $ano]);
        while($r = $stmt2->fetch(PDO::FETCH_ASSOC)): 
            $status_u = (($r['TAXA_OCUPACAO'] ?? 0) > 50) ? 'Alta utilização' : 'Média utilização'; ?>
            <tr>
                <td><?= $r['NOME_VEICULO'] ?></td><td><?= $r['PLACA'] ?></td><td><?= $r['CATEGORIA'] ?></td><td>R$ <?= number_format($r['VALOR_DIARIA'], 2, ',', '.') ?></td><td><?= $r['QTD_LOCACOES'] ?></td><td><?= $r['DIAS_ALUGADOS'] ?? 0 ?></td>
                <td>R$ <?= number_format($r['RECEITA_BRUTA'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['RECEITA_LIQUIDA'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['MULTAS_GERADAS'], 2, ',', '.') ?></td><td><?= $r['TAXA_OCUPACAO'] ?? 0 ?>%</td><td><strong><?= $status_u ?></strong></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Relatório 3 — Locações</h2>
    <table>
        <tr>
            <th>ID Locação</th><th>Cliente</th><th>Veículo</th><th>Categoria</th><th>Data Retirada</th><th>Data Prevista</th><th>Data Devolução</th><th>Dias Contratados</th><th>Dias Reais</th><th>Valor Bruto</th><th>Desconto</th><th>Multa</th><th>Valor Final</th><th>Status</th>
        </tr>
        <?php
        $sql3 = "SELECT l.ID_LOCACAO, c.NOME as cliente, v.NOME_VEICULO as veiculo, v.CATEGORIA, CONVERT(VARCHAR, l.DATA_RETIRADA, 103) as dt_ret, CONVERT(VARCHAR, l.DATA_PREVISTA, 103) as dt_prev, CONVERT(VARCHAR, l.DATA_DEVOLUCAO, 103) as dt_dev, l.DIAS_CONTRATADOS, l.DIAS_REAIS, l.VALOR_BRUTO, l.DESCONTO, l.MULTA, l.VALOR_FINAL, l.STATUS FROM ALUGA l JOIN CLIENTE c ON l.FK_CLIENTE_ID_CLIENTE = c.ID_CLIENTE JOIN VEICULOS v ON l.FK_VEICULOS_ID_VEICULOS = v.ID_VEICULOS WHERE MONTH(l.DATA_RETIRADA) = :m AND YEAR(l.DATA_RETIRADA) = :a";
        $stmt3 = $conn->prepare($sql3); $stmt3->execute(['m' => $mes, 'a' => $ano]);
        while($r = $stmt3->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= $r['ID_LOCACAO'] ?></td><td><?= $r['cliente'] ?></td><td><?= $r['veiculo'] ?></td><td><?= $r['CATEGORIA'] ?></td><td><?= $r['dt_ret'] ?></td><td><?= $r['dt_prev'] ?></td><td><?= $r['dt_dev'] ?? '-' ?></td><td><?= $r['DIAS_CONTRATADOS'] ?></td><td><?= $r['DIAS_REAIS'] ?? '-' ?></td>
                <td>R$ <?= number_format($r['VALOR_BRUTO'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['DESCONTO'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['MULTA'], 2, ',', '.') ?></td><td>R$ <?= number_format($r['VALOR_FINAL'], 2, ',', '.') ?></td><td><strong><?= $r['STATUS'] ?></strong></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>