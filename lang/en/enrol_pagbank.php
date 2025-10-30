<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'PagBank Payments';
$string['pluginname_desc'] = 'Allows users to pay for courses using PagBank.';
$string['status'] = 'Enable PagBank enrolment';
$string['status_desc'] = 'Allow PagBank to be used as an enrolment method.';
$string['cost'] = 'Enrolment cost';
$string['costerror'] = 'The enrolment cost must be numeric.';
$string['currency'] = 'Currency';
// --- Configurações da API ---
$string['pagbanksettings'] = 'PagBank API Settings';
$string['bearertoken_prod'] = 'Production Bearer Token';
$string['bearertoken_prod_desc'] = 'Your PagBank Production Bearer Token.';
$string['bearertoken_sandbox'] = 'Sandbox Bearer Token'; // Verifique se esta linha está EXATAMENTE assim
$string['bearertoken_sandbox_desc'] = 'Your PagBank Sandbox Bearer Token.';
$string['sandboxmode'] = 'Sandbox Mode';
$string['sandboxmode_desc'] = 'Enable Sandbox mode for testing.';
$string['webhookurl'] = 'Webhook URL';
$string['webhookurldesc'] = 'Register this URL in your PagBank panel for receiving payment notifications:';
$string['enrolmentstart'] = 'Starting PagBank enrolment'; // Adicionámos esta antes