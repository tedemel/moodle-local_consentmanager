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
 * Renderable: user consent preferences page.
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
 * Renderable for the "My Consents" user preferences page.
 */
class user_preferences implements renderable, templatable {

    /** @var array */
    private array $categories;
    /** @var array */
    private array $history;

    public function __construct() {
        global $DB, $USER;

        $manager    = consent_manager::instance();
        $cats       = $manager->get_categories();
        $consents   = $manager->get_user_consents();
        $syscontext = \context_system::instance();

        $this->categories = [];
        foreach ($cats as $cat) {
            $this->categories[] = [
                'id'          => $cat->id,
                'name'        => format_string($cat->name),
                'description' => format_text($cat->description, $cat->descriptionformat, ['context' => $syscontext]),
                'required'    => (bool)$cat->required,
                'given'       => $cat->required || ($consents[$cat->id] ?? false),
                'can_withdraw' => !$cat->required && ($consents[$cat->id] ?? false),
            ];
        }

        // Load recent consent history (last 50 entries).
        $catnames = [];
        foreach ($cats as $c) {
            $catnames[$c->id] = format_string($c->name);
        }

        $logs = $DB->get_records_select(
            'local_consentmanager_log',
            'userid = :userid',
            ['userid' => $USER->id],
            'timecreated DESC',
            '*',
            0,
            50
        );

        $this->history = [];
        foreach ($logs as $log) {
            $this->history[] = [
                'action'    => get_string('log_action_' . $log->action, 'local_consentmanager'),
                'catname'   => $catnames[$log->catid] ?? '?',
                'revision'  => $log->revision,
                'timecreated' => userdate($log->timecreated),
            ];
        }
    }

    public function export_for_template(renderer_base $output): array {
        return [
            'categories'        => $this->categories,
            'history'           => $this->history,
            'has_history'       => !empty($this->history),
            'privacypolicy_url' => get_config('local_consentmanager', 'privacypolicy_url'),
            'sesskey'           => sesskey(),
        ];
    }
}
