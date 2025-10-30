<?php
require_once('../../config.php');
require_once($CFG->libdir.'/enrollib.php');
require_once('classes/pagbank_api.php'); // Inclui a nossa nova classe

// Parâmetros necessários
$courseid = required_param('id', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);

// Obtém informações
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('id' => $instanceid, 'enrol' => 'pagbank', 'courseid' => $course->id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Verifica se o utilizador está logado
require_login();
global $USER; // Garante que $USER está disponível

// Verifica se o método de inscrição está ativo e se o utilizador pode se inscrever
$plugin = enrol_get_plugin('pagbank');
if ($instance->status != ENROL_INSTANCE_ENABLED || !$plugin->can_self_enrol($instance)) {
    print_error('cannotenrol', 'enrol');
}

// Configurações da página Moodle
$PAGE->set_url('/enrol/pagbank/enrol.php', array('id' => $courseid, 'instance' => $instanceid));
$PAGE->set_context($context);
$PAGE->set_title(get_string('enrolmentstart', 'enrol_pagbank', format_string($course->fullname)));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('standard'); 

// Mostra o cabeçalho
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('enrolmentstart', 'enrol_pagbank'));

try {
    // ---- ESTA É A NOVA LÓGICA ----
    // Tenta criar o pedido na API do PagBank
    echo $OUTPUT->notification('A contactar o PagBank Sandbox. Aguarde...', 'notifysuccess');

    $order = \enrol_pagbank\pagbank_api::create_order($course, $instance, $USER);

    // Se funcionou, $order contém a resposta do PagBank.
    // Vamos apenas imprimir a resposta no ecrã para o nosso teste.
    echo $OUTPUT->heading('Pedido (Order) criado com sucesso no Sandbox!', 3);
    echo $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter');
    echo '<pre>';
    print_r($order);
    echo '</pre>';
    echo $OUTPUT->box_end();

    // Num plugin real, aqui mostraríamos o QR Code de PIX
    // Ex: $qrcode_url = $order->qr_codes[0]->links[0]->href;
    // Ex: echo '<img src="' . $qrcode_url . '" alt="Pague com PIX">';
    
    // E também o formulário para Cartão de Crédito (Passo seguinte)

} catch (Exception $e) {
    // Se falhar, mostra o erro
    echo $OUTPUT->notification('Erro ao criar o pedido no PagBank:');
    echo $OUTPUT->box($e->getMessage(), 'errorbox');
    \enrol_pagbank\pagbank_log("ERRO FATAL em enrol.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

// Mostra o rodapé
echo $OUTPUT->footer();