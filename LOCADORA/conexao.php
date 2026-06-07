<?php
// Ativa exibição de erros para diagnóstico rápido
ini_set('display_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost"; 
$database = "LocadoraDB";
$uid = ""; 
$pwd = ""; 

try {
    // Tenta conectar usando o driver do SQL Server (utilizado nas imagens do seu modelo relacional)
    if (extension_loaded('pdo_sqlsrv')) {
        $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $uid, $pwd);
    } else {
        // Alternativa caso seu ambiente local esteja usando MySQL/XAMPP padrão por engano
        $conn = new PDO("mysql:host=$serverName;dbname=$database;charset=utf8", "root", "");
    }
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<div style='background:#f8d7da; color:#721c24; padding:20px; margin:20px; border:1px solid #f5c6cb; border-radius:5px;'>";
    echo "<h3>🚨 Erro de Conexão com o Banco de Dados:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Dica:</strong> Certifique-se de que o banco de dados chamado <u>$database</u> existe e o servidor está ativo.</p>";
    echo "</div>";
    die();
}
?>