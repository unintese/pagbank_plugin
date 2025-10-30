<?php
namespace enrol_pagbank;

defined('MOODLE_INTERNAL') || die();

// Inclui as classes Moodle necessárias
require_once($CFG->libdir . '/formslib.php');

class plugin extends \enrol_plugin {

    /**
     * Adiciona elementos ao formulário de configuração da instância no curso.
     * @param \MoodleQuickForm $mform
     * @param \stdClass $instance
     * @param \context $context
     * @return bool
     */
    public function add_instance_form_elements(\MoodleQuickForm $mform, \stdClass $instance = null, $context) {
        global $CFG, $OUTPUT;

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_pagbank'), array('size'=> '4'));
        $mform->setType('cost', PARAM_RAW);
        $mform->addRule('cost', get_string('costerror', 'enrol_pagbank'), 'numeric', null, 'client');
        $mform->setDefault('cost', $this->get_config('cost')); // Pega o padrão global se existir
        $mform->addHelpButton('cost', 'cost', 'enrol_pagbank');

        // Moeda (Simplificado)
        $currencies = array('BRL' => 'BRL');
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_pagbank'), $currencies);
        $mform->setDefault('currency', $this->get_config('currency')); // Pega o padrão global se existir
        $mform->addHelpButton('currency', 'currency', 'enrol_pagbank');

        // Campo oculto para garantir que o status seja salvo
        $mform->addElement('hidden', 'status', $this->get_config('status'));
        $mform->setType('status', PARAM_INT);


        // Chama o método pai para adicionar elementos comuns (nome, etc.)
        parent::add_instance_form_elements($mform, $instance, $context);
    }

    /**
     * Retorna o HTML para o botão de inscrição que o aluno vê.
     *
     * @param stdClass $instance A instância de inscrição do curso.
     * @return string HTML code.
     */
    public function get_enrolment_button(\stdClass $instance) {
        global $CFG, $OUTPUT, $DB;

        // Verifica se o curso e a instância são válidos
        if (empty($instance->id) || empty($instance->courseid)) {
            return '';
        }
        $course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        // Verifica se o usuário pode se inscrever
        if (!$this->can_self_enrol($instance)) {
            return '';
        }

        // Verifica o custo
        $cost = (float)$instance->cost;
        if (abs($cost) < 0.01) { // Gratuito ou custo inválido, não mostrar botão PagBank
             return '';
        }
        $currency = $instance->currency;

        // Monta o URL para onde o botão vai apontar (nosso futuro script de início de pagamento)
        $enrolurl = new \moodle_url('/enrol/pagbank/enrol.php', array('id' => $course->id, 'instance' => $instance->id));

        // Cria o botão
        $button = new \single_button($enrolurl, get_string('enrolme', 'core_enrol'));

        // Adiciona informações sobre o custo
        $output = \html_writer::tag('div', get_string('cost') . ": " . $currency . ' ' . format_float($cost, 2), array('class' => 'cost'));
        $output .= $OUTPUT->render($button);

        return $output;
    }


    // --- Outros métodos da classe (mantém os que já estavam) ---

   /**
     * Returns optional enrolment information icons for multiple instances.
     *
     * @param array $instances Array of enrolment instances.
     * @return array Array of html_writer_tag objects, keyed by instance id.
     */
    public function get_info_icons(array $instances): array {
        $icons = [];
        // Se precisar adicionar ícones específicos para cada instância,
        // você pode iterar sobre o array $instances aqui.
        // Exemplo:
        // foreach ($instances as $instance) {
        //     $icons[$instance->id] = new \html_writer_tag('img', '', ['src' => ..., 'alt' => ...]);
        // }

        // Por enquanto, apenas retornamos um array vazio, o que é válido.
        return $icons;
    }

    /**
     * Returns edit instance icon.
     * @param stdClass $instance
     * @return \pix_icon
     */
    public function get_instance_link(\stdClass $instance) {
        global $OUTPUT;
        $icon = new \pix_icon('i/settings', get_string('settings'));
        return $OUTPUT->render($icon);
    }

    /**
     * Can user enrol into the course instance?
     * @param stdClass $instance course enrolment instance
     * @param bool $checkuserenrolment check if user is already enrolled
     * @return bool
     */
    public function can_self_enrol(\stdClass $instance, $checkuserenrolment = true) {
         return parent::can_self_enrol($instance, $checkuserenrolment);
    }

    /**
     * Returns link to enrolment info page, used in course enrolment methods page.
     * @return moodle_url or null if not link needed
     */
    public static function get_info_page_url() {
        return null;
    }

    /**
     * Pode cancelar a própria inscrição?
     * @param stdClass $instance
     * @return bool
     */
    public function can_self_unenrol(\stdClass $instance) {
        // Assume que haverá uma config global 'unenrolself'
        // Se não houver, pode retornar false por enquanto.
        return $this->get_config('unenrolself');
    }
}