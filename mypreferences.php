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
 * "My Consents" user preferences page.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/consentmanager/mypreferences.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('mypreferences_title', 'local_consentmanager'));
$PAGE->set_heading(get_string('mypreferences_title', 'local_consentmanager'));

// Load AMD module for withdraw/give actions.
$PAGE->requires->js_call_amd('local_consentmanager/preferences', 'init');
$PAGE->requires->js_call_amd('local_consentmanager/iframe_unlock', 'init');

echo $OUTPUT->header();

$renderable = new \local_consentmanager\output\user_preferences();
echo $OUTPUT->render_from_template('local_consentmanager/preferences', $renderable->export_for_template($OUTPUT));

echo $OUTPUT->footer();
