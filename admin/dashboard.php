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
 * Admin consent overview dashboard.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$context = context_system::instance();
require_login();
require_capability('local/consentmanager:viewreports', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/consentmanager/admin/dashboard.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('dashboard_title', 'local_consentmanager'));
$PAGE->set_heading(get_string('dashboard_title', 'local_consentmanager'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dashboard_title', 'local_consentmanager'));
echo $OUTPUT->box(get_string('admin_dashboard_intro', 'local_consentmanager'), 'generalbox mb-3');

// Admin navigation.
$navlinks = [
    [
        'url'   => new moodle_url('/local/consentmanager/admin/categories.php'),
        'label' => get_string('managecategories', 'local_consentmanager'),
    ],
    [
        'url'   => new moodle_url('/local/consentmanager/admin/services.php'),
        'label' => get_string('manageservices', 'local_consentmanager'),
    ],
    [
        'url'   => new moodle_url('/local/consentmanager/admin/export.php'),
        'label' => get_string('btn_export_csv', 'local_consentmanager'),
    ],
];
$navhtml = '<div class="mb-3">';
foreach ($navlinks as $link) {
    $navhtml .= html_writer::link($link['url'], $link['label'], ['class' => 'btn btn-sm btn-outline-secondary me-2']);
}
$navhtml .= html_writer::tag(
    'button',
    get_string('preview_btn', 'local_consentmanager'),
    ['type' => 'button', 'class' => 'btn btn-sm btn-outline-primary', 'id' => 'local-consentmanager-preview-btn']
);
$navhtml .= '</div>';
echo $navhtml;
$PAGE->requires->js_call_amd('local_consentmanager/banner', 'initPreviewButton', [[
    'closelabel' => get_string('preview_close', 'local_consentmanager'),
]]);

$renderable = new \local_consentmanager\output\admin_dashboard();
$data       = $renderable->export_for_template($OUTPUT);

// Render stats table.
echo $OUTPUT->heading(get_string('dashboard_stats', 'local_consentmanager'), 3);
$table = new html_table();
$table->head = [
    get_string('form_cat_name', 'local_consentmanager'),
    get_string('cat_required', 'local_consentmanager'),
    get_string('status_given', 'local_consentmanager'),
    get_string('btn_withdraw', 'local_consentmanager'),
    get_string('dashboard_col_optin_rate', 'local_consentmanager'),
];
$table->attributes['class'] = 'table table-bordered table-sm';
foreach ($data['stats'] as $stat) {
    $table->data[] = [
        $stat['catname'],
        $stat['required'] ? get_string('yes') : get_string('no'),
        $stat['given'],
        $stat['withdrawn'],
        $stat['rate'] . '%',
    ];
}
echo html_writer::table($table);

// Revision info.
echo html_writer::tag('p', get_string('dashboard_revision', 'local_consentmanager') . ': <strong>' . $data['revision'] . '</strong>',
    ['class' => 'alert alert-info']);

// Recent log.
echo $OUTPUT->heading(get_string('dashboard_recentlog', 'local_consentmanager'), 3);
if (!empty($data['recentlogs'])) {
    $logtable = new html_table();
    $logtable->head = [
        get_string('history_col_time', 'local_consentmanager'),
        get_string('history_col_action', 'local_consentmanager'),
        get_string('history_col_category', 'local_consentmanager'),
        get_string('dashboard_col_userid', 'local_consentmanager'),
        get_string('history_col_revision', 'local_consentmanager'),
    ];
    $logtable->attributes['class'] = 'table table-sm table-striped';
    foreach ($data['recentlogs'] as $log) {
        $logtable->data[] = [
            $log['timecreated'],
            $log['action'],
            $log['catname'],
            $log['userid'],
            $log['revision'],
        ];
    }
    echo html_writer::table($logtable);
} else {
    echo html_writer::tag('p', get_string('history_empty', 'local_consentmanager'), ['class' => 'text-muted']);
}

echo $OUTPUT->footer();
