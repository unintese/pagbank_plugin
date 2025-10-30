<?php
// Este ficheiro recebe as notificações (Webhooks) do PagBank.
// Ele é chamado "servidor-para-servidor", não por um browser.

require_once('../../config.php');

// Define um nome de ficheiro de log
$logfilepath = $CFG->dataroot . '/pagbank_webhook_log.txt';

// Função simples para logar
function pagbank_log($message) {
    global $logfilepath;
    // Adiciona data e hora à mensagem
    $timestamped_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    // Escreve no ficheiro de log
    file_put_contents($logfilepath, $timestamped_message, FILE_APPEND);
}

// Cabeçalho para responder ao PagBank que recebemos (mesmo que ainda não processemos)
// É crucial responder 200 OK, senão o PagBank continuará a tentar enviar.
http_response_code(200);

pagbank_log("==== Webhook Recebido ====");

// Obtém o corpo (Body) da requisição POST (que será JSON)
$jsonbody = file_get_contents('php://input');

// Loga o JSON bruto que o PagBank enviou
pagbank_log("Corpo (JSON) recebido: " . $jsonbody);

// Tenta descodificar o JSON
$data = json_decode($jsonbody);

if (json_last_error() === JSON_ERROR_NONE) {
    // Loga os dados descodificados
    pagbank_log("JSON descodificado com sucesso.");

    // ---- LÓGICA FUTURA (A SER IMPLEMENTADA) ----
    // 1. Validar a assinatura do Webhook (ex: 'x-pagbank-signature') - CRÍTICO
    
    // 2. Extrair o $order_id (ou $reference_id) e o $status do $data
    //    ex: $reference_id = $data->reference_id;
    //    ex: $status = $data->status; // (ou o caminho correto no JSON)

    // 3. Encontrar a inscrição pendente no Moodle usando o $reference_id
    
    // 4. Se $status == 'PAID' (ou o equivalente de 'PAGO'):
    //    - Chamar a API de inscrição do Moodle para matricular o aluno.
    //    - pagbank_log("Matriculando utilizador para reference_id: $reference_id");

    // 5. Se $status == 'CANCELED' / 'DECLINED' / 'REFUNDED':
    //    - Atualizar a inscrição para suspensa ou cancelada.
    //    - pagbank_log("Cancelando/Reembolsando inscrição para reference_id: $reference_id");
    // ---- FIM DA LÓGICA FUTURA ----

} else {
    // Loga se o JSON recebido for inválido
    pagbank_log("Erro ao descodificar o JSON recebido.");
}

pagbank_log("==== Fim do Webhook ====");

// Termina a execução
die();