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
 * Admin: manage third-party services.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_consentmanager\form\service_form;
use local_consentmanager\local\service;
use local_consentmanager\local\consent_manager;

$context = context_system::instance();
require_login();
require_capability('local/consentmanager:manage', $context);

$action = optional_param('action', 'list', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/consentmanager/admin/services.php');
$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('manageservices', 'local_consentmanager'));
$PAGE->set_heading(get_string('manageservices', 'local_consentmanager'));

$manager = consent_manager::instance();

if ($action === 'delete' && $id) {
    require_sesskey();
    $manager->delete_service($id);
    redirect($baseurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'toggle' && $id) {
    require_sesskey();
    global $DB;
    $rec = $DB->get_record('local_consentmanager_services', ['id' => $id], '*', MUST_EXIST);
    $rec->enabled       = $rec->enabled ? 0 : 1;
    $rec->timemodified  = time();
    $DB->update_record('local_consentmanager_services', $rec);
    \cache::make('local_consentmanager', 'serviceregistry')->delete('all');
    redirect($baseurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'edit') {
    global $DB;
    $existing = null;
    if ($id) {
        $rec = $DB->get_record('local_consentmanager_services', ['id' => $id], '*', MUST_EXIST);
        $existing = new service($rec);
    }

    $formdata = null;
    if ($existing) {
        $formdata = $existing->to_record();
        $formdata->domainpatterns     = implode("\n", $existing->domainpatterns);
        $formdata->description_editor = ['text' => $existing->description, 'format' => $existing->descriptionformat];
    }

    $form = new service_form($baseurl->out(false) . '?action=edit&id=' . $id);
    if ($formdata) {
        $form->set_data($formdata);
    }

    if ($form->is_cancelled()) {
        redirect($baseurl);
    } else if ($data = $form->get_data()) {
        $svc = new service((object)[
            'id'                => $data->id ?? 0,
            'catid'             => $data->catid,
            'name'              => $data->name,
            'provider'          => $data->provider ?? '',
            'privacyurl'        => $data->privacyurl ?? '',
            'description'       => $data->description_editor['text'],
            'descriptionformat' => $data->description_editor['format'],
            'domainpatterns'    => $data->domainpatterns ?? '',
            'enabled'           => $data->enabled ?? 1,
            'timecreated'       => 0,
            'timemodified'      => 0,
        ]);
        $manager->save_service($svc);
        redirect($baseurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading($id ? get_string('form_svc_name', 'local_consentmanager') : get_string('manageservices', 'local_consentmanager'));
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageservices', 'local_consentmanager'));
echo $OUTPUT->box(get_string('admin_services_intro', 'local_consentmanager'), 'generalbox mb-3');

$addurl = new moodle_url($baseurl, ['action' => 'edit']);
echo html_writer::link($addurl, get_string('add'), ['class' => 'btn btn-primary mb-3']);

$categories = $manager->get_categories();
$catnames   = [];
foreach ($categories as $c) {
    $catnames[$c->id] = format_string($c->name);
}

$services = $manager->get_all_services();
if ($services) {
    $table = new html_table();
    $table->head = [
        get_string('form_svc_name', 'local_consentmanager'),
        get_string('form_svc_category', 'local_consentmanager'),
        get_string('form_svc_provider', 'local_consentmanager'),
        get_string('form_svc_enabled', 'local_consentmanager'),
        get_string('edit'),
        get_string('delete'),
    ];
    $table->attributes['class'] = 'table table-bordered table-sm align-middle';
    foreach ($services as $svc) {
        $editurl   = new moodle_url($baseurl, ['action' => 'edit', 'id' => $svc->id]);
        $deleteurl = new moodle_url($baseurl, ['action' => 'delete', 'id' => $svc->id, 'sesskey' => sesskey()]);
        $toggleurl = new moodle_url($baseurl, ['action' => 'toggle', 'id' => $svc->id, 'sesskey' => sesskey()]);
        $togglehtml = html_writer::tag('form',
            html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action',  'value' => 'toggle']) .
            html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id',      'value' => $svc->id]) .
            html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]) .
            html_writer::tag('div',
                html_writer::empty_tag('input', array_merge(
                    ['class' => 'form-check-input', 'type' => 'checkbox', 'role' => 'switch',
                     'onchange' => 'this.form.submit()', 'style' => 'cursor:pointer; width:2.5em; height:1.25em'],
                    $svc->enabled ? ['checked' => 'checked'] : []
                )),
                ['class' => 'form-check form-switch mb-0']
            ),
            ['method' => 'post', 'action' => $baseurl->out(false), 'class' => 'd-inline m-0']
        );
        $table->data[] = [
            s($svc->name),
            $catnames[$svc->catid] ?? '?',
            s($svc->provider),
            $togglehtml,
            html_writer::link($editurl, get_string('edit')),
            html_writer::link($deleteurl, get_string('delete'), [
                'onclick' => 'return confirm(' . json_encode(get_string('areyousure')) . ')',
                'class'   => 'text-danger',
            ]),
        ];
    }
    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('history_empty', 'local_consentmanager'), ['class' => 'text-muted']);
}

echo $OUTPUT->footer();
