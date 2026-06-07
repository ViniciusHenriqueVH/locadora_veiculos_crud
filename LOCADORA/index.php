<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Locadora 2026</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f7f6; }
        .box { background: white; padding: 25px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #0056b3; margin-top: 0; }
        a { display: inline-block; padding: 10px 20px; background: #0056b3; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        a:hover { background: #004085; }
        .btn-rel { background: #28a745; border: none; padding: 10px 20px; color: white; border-radius: 4px; cursor: pointer; }
        .btn-rel:hover { background: #218838; }
    </style>
</head>
<body>
    <h1>Sistema de Gestão de Locadora v2026</h1>
    <hr><br>

    <div class="box">
        <h2>Operações do Sistema </h2>
        <a href="clienteslista.php">Gerenciar Clientes</a>
        <a href="veiculoslista.php">Gerenciar Veículos</a>
        <a href="alugueislista.php">Efetuar Locações / Devoluções</a>
    </div>

    <div class="box">
        <h2>Métricas e Relatórios Analíticos</h2>
        <form action="relatorio.php" method="GET">
            <label>Mês de Referência:</label>
            <input type="number" name="mes" min="1" max="12" value="5" required style="width: 60px; padding: 5px;">
            
            <label style="margin-left: 15px;">Ano:</label>
            <input type="number" name="ano" value="2026" required style="width: 80px; padding: 5px;">
            
            <button type="submit" class="btn-rel" style="margin-left: 15px;">Gerar Relatórios Mensais</button>
        </form>
    </div>
</body>
</html>