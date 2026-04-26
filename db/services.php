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
 * External function (web service) definitions for local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_consentmanager_set_consent' => [
        'classname'   => \local_consentmanager\external\set_consent::class,
        'description' => 'Store or update user consent for one or more categories.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false, // Guests can also consent; capability is checked inline for logged-in users.
    ],
    'local_consentmanager_withdraw_consent' => [
        'classname'   => \local_consentmanager\external\withdraw_consent::class,
        'description' => 'Withdraw user consent for a specific category.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],
    'local_consentmanager_get_status' => [
        'classname'   => \local_consentmanager\external\get_consent_status::class,
        'description' => 'Return the current consent status for all categories.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
    ],
];
