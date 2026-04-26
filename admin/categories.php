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
 * Admin: manage consent categories.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_consentmanager\form\category_form;
use local_consentmanager\local\category;
use local_consentmanager\local\consent_manager;

$context = context_system::instance();
require_login();
require_capability('local/consentmanager:manage', $context);

$action = optional_param('action', 'list', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/consentmanager/admin/categories.php');
$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managecategories', 'local_consentmanager'));
$PAGE->set_heading(get_string('managecategories', 'local_consentmanager'));

$manager = consent_manager::instance();

if ($action === 'delete' && $id) {
    require_sesskey();
    $manager->delete_category($id);
    redirect($baseurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'edit') {
    $existing = null;
    if ($id) {
        $existing = $manager->get_category($id);
    }

    $formdata = null;
    if ($existing) {
        $formdata = $existing->to_record();
        $formdata->description_editor = ['text' => $existing->description, 'format' => $existing->descriptionformat];
    }

    $form = new category_form($baseurl->out(false) . '?action=edit&id=' . $id);
    if ($formdata) {
        $form->set_data($formdata);
    }

    if ($form->is_cancelled()) {
        redirect($baseurl);
    } else if ($data = $form->get_data()) {
        $cat = new category((object)[
            'id'                => $data->id ?? 0,
            'shortname'         => $data->shortname,
            'name'              => $data->name,
            'description'       => $data->description_editor['text'],
            'descriptionformat' => $data->description_editor['format'],
            'required'          => $data->required ?? 0,
            'sortorder'         => $data->sortorder ?? 0,
            'timecreated'       => 0,
            'timemodified'      => 0,
        ]);
        $manager->save_category($cat);
        redirect($baseurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    echo $OUTPUT->header();
    $heading = $id
        ? get_string('form_cat_name', 'local_consentmanager')
        : get_string('managecategories', 'local_consentmanager');
    echo $OUTPUT->heading($heading);
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecategories', 'local_consentmanager'));
echo $OUTPUT->box(get_string('admin_categories_intro', 'local_consentmanager'), 'generalbox mb-3');

$addurl = new moodle_url($baseurl, ['action' => 'edit']);
echo html_writer::link($addurl, get_string('add'), ['class' => 'btn btn-primary mb-3']);

$categories = $manager->get_categories();
if ($categories) {
    $table = new html_table();
    $table->head = [
        get_string('form_cat_shortname', 'local_consentmanager'),
        get_string('form_cat_name', 'local_consentmanager'),
        get_string('form_cat_required', 'local_consentmanager'),
        get_string('form_cat_sortorder', 'local_consentmanager'),
        get_string('edit'),
        get_string('delete'),
    ];
    $table->attributes['class'] = 'table table-bordered table-sm';
    foreach ($categories as $cat) {
        $editurl   = new moodle_url($baseurl, ['action' => 'edit', 'id' => $cat->id]);
        $deleteurl = new moodle_url($baseurl, ['action' => 'delete', 'id' => $cat->id, 'sesskey' => sesskey()]);
        $table->data[] = [
            s($cat->shortname),
            s($cat->name),
            $cat->required ? get_string('yes') : get_string('no'),
            $cat->sortorder,
            html_writer::link($editurl, get_string('edit')),
            $cat->required ? '' : html_writer::link($deleteurl, get_string('delete'), [
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
