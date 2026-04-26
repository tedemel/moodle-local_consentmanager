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
 * External function: withdraw_consent.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_consentmanager\local\consent_manager;

/**
 * Web service: withdraw consent for a single category.
 */
class withdraw_consent extends \core_external\external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'catid' => new external_value(PARAM_INT, 'Category ID to withdraw consent from'),
        ]);
    }

    public static function execute(int $catid): array {
        $params = self::validate_parameters(self::execute_parameters(), ['catid' => $catid]);

        // Skip validate_context() — see set_consent.php for rationale.
        if (isloggedin() && !isguestuser()) {
            require_capability('local/consentmanager:giveconsent', \context_system::instance());
        }

        try {
            consent_manager::instance()->withdraw_consent($params['catid']);
            return ['success' => true, 'message' => ''];
        } catch (\moodle_exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'message' => new external_value(PARAM_TEXT, 'Error message, empty on success'),
        ]);
    }
}
