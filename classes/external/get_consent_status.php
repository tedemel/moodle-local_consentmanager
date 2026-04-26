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
 * External function: get_consent_status.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use local_consentmanager\local\consent_manager;

/**
 * Web service: return the current consent status for all categories.
 */
class get_consent_status extends \core_external\external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    public static function execute(): array {
        $manager    = consent_manager::instance();
        $categories = $manager->get_categories();
        $revision   = (int)get_config('local_consentmanager', 'revision');

        if (!isloggedin() || isguestuser()) {
            $catdata = [];
            foreach ($categories as $cat) {
                $catdata[] = ['id' => $cat->id, 'name' => $cat->name, 'required' => (int)$cat->required, 'given' => 0];
            }
            return ['revision' => $revision, 'needs_consent' => 1, 'categories' => $catdata];
        }

        $context = \context_system::instance();
        self::validate_context($context);

        $consents   = $manager->get_user_consents();
        $needs      = $manager->needs_consent();

        $catdata = [];
        foreach ($categories as $cat) {
            $catdata[] = [
                'id'       => $cat->id,
                'name'     => $cat->name,
                'required' => (int)$cat->required,
                'given'    => (int)($consents[$cat->id] ?? false),
            ];
        }

        return [
            'revision'       => $revision,
            'needs_consent'  => (int)$needs,
            'categories'     => $catdata,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'revision'      => new external_value(PARAM_INT, 'Current consent revision'),
            'needs_consent' => new external_value(PARAM_INT, '1 if the banner should be shown'),
            'categories'    => new external_multiple_structure(
                new external_single_structure([
                    'id'       => new external_value(PARAM_INT, 'Category ID'),
                    'name'     => new external_value(PARAM_TEXT, 'Category display name'),
                    'required' => new external_value(PARAM_INT, '1 if essential/required'),
                    'given'    => new external_value(PARAM_INT, '1 if consent is given'),
                ])
            ),
        ]);
    }
}
