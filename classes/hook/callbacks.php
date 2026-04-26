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
 * Hook callback implementations for local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\hook;

use local_consentmanager\local\consent_manager;

/**
 * Hooks into Moodle's page lifecycle to inject the consent banner.
 */
class callbacks {

    /**
     * Called before HTTP headers are sent.
     *
     * Used to issue the long-lived guest cookie for anonymous visitors. We must
     * do this here (not in before_footer_html_generation) because by footer time
     * headers are already sent and setcookie() becomes a no-op.
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function before_http_headers(\core\hook\output\before_http_headers $hook): void {
        global $PAGE;
        if (!get_config('local_consentmanager', 'enabled')) {
            return;
        }
        if (in_array($PAGE->pagelayout, ['embedded', 'maintenance', 'secure', 'popup'])) {
            return;
        }
        if (isloggedin() && !isguestuser()) {
            return;
        }
        // Issue / refresh the rolling 12-month guest cookie.
        consent_manager::instance()->get_or_create_guest_token();
    }

    /**
     * Called just before the footer HTML is generated.
     * Injects the banner markup into the page.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        global $OUTPUT, $PAGE;

        $enabled   = (bool)get_config('local_consentmanager', 'enabled');
        $ismanager = has_capability('local/consentmanager:manage', \context_system::instance());

        // Always inject banner HTML for managers (needed for admin preview).
        // For regular users only inject when plugin is enabled.
        if (!$enabled && !$ismanager) {
            return;
        }

        if (in_array($PAGE->pagelayout, ['embedded', 'maintenance', 'secure', 'popup'])) {
            return;
        }

        $manager       = consent_manager::instance();
        $categories    = $manager->get_categories();
        $consents      = $manager->get_user_consents();
        $needsconsent  = $manager->needs_consent();

        // For anonymous users, pre-issue a guest token so the JS can pass it back to
        // service-nologin.php (which runs under NO_MOODLE_COOKIES — $SESSION is not
        // available there, so we cannot create the token inside the AJAX call).
        $guesttoken = (isloggedin() && !isguestuser()) ? '' : $manager->get_or_create_guest_token();

        $catdata = [];
        foreach ($categories as $cat) {
            $catdata[] = [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'description' => format_text($cat->description, $cat->descriptionformat, ['context' => \context_system::instance()]),
                'required'    => $cat->required,
                'given'       => $cat->required || ($consents[$cat->id] ?? false),
            ];
        }

        $data = [
            'categories'       => array_values($catdata),
            'privacypolicy_url' => get_config('local_consentmanager', 'privacypolicy_url'),
            'imprint_url'      => get_config('local_consentmanager', 'imprint_url'),
            'banner_intro'     => format_text(
                get_config('local_consentmanager', 'banner_intro'),
                FORMAT_HTML,
                ['context' => \context_system::instance()]
            ),
            'sitename'         => get_config('local_consentmanager', 'sitename') ?: get_site()->fullname,
            'mypreferences_url' => (new \moodle_url('/local/consentmanager/mypreferences.php'))->out(false),
            'isloggedin'       => isloggedin() && !isguestuser(),
        ];

        $html = $OUTPUT->render_from_template('local_consentmanager/banner', $data);
        $hook->add_html($html);

        $PAGE->requires->js_call_amd('local_consentmanager/banner', 'init', [[
            'wwwroot'      => (new \moodle_url('/'))->out(false),
            'sesskey'      => sesskey(),
            'revision'     => (int)get_config('local_consentmanager', 'revision'),
            'needsconsent' => (bool)$needsconsent,
            'isloggedin'   => isloggedin() && !isguestuser(),
            'guesttoken'   => $guesttoken,
        ]]);
    }
}
