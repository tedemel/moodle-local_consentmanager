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
 * Scheduled task: clean up expired log entries.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\task;

/**
 * Deletes audit log rows older than the configured retention period.
 * Default retention: 3 years.
 */
class cleanup_expired_logs extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_cleanup_logs', 'local_consentmanager');
    }

    public function execute(): void {
        global $DB;

        $retentionseconds = (int)get_config('local_consentmanager', 'logretention');
        if (!$retentionseconds) {
            // Default: 3 years.
            $retentionseconds = 3 * YEARSECS;
        }

        $cutoff = time() - $retentionseconds;

        $deleted = $DB->count_records_select(
            'local_consentmanager_log',
            'timecreated < :cutoff',
            ['cutoff' => $cutoff]
        );

        $DB->delete_records_select(
            'local_consentmanager_log',
            'timecreated < :cutoff',
            ['cutoff' => $cutoff]
        );

        mtrace("local_consentmanager: deleted {$deleted} expired audit log entries (cutoff: " . userdate($cutoff) . ").");
    }
}
