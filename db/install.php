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
 * Post-install hook: creates default consent categories and services.
 *
 * Names and descriptions use the multilang2 filter syntax ({mlang xx}…{mlang})
 * so the same record renders in the user's language at runtime, regardless of
 * the site language at install time.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Wrap text in mlang2 tags for English and German.
 *
 * @param string $en English text.
 * @param string $de German text.
 * @return string
 */
function local_consentmanager_mlang(string $en, string $de): string {
    return '{mlang en}' . $en . '{mlang}{mlang de}' . $de . '{mlang}';
}

/**
 * Create default categories and services after installation.
 */
function xmldb_local_consentmanager_install(): void {
    global $DB;

    $now = time();

    // Default categories.
    $categories = [
        [
            'shortname'         => 'essential',
            'name'              => local_consentmanager_mlang('Essential', 'Essenziell'),
            'description'       => local_consentmanager_mlang(
                'Technically necessary for the operation of the platform. Cannot be declined by users.',
                'Technisch notwendig für den Betrieb der Plattform. Kann von Nutzern nicht abgelehnt werden.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'required'          => 1,
            'sortorder'         => 1,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'shortname'         => 'functional',
            'name'              => local_consentmanager_mlang('Functional', 'Funktional'),
            'description'       => local_consentmanager_mlang(
                'Enable embedded content and extended features, e.g. videos and interactive H5P elements.',
                'Ermöglichen eingebettete Inhalte und erweiterte Funktionen, z. B. Videos und interaktive H5P-Elemente.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'required'          => 0,
            'sortorder'         => 2,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'shortname'         => 'statistics',
            'name'              => local_consentmanager_mlang('Statistics', 'Statistik'),
            'description'       => local_consentmanager_mlang(
                'Help understand how users use the platform. Data is only processed in aggregate form.',
                'Helfen zu verstehen, wie Nutzer die Plattform verwenden. Daten werden nur aggregiert ausgewertet.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'required'          => 0,
            'sortorder'         => 3,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'shortname'         => 'marketing',
            'name'              => local_consentmanager_mlang('Marketing', 'Marketing'),
            'description'       => local_consentmanager_mlang(
                'Enable personalised content and embedding of platforms such as YouTube'
                . ' that include their own tracking mechanisms.',
                'Ermöglichen personalisierte Inhalte und das Einbetten von Plattformen wie YouTube,'
                . ' die eigene Tracking-Mechanismen mitbringen.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'required'          => 0,
            'sortorder'         => 4,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
    ];

    $catids = [];
    foreach ($categories as $cat) {
        $catids[$cat['shortname']] = $DB->insert_record('local_consentmanager_cats', (object)$cat);
    }

    // Default services. All disabled — admin enables only what is actually used.
    $services = [
        [
            'catid'             => $catids['marketing'],
            'name'              => 'YouTube',
            'provider'          => 'Google Ireland Ltd.',
            'privacyurl'        => 'https://policies.google.com/privacy',
            'description'       => local_consentmanager_mlang(
                'Video platform by Google Ireland Ltd. Used for embedded YouTube videos.'
                . ' Data may be transferred to the USA.',
                'Videoplattform von Google Ireland Ltd. Wird für eingebettete YouTube-Videos verwendet.'
                . ' Daten werden ggf. in die USA übertragen.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => "youtube\\.com\nyoutu\\.be\nyoutube-nocookie\\.com",
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'catid'             => $catids['functional'],
            'name'              => 'Vimeo',
            'provider'          => 'Vimeo, Inc.',
            'privacyurl'        => 'https://vimeo.com/privacy',
            'description'       => local_consentmanager_mlang(
                'Video platform by Vimeo, Inc. Used for embedded Vimeo videos.',
                'Videoplattform von Vimeo, Inc. Wird für eingebettete Vimeo-Videos verwendet.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => "vimeo\\.com\nplayer\\.vimeo\\.com",
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'catid'             => $catids['functional'],
            'name'              => 'H5P.com',
            'provider'          => 'Joubel AS',
            'privacyurl'        => 'https://h5p.com/privacy',
            'description'       => local_consentmanager_mlang(
                'Hosting platform for interactive H5P content. Used when H5P content is embedded from h5p.com.',
                'Hosting-Plattform für interaktive H5P-Inhalte. Wird verwendet, wenn H5P-Inhalte von h5p.com eingebettet werden.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => "h5p\\.com\napi\\.h5p\\.com",
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'catid'             => $catids['functional'],
            'name'              => 'Google Fonts',
            'provider'          => 'Google LLC',
            'privacyurl'        => 'https://policies.google.com/privacy',
            'description'       => local_consentmanager_mlang(
                'Font service by Google. Loads fonts from Google servers'
                . ' — IP addresses are transmitted to Google in the process.',
                'Schriftarten-Dienst von Google. Lädt Schriftarten von Google-Servern'
                . ' – dabei werden IP-Adressen an Google übertragen.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => "fonts\\.googleapis\\.com\nfonts\\.gstatic\\.com",
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'catid'             => $catids['functional'],
            'name'              => 'Google Maps',
            'provider'          => 'Google Ireland Ltd.',
            'privacyurl'        => 'https://policies.google.com/privacy',
            'description'       => local_consentmanager_mlang(
                'Map service by Google Ireland Ltd. Used for embedded Google Maps.',
                'Kartendienst von Google Ireland Ltd. Wird für eingebettete Google Maps-Karten verwendet.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => "maps\\.googleapis\\.com\nmaps\\.gstatic\\.com\ngoogle\\.com/maps",
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
        [
            'catid'             => $catids['statistics'],
            'name'              => 'Matomo',
            'provider'          => 'InnoCraft Ltd.',
            'privacyurl'        => 'https://matomo.org/privacy-policy/',
            'description'       => local_consentmanager_mlang(
                'Privacy-friendly web analytics tool. Please adjust the domain pattern to match your own Matomo instance URL.',
                'Datenschutzfreundliches Webanalyse-Tool. Domainmuster bitte auf die eigene Matomo-Instanz anpassen.'
            ),
            'descriptionformat' => FORMAT_HTML,
            'domainpatterns'    => '',
            'enabled'           => 0,
            'timecreated'       => $now,
            'timemodified'      => $now,
        ],
    ];

    foreach ($services as $svc) {
        $DB->insert_record('local_consentmanager_services', (object)$svc);
    }
}
