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
 * Admin: export consent audit log as CSV (HMAC-signed).
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$context = context_system::instance();
require_login();
require_capability('local/consentmanager:exportlogs', $context);
require_sesskey();

global $DB;

$manager  = \local_consentmanager\local\consent_manager::instance();
$cats     = $manager->get_categories();
$catnames = [];
foreach ($cats as $c) {
    $catnames[$c->id] = $c->name;
}

// Stream CSV.
$filename = 'consent_audit_log_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$out = fopen('php://output', 'w');

// BOM for Excel UTF-8.
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row.
fputcsv($out, [
    'id', 'userid', 'guesttoken', 'action', 'category', 'revision',
    'ipaddress', 'timecreated', 'hmac',
]);

$secret = hash('sha256', $CFG->wwwroot . $CFG->dbname . 'consentmanager');

$logs = $DB->get_recordset('local_consentmanager_log', null, 'timecreated ASC');
foreach ($logs as $log) {
    $row = [
        $log->id,
        $log->userid ?? '',
        $log->guesttoken ?? '',
        $log->action,
        $catnames[$log->catid] ?? $log->catid,
        $log->revision,
        $log->ipaddress ?? '',
        date('Y-m-d H:i:s', $log->timecreated),
    ];
    // HMAC over the raw row for tamper evidence.
    $hmac = hash_hmac('sha256', implode('|', $row), $secret);
    $row[] = $hmac;
    fputcsv($out, $row);
}
$logs->close();
fclose($out);
exit;
