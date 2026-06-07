<?php
function verificarDisponibilidade($id_veiculo, $conn) {
    $stmt = $conn->prepare("SELECT STATUS FROM VEICULOS WHERE ID_VEICULOS = :id");
    $stmt->execute(['id' => $id_veiculo]);
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($veiculo && $veiculo['STATUS'] === 'disponivel');
}

function calcularValoresIniciais($id_cliente, $id_veiculo, $dias_contratados, $conn) {
    $stmt = $conn->prepare("SELECT VALOR_DIARIA FROM VEICULOS WHERE ID_VEICULOS = :id");
    $stmt->execute(['id' => $id_veiculo]);
    $valor_diaria = $stmt->fetch(PDO::FETCH_ASSOC)['VALOR_DIARIA'];

    $valor_bruto = $dias_contratados * $valor_diaria;

    $stmt = $conn->prepare("SELECT COUNT(ID_LOCACAO) as total FROM ALUGA WHERE FK_CLIENTE_ID_CLIENTE = :id");
    $stmt->execute(['id' => $id_cliente]);
    $total_historico = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $desc_fidelidade = 0;
    if ($total_historico >= 10) $desc_fidelidade = 0.15;
    elseif ($total_historico >= 5) $desc_fidelidade = 0.10;
    elseif ($total_historico >= 3) $desc_fidelidade = 0.05;

    $desc_dias = 0;
    if ($dias_contratados > 15) $desc_dias = 0.15;
    elseif ($dias_contratados > 7) $desc_dias = 0.10;

    $percentual_desconto = max($desc_fidelidade, $desc_dias);
    $valor_desconto = $valor_bruto * $percentual_desconto;
    $valor_final = $valor_bruto - $valor_desconto;

    return [
        'bruto' => $valor_bruto,
        'desconto' => $valor_desconto,
        'final' => $valor_final
    ];
}

function processarDevolucao($id_cliente, $id_veiculo, $data_retirada, $data_prevista, $data_devolucao_real, $conn) {
    $stmt = $conn->prepare("SELECT VALOR_DIARIA FROM VEICULOS WHERE ID_VEICULOS = :id");
    $stmt->execute(['id' => $id_veiculo]);
    $valor_diaria = $stmt->fetch(PDO::FETCH_ASSOC)['VALOR_DIARIA'];

    $retirada = new DateTime($data_retirada);
    $prevista = new DateTime($data_prevista);
    $real = new DateTime($data_devolucao_real);

    $dias_reais = $retirada->diff($real)->days;
    $dias_atraso = $prevista->diff($real)->format("%r%a");

    $multa = 0.00;
    $status_final = 'Entregue';

    if ($dias_atraso > 0) {
        $multa = $dias_atraso * $valor_diaria * 1.2;
        $status_final = 'Atrasado';
    } elseif ($dias_atraso < 0) {
        $status_final = 'Entregue Antecipado';
    }

    return [
        'dias_reais' => $dias_reais,
        'multa' => $multa,
        'status' => $status_final
    ];
}
?>