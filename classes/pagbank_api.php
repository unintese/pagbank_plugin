<?php
namespace enrol_pagbank;

defined('MOODLE_INTERNAL') || die();

class pagbank_api {

    /**
     * Obtém as configurações da API (Token e URL do endpoint) com base no modo Sandbox/Produção.
     * @return \stdClass (contém ->token e ->endpointurl)
     */
    private static function get_api_config() {
        global $CFG;

        $config = get_config('enrol_pagbank');
        $istesting = !empty($config->sandboxmode);

        $output = new \stdClass();

        if ($istesting) {
            $output->token = $config->bearertoken_sandbox;
            $output->endpointurl = 'https://api.sandbox.pagbank.com.br/orders';
        } else {
            $output->token = $config->bearertoken_prod;
            $output->endpointurl = 'https://api.pagbank.com.br/orders';
        }

        if (empty($output->token)) {
            throw new \moodle_exception('Bearer Token do PagBank não configurado.');
        }

        return $output;
    }

    /**
     * Cria um novo Pedido (Order) na API do PagBank.
     *
     * @param \stdClass $course O objeto do curso do Moodle.
     * @param \stdClass $instance A instância de inscrição (contém o preço).
     * @param \stdClass $user O objeto do utilizador (aluno) do Moodle.
     * @return \stdClass O objeto JSON descodificado da resposta do PagBank.
     * @throws \moodle_exception Se a chamada à API falhar.
     */
    public static function create_order(\stdClass $course, \stdClass $instance, \stdClass $user) {
        global $CFG;

        $apiconfig = self::get_api_config();

        // 1. Montar o JSON
        // O preço deve ser em centavos.
        $itemamount = (int)($instance->cost * 100);
        
        // ID de referência único para ligar ao Moodle
        $reference_id = 'MDL-' . $instance->id . '-' . $user->id . '-' . time();

        // URL do Webhook
        $webhookurl = $CFG->wwwroot . '/enrol/pagbank/webhook.php';

        // --- DADOS DO CLIENTE (CRUCIAL) ---
        // A API do PagBank EXIGE Nome, E-mail, CPF e Telefone.
        // O Moodle só tem Nome e E-mail por padrão.
        // Para este TESTE, vamos usar dados FIXOS (HARDCODED) de teste.
        // Num plugin real, teríamos um formulário para o aluno preencher o CPF/Telefone.
        
        $customer_data = [
            'name' => fullname($user),
            'email' => $user->email,
            'tax_id' => '01234567890', // CPF de TESTE (Hardcoded)
            'phones' => [
                [
                    'country' => '55',
                    'area' => '11',
                    'number' => '999999999', // Telefone de TESTE (Hardcoded)
                    'type' => 'MOBILE'
                ]
            ]
        ];

        // --- JSON Body Completo ---
        $payload = [
            'reference_id' => $reference_id,
            'customer' => $customer_data,
            'items' => [
                [
                    'name' => $course->fullname,
                    'quantity' => 1,
                    'unit_amount' => $itemamount
                ]
            ],
            // Vamos pedir um QR Code de PIX, que é o mais simples de testar
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $itemamount
                    ],
                    // Pode definir uma data de expiração
                    'expiration_date' => '2025-10-31T23:59:59-03:00', 
                ]
            ],
            'notification_urls' => [ $webhookurl ]
        ];

        $jsonbody = json_encode($payload);

        // 2. Fazer a Chamada à API (usando o curl_helper do Moodle)
        $http = new \core\http\curl_helper();
        $http->set_timeout(30); // 30 segundos de timeout

        $headers = [
            'Authorization: Bearer ' . $apiconfig->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // Escreve no log de webhook para depuração
        pagbank_log("Enviando para PagBank: " . $jsonbody);

        $response = $http->post($apiconfig->endpointurl, $jsonbody, $headers);

        // 3. Processar a Resposta
        if ($http->get_errno() !== 0) {
            pagbank_log("Erro cURL: " . $http->get_error());
            throw new \moodle_exception('Erro de comunicação com a API PagBank: ' . $http->get_error());
        }

        $statuscode = $http->get_info(CURLINFO_HTTP_CODE);
        pagbank_log("Resposta PagBank (Status $statuscode): " . $response);

        $responseobj = json_decode($response);

        if ($statuscode >= 300 || $responseobj === null) {
            $errormsg = $response;
            if (isset($responseobj->error_messages)) {
                $errormsg = $responseobj->error_messages[0]->description;
            }
            throw new \moodle_exception("API PagBank retornou um erro ($statuscode): " . $errormsg);
        }

        // SUCESSO!
        // $responseobj contém o Pedido (Order) criado, incluindo o QR Code.
        return $responseobj;
    }

    // (Futuramente, adicione aqui métodos para processar pagamentos com cartão, etc.)
}

// Função de log (do webhook.php) movida para cá para ser global
function pagbank_log($message) {
    global $CFG;
    $logfilepath = $CFG->dataroot . '/pagbank_webhook_log.txt';
    $timestamped_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    file_put_contents($logfilepath, $timestamped_message, FILE_APPEND);
}