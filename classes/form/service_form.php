<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Moodle form: create / edit a third-party service.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\form;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing service registry entries.
 */
class service_form extends \moodleform {

    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Service name.
        $mform->addElement('text', 'name', get_string('form_svc_name', 'local_consentmanager'), ['maxlength' => 255, 'size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        // Category.
        $manager = \local_consentmanager\local\consent_manager::instance();
        $catoptions = [];
        foreach ($manager->get_categories() as $cat) {
            $catoptions[$cat->id] = format_string($cat->name);
        }
        $mform->addElement('select', 'catid', get_string('form_svc_category', 'local_consentmanager'), $catoptions);
        $mform->addRule('catid', null, 'required');

        // Provider.
        $mform->addElement('text', 'provider', get_string('form_svc_provider', 'local_consentmanager'), ['maxlength' => 255, 'size' => 50]);
        $mform->setType('provider', PARAM_TEXT);

        // Privacy URL.
        $mform->addElement('text', 'privacyurl', get_string('form_svc_privacyurl', 'local_consentmanager'), ['maxlength' => 1024, 'size' => 60]);
        $mform->setType('privacyurl', PARAM_URL);

        // Description.
        $mform->addElement('editor', 'description_editor', get_string('form_svc_description', 'local_consentmanager'));
        $mform->setType('description_editor', PARAM_RAW);

        // Domain patterns.
        $mform->addElement(
            'textarea',
            'domainpatterns',
            get_string('form_svc_domainpatterns', 'local_consentmanager'),
            ['rows' => 5, 'cols' => 60]
        );
        $mform->setType('domainpatterns', PARAM_RAW);
        $mform->addHelpButton('domainpatterns', 'form_svc_domainpatterns', 'local_consentmanager');

        // Enabled.
        $mform->addElement('advcheckbox', 'enabled', get_string('form_svc_enabled', 'local_consentmanager'));
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons();
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($data['name'])) {
            $errors['name'] = get_string('required');
        }

        // Validate each domain regex.
        if (!empty($data['domainpatterns'])) {
            $patterns = array_filter(array_map('trim', explode("\n", $data['domainpatterns'])));
            foreach ($patterns as $pattern) {
                if (@preg_match('/' . $pattern . '/', '') === false) {
                    $errors['domainpatterns'] = get_string('error_invalid_regex', 'local_consentmanager', s($pattern));
                    break;
                }
            }
        }

        return $errors;
    }
}
