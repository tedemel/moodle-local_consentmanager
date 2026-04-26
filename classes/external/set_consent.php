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
 * External function: set_consent.
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
 * Web service: store or update consent for one or more categories.
 */
class set_consent extends \core_external\external_api {
    /**
     * Define web service input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'catids'    => new external_multiple_structure(
                new external_value(PARAM_INT, 'Category ID'),
                'List of category IDs to grant consent to (empty = only essential)',
                VALUE_DEFAULT,
                []
            ),
            'acceptall' => new external_value(
                PARAM_BOOL,
                'If true, accept all categories at once',
                VALUE_DEFAULT,
                false
            ),
            'guesttoken' => new external_value(
                PARAM_ALPHANUM,
                'Pre-issued guest token from page render (anonymous users only)',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    /**
     * Store or update consent for the given categories.
     *
     * @return array
     */
    public static function execute(array $catids = [], bool $acceptall = false, string $guesttoken = ''): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'catids'     => $catids,
            'acceptall'  => $acceptall,
            'guesttoken' => $guesttoken,
        ]);

        // Skip validate_context() — calls require_login() which fails for
        // not-logged-in users; this service is loginrequired:false by design
        // (guests must be able to consent). Capability check is gated on
        // logged-in users only.
        if (isloggedin() && !isguestuser()) {
            require_capability('local/consentmanager:giveconsent', \context_system::instance());
        }

        $providedtoken = !empty($params['guesttoken']) ? $params['guesttoken'] : null;
        consent_manager::instance()->store_consent($params['catids'], $params['acceptall'], $providedtoken);

        return ['success' => true, 'message' => ''];
    }

    /**
     * Define web service return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'message' => new external_value(PARAM_TEXT, 'Error message, empty on success'),
        ]);
    }
}
