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
 * Admin settings for local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Main settings page under Privacy and Policies.
    $settings = new admin_settingpage(
        'local_consentmanager',
        get_string('pluginname', 'local_consentmanager')
    );

    // Add category under "Privacy and policies".
    $ADMIN->add('privacy', new admin_category(
        'local_consentmanager_cat',
        get_string('pluginname', 'local_consentmanager')
    ));
    $ADMIN->add('local_consentmanager_cat', $settings);

    // Admin dashboard link.
    $ADMIN->add('local_consentmanager_cat', new admin_externalpage(
        'local_consentmanager_dashboard',
        get_string('admindashboard', 'local_consentmanager'),
        new moodle_url('/local/consentmanager/admin/dashboard.php'),
        'local/consentmanager:manage'
    ));

    // Categories management.
    $ADMIN->add('local_consentmanager_cat', new admin_externalpage(
        'local_consentmanager_categories',
        get_string('managecategories', 'local_consentmanager'),
        new moodle_url('/local/consentmanager/admin/categories.php'),
        'local/consentmanager:manage'
    ));

    // Services management.
    $ADMIN->add('local_consentmanager_cat', new admin_externalpage(
        'local_consentmanager_services',
        get_string('manageservices', 'local_consentmanager'),
        new moodle_url('/local/consentmanager/admin/services.php'),
        'local/consentmanager:manage'
    ));

    // General settings.
    $settings->add(new admin_setting_heading(
        'local_consentmanager/generalheading',
        get_string('settings_general', 'local_consentmanager'),
        get_string('settings_general_desc', 'local_consentmanager')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_consentmanager/enabled',
        get_string('settings_enabled', 'local_consentmanager'),
        get_string('settings_enabled_desc', 'local_consentmanager'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_consentmanager/sitename',
        get_string('settings_sitename', 'local_consentmanager'),
        get_string('settings_sitename_desc', 'local_consentmanager'),
        $SITE->fullname ?? ''
    ));

    // Revision management.
    $settings->add(new admin_setting_heading(
        'local_consentmanager/revisionheading',
        get_string('settings_revision', 'local_consentmanager'),
        get_string('settings_revision_desc', 'local_consentmanager')
    ));

    $settings->add(new admin_setting_configtext(
        'local_consentmanager/revision',
        get_string('settings_revision_current', 'local_consentmanager'),
        get_string('settings_revision_current_desc', 'local_consentmanager'),
        '1',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_consentmanager/revision_minor',
        get_string('settings_revision_minor', 'local_consentmanager'),
        get_string('settings_revision_minor_desc', 'local_consentmanager'),
        0
    ));

    // Privacy / logging settings.
    $settings->add(new admin_setting_heading(
        'local_consentmanager/privacyheading',
        get_string('settings_privacy', 'local_consentmanager'),
        get_string('settings_privacy_desc', 'local_consentmanager')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_consentmanager/log_ipaddress',
        get_string('settings_log_ipaddress', 'local_consentmanager'),
        get_string('settings_log_ipaddress_desc', 'local_consentmanager'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_consentmanager/anonymize_ip',
        get_string('settings_anonymize_ip', 'local_consentmanager'),
        get_string('settings_anonymize_ip_desc', 'local_consentmanager'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_consentmanager/log_useragent',
        get_string('settings_log_useragent', 'local_consentmanager'),
        get_string('settings_log_useragent_desc', 'local_consentmanager'),
        0
    ));

    $settings->add(new admin_setting_configduration(
        'local_consentmanager/logretention',
        get_string('settings_logretention', 'local_consentmanager'),
        get_string('settings_logretention_desc', 'local_consentmanager'),
        3 * YEARSECS
    ));

    // Banner appearance.
    $settings->add(new admin_setting_heading(
        'local_consentmanager/bannerheading',
        get_string('settings_banner', 'local_consentmanager'),
        get_string('settings_banner_desc', 'local_consentmanager')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_consentmanager/banner_intro',
        get_string('settings_banner_intro', 'local_consentmanager'),
        get_string('settings_banner_intro_desc', 'local_consentmanager'),
        ''
    ));

    $settings->add(new admin_setting_description(
        'local_consentmanager/preview_btn_placeholder',
        '',
        html_writer::tag(
            'button',
            get_string('preview_btn', 'local_consentmanager'),
            ['type' => 'button', 'class' => 'btn btn-outline-primary', 'id' => 'local-consentmanager-preview-btn']
        )
    ));

    // Only add the AMD call when actually viewing this settings page, not on
    // every admin page (settings.php is loaded for admin-tree construction too).
    if (str_contains($PAGE->pagetype, 'consentmanager')) {
        $PAGE->requires->js_call_amd('local_consentmanager/banner', 'initPreviewButton', [[
            'closelabel' => get_string('preview_close', 'local_consentmanager'),
        ]]);
    }

    $settings->add(new admin_setting_configtext(
        'local_consentmanager/privacypolicy_url',
        get_string('settings_privacypolicy_url', 'local_consentmanager'),
        get_string('settings_privacypolicy_url_desc', 'local_consentmanager'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_consentmanager/imprint_url',
        get_string('settings_imprint_url', 'local_consentmanager'),
        get_string('settings_imprint_url_desc', 'local_consentmanager'),
        '',
        PARAM_URL
    ));
}
