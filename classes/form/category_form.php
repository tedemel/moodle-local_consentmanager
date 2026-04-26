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
 * Moodle form: create / edit a consent category.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\form;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing consent categories.
 */
class category_form extends \moodleform {

    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Shortname.
        $mform->addElement('text', 'shortname', get_string('form_cat_shortname', 'local_consentmanager'), ['maxlength' => 100]);
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addRule('shortname', null, 'required');
        $mform->addHelpButton('shortname', 'form_cat_shortname', 'local_consentmanager');

        // Display name.
        $mform->addElement('text', 'name', get_string('form_cat_name', 'local_consentmanager'), ['maxlength' => 255, 'size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        // Description.
        $mform->addElement('editor', 'description_editor', get_string('form_cat_description', 'local_consentmanager'));
        $mform->setType('description_editor', PARAM_RAW);

        // Required (essential).
        $mform->addElement('advcheckbox', 'required', get_string('form_cat_required', 'local_consentmanager'));
        $mform->addHelpButton('required', 'form_cat_required', 'local_consentmanager');

        // Sort order.
        $mform->addElement('text', 'sortorder', get_string('form_cat_sortorder', 'local_consentmanager'), ['size' => 5]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $this->add_action_buttons();
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($data['shortname'])) {
            $errors['shortname'] = get_string('required');
        }
        if (empty($data['name'])) {
            $errors['name'] = get_string('required');
        }

        return $errors;
    }
}
