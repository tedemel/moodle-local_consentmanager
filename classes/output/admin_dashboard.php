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
 * Renderable: admin consent dashboard.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\output;

use local_consentmanager\local\consent_manager;
use renderable;
use renderer_base;
use templatable;

/**
 * Renderable for the admin consent overview dashboard.
 */
class admin_dashboard implements renderable, templatable {

    /** @var array */
    private array $stats;

    public function __construct() {
        global $DB;

        $manager  = consent_manager::instance();
        $cats     = $manager->get_categories();
        $revision = (int)get_config('local_consentmanager', 'revision');

        $this->stats = [];
        foreach ($cats as $cat) {
            // Total users with a consent record for this category.
            $total = $DB->count_records('local_consentmanager_consents', ['catid' => $cat->id]);

            // Users who gave consent.
            $given = $DB->count_records('local_consentmanager_consents', [
                'catid'  => $cat->id,
                'status' => 1,
            ]);

            // Users who withdrew.
            $withdrawn = $DB->count_records('local_consentmanager_consents', [
                'catid'  => $cat->id,
                'status' => 2,
            ]);

            $rate = $total > 0 ? round($given / $total * 100) : 0;

            $this->stats[] = [
                'catname'   => format_string($cat->name),
                'required'  => $cat->required,
                'total'     => $total,
                'given'     => $given,
                'withdrawn' => $withdrawn,
                'rate'      => $rate,
            ];
        }
    }

    public function export_for_template(renderer_base $output): array {
        global $DB;

        $revision        = (int)get_config('local_consentmanager', 'revision');
        $revision_minor  = (bool)get_config('local_consentmanager', 'revision_minor');

        // Recent audit log (last 100).
        $recentlogs = $DB->get_records_select(
            'local_consentmanager_log',
            '1=1',
            [],
            'timecreated DESC',
            '*',
            0,
            100
        );

        $logs = [];
        $cats = consent_manager::instance()->get_categories();
        $catnames = [];
        foreach ($cats as $c) {
            $catnames[$c->id] = format_string($c->name);
        }
        foreach ($recentlogs as $log) {
            $logs[] = [
                'userid'      => $log->userid ?? get_string('guest'),
                'action'      => get_string('log_action_' . $log->action, 'local_consentmanager'),
                'catname'     => $catnames[$log->catid] ?? '?',
                'revision'    => $log->revision,
                'timecreated' => userdate($log->timecreated),
            ];
        }

        return [
            'stats'          => $this->stats,
            'revision'       => $revision,
            'revision_minor' => $revision_minor,
            'recentlogs'     => $logs,
            'export_url'     => (new \moodle_url('/local/consentmanager/admin/export.php'))->out(false),
            'sesskey'        => sesskey(),
        ];
    }
}
