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
 * Privacy API provider for local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider — declares and handles personal data stored in consent tables.
 *
 * Design note on audit log deletion:
 * When a user exercises their "right to erasure" (Art. 17 DSGVO), we delete the
 * current consent records (local_consentmanager_consents) but only ANONYMISE the
 * audit log (local_consentmanager_log) by nulling the userid.  This is necessary
 * to fulfil the competing obligation of Art. 7 DSGVO (accountability/provability
 * of consent) and is documented in the privacy metadata strings.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_consentmanager_consents',
            [
                'userid'       => 'privacy:metadata:consents:userid',
                'guesttoken'   => 'privacy:metadata:consents:guesttoken',
                'catid'        => 'privacy:metadata:consents:catid',
                'status'       => 'privacy:metadata:consents:status',
                'revision'     => 'privacy:metadata:consents:revision',
                'timecreated'  => 'privacy:metadata:consents:timecreated',
                'timemodified' => 'privacy:metadata:consents:timemodified',
            ],
            'privacy:metadata:consents'
        );

        $collection->add_database_table(
            'local_consentmanager_log',
            [
                'userid'      => 'privacy:metadata:log:userid',
                'guesttoken'  => 'privacy:metadata:log:guesttoken',
                'action'      => 'privacy:metadata:log:action',
                'catid'       => 'privacy:metadata:log:catid',
                'revision'    => 'privacy:metadata:log:revision',
                'ipaddress'   => 'privacy:metadata:log:ipaddress',
                'useragent'   => 'privacy:metadata:log:useragent',
                'timecreated' => 'privacy:metadata:log:timecreated',
            ],
            'privacy:metadata:log'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :systemlevel
                   AND (
                       EXISTS (SELECT 1 FROM {local_consentmanager_consents} c WHERE c.userid = :userid1)
                    OR EXISTS (SELECT 1 FROM {local_consentmanager_log} l WHERE l.userid = :userid2)
                   )";
        $contextlist->add_from_sql($sql, [
            'systemlevel' => CONTEXT_SYSTEM,
            'userid1'     => $userid,
            'userid2'     => $userid,
        ]);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $userlist->add_from_table('local_consentmanager_consents', 'userid');
        $userlist->add_from_table('local_consentmanager_log', 'userid');
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_SYSTEM) {
                continue;
            }

            // Export current consents.
            $consents = $DB->get_records('local_consentmanager_consents', ['userid' => $userid]);
            if ($consents) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:path:consents', 'local_consentmanager')],
                    (object)['consents' => array_values($consents)]
                );
            }

            // Export audit log.
            $logs = $DB->get_records('local_consentmanager_log', ['userid' => $userid]);
            if ($logs) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:path:log', 'local_consentmanager')],
                    (object)['log' => array_values($logs)]
                );
            }
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        // System context: we anonymise rather than delete the log (accountability).
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        global $DB;
        $DB->delete_records('local_consentmanager_consents', []);
        $DB->set_field('local_consentmanager_log', 'userid', null, []);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_SYSTEM) {
                continue;
            }
            // Delete current consent entries.
            $DB->delete_records('local_consentmanager_consents', ['userid' => $userid]);
            // Anonymise audit log (keep the log row, null the userid).
            $DB->set_field('local_consentmanager_log', 'userid', null, ['userid' => $userid]);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }
        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_consentmanager_consents', "userid $insql", $inparams);
        $DB->set_field_select('local_consentmanager_log', 'userid', null, "userid $insql", $inparams);
    }
}
