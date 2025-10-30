<?php
defined('MOODLE_INTERNAL') || die();

// REMOVEMOS a linha que causava o erro:
// $PAGE->requires->string_for_plugin('enrol_pagbank', 'pluginname');

if ($ADMIN->fulltree) {

    // --- Configurações da API PagBank ---
    // USANDO STRINGS HARDCODED TEMPORARIAMENTE PARA CORRIGIR O ERRO DE CARREGAMENTO
    $settings->add(new admin_setting_heading(
        'enrol_pagbank/pagbanksettings',
        'PagBank API Settings', // Texto hardcoded
        ''
    ));

    // Token de Produção
    $settings->add(new admin_setting_configtext(
        'enrol_pagbank/bearertoken_prod',
        'Production Bearer Token', // Texto hardcoded
        'Your PagBank Production Bearer Token.', // Texto hardcoded
        '', 
        PARAM_TEXT
    ));

    // Token de Sandbox
    $settings->add(new admin_setting_configtext(
        'enrol_pagbank/bearertoken_sandbox',
        'Sandbox Bearer Token', // Texto hardcoded
        'Your PagBank Sandbox Bearer Token.', // Texto hardcoded
        '', 
        PARAM_TEXT
    ));

    // Modo Sandbox
    $settings->add(new admin_setting_configcheckbox(
        'enrol_pagbank/sandboxmode',
        'Sandbox Mode', // Texto hardcoded
        'Enable Sandbox mode for testing.', // Texto hardcoded
        0 // Desativado por padrão
    ));

    // URL do Webhook (Apenas informativo)
    global $CFG;
    $webhookurl = $CFG->wwwroot . '/enrol/pagbank/webhook.php';
    $settings->add(new admin_setting_heading(
       'enrol_pagbank/webhookurlinfo',
       'Webhook URL', // Texto hardcoded
       'Register this URL in your PagBank panel for receiving payment notifications:' . '<br><code>' . $webhookurl . '</code>' // Texto hardcoded
    ));
}