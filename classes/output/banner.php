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
 * Renderable: consent banner.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\output;

use local_consentmanager\local\consent_manager;
use renderable;
use renderer_base;
use templatable;

/**
 * Renderable for the consent banner modal.
 */
class banner implements renderable, templatable {
    /** @var array Category data with consent status */
    private array $categories;

    /**
     * Build the banner view-model from current consent state.
     */
    public function __construct() {
        $manager        = consent_manager::instance();
        $cats           = $manager->get_categories();
        $consents       = $manager->get_user_consents();
        $syscontext     = \context_system::instance();

        $this->categories = [];
        foreach ($cats as $cat) {
            $this->categories[] = [
                'id'          => $cat->id,
                'name'        => format_string($cat->name),
                'description' => format_text($cat->description, $cat->descriptionformat, ['context' => $syscontext]),
                'required'    => (bool)$cat->required,
                'given'       => $cat->required || ($consents[$cat->id] ?? false),
            ];
        }
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'categories'        => $this->categories,
            'banner_intro'      => format_text(
                get_config('local_consentmanager', 'banner_intro'),
                FORMAT_HTML,
                ['context' => \context_system::instance()]
            ),
            'sitename'          => get_config('local_consentmanager', 'sitename') ?: get_site()->fullname,
            'privacypolicy_url' => get_config('local_consentmanager', 'privacypolicy_url'),
            'imprint_url'       => get_config('local_consentmanager', 'imprint_url'),
            'mypreferences_url' => (new \moodle_url('/local/consentmanager/mypreferences.php'))->out(false),
            'sesskey'           => sesskey(),
        ];
    }
}
