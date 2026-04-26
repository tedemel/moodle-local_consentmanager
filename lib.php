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
 * Library functions for local_consentmanager.
 * Kept minimal — logic lives in classes/, hooks, and external functions.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add a link to "My consents" in the user profile navigation.
 *
 * @param \navigation_node $profilenode The profile navigation node.
 * @param \stdClass        $user        The user object.
 * @param \context         $context     The context.
 * @param \stdClass        $course      The course.
 * @param \cm_info|null    $cm          The course module.
 */
function local_consentmanager_extend_navigation_user_settings(
    \navigation_node $profilenode,
    \stdClass $user,
    \context $context,
    \stdClass $course,
    $cm
): void {
    global $USER;
    if ($USER->id !== $user->id) {
        return;
    }
    if (!get_config('local_consentmanager', 'enabled')) {
        return;
    }
    $url = new \moodle_url('/local/consentmanager/mypreferences.php');
    $profilenode->add(
        get_string('mypreferences', 'local_consentmanager'),
        $url,
        \navigation_node::TYPE_SETTING,
        null,
        'consentmanager_myprefs',
        new \pix_icon('i/settings', '')
    );
}
