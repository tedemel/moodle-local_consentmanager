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
 * Event: consent given.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\event;

/**
 * Fired when a user gives consent to a category.
 */
class consent_given extends \core\event\base {

    protected function init(): void {
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_consentmanager_consents';
    }

    public static function get_name(): string {
        return get_string('event_consent_given', 'local_consentmanager');
    }

    public function get_description(): string {
        $catid    = $this->other['catid'] ?? 0;
        $revision = $this->other['revision'] ?? 0;
        return "User {$this->userid} gave consent to category {$catid} (revision {$revision}).";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/local/consentmanager/mypreferences.php');
    }

    public static function get_other_mapping(): array {
        return false; // No backup/restore mapping needed.
    }
}
