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
 * English language strings for local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admin_categories_intro'] = 'Categories group services by purpose. Each service belongs to exactly one category. Users consent or decline per category — not per individual service. The <strong>Essential</strong> category is always active and cannot be declined.';
$string['admin_dashboard_intro'] = 'Overview of all consent activity on this platform. The table shows opt-in rates per category and the most recent consent events.';
$string['admin_services_intro'] = 'Services are third-party providers whose content is embedded on this platform (e.g. YouTube, Vimeo). Each service has a domain pattern (regular expression). The filter checks every iframe against these patterns and blocks it until the user has consented to the associated category. Enable only the services that are actually used.';
$string['admindashboard'] = 'Consent Overview';
$string['banner_default_intro'] = '{$a} uses cookies and similar technologies. Please choose which categories you consent to.';
$string['banner_settings_title'] = 'Detailed privacy settings';
$string['banner_title'] = 'Your privacy choices';
$string['btn_acceptall'] = 'Accept all';
$string['btn_acceptall_label'] = 'Accept all cookies and tracking';
$string['btn_back'] = 'Back';
$string['btn_close_label'] = 'Close – essential cookies only';
$string['btn_essential'] = 'Essential only';
$string['btn_essential_label'] = 'Decline optional cookies — essential only';
$string['btn_export_csv'] = 'Export audit log (CSV)';

$string['btn_give'] = 'Give consent';
$string['btn_save'] = 'Save selection';
$string['btn_settings'] = 'Settings';
$string['btn_withdraw'] = 'Withdraw';
$string['btn_withdraw_confirm'] = 'Are you sure you want to withdraw your consent for this category?';
$string['cat_required'] = 'Required';

$string['dashboard_col_optin_rate'] = 'Opt-in rate';
$string['dashboard_col_userid'] = 'User ID';
$string['dashboard_recentlog'] = 'Recent consent activity';
$string['dashboard_revision'] = 'Current revision';
$string['dashboard_stats'] = 'Consent statistics';
$string['dashboard_title'] = 'Consent Manager — Admin Dashboard';
$string['error_cannot_withdraw_required'] = 'You cannot withdraw consent for a required (essential) category.';
$string['error_invalid_regex'] = 'Invalid regular expression: {$a}';

$string['event_consent_given'] = 'Consent given';
$string['event_consent_withdrawn'] = 'Consent withdrawn';
$string['event_revision_published'] = 'Consent revision published';

$string['form_cat_description'] = 'Description';
$string['form_cat_name'] = 'Display name';
$string['form_cat_required'] = 'Required (essential)';
$string['form_cat_required_help'] = 'If checked, users cannot decline this category (e.g. technically necessary cookies).';
$string['form_cat_shortname'] = 'Short name';
$string['form_cat_shortname_help'] = 'Machine-readable identifier (letters, digits, underscores). Example: <code>marketing</code>';
$string['form_cat_sortorder'] = 'Sort order';
$string['form_svc_category'] = 'Category';
$string['form_svc_description'] = 'Description';
$string['form_svc_domainpatterns'] = 'Domain patterns (regex)';
$string['form_svc_domainpatterns_help'] = 'One regular expression per line, matched against the iframe <code>src</code> attribute. Example: <code>youtube\\.com|youtu\\.be</code>';
$string['form_svc_enabled'] = 'Enabled';

$string['form_svc_name'] = 'Service name';
$string['form_svc_privacyurl'] = 'Provider privacy policy URL';
$string['form_svc_provider'] = 'Provider (legal entity)';
$string['history_col_action'] = 'Action';
$string['history_col_category'] = 'Category';
$string['history_col_revision'] = 'Revision';
$string['history_col_time'] = 'Time';
$string['history_empty'] = 'No consent history found.';

$string['history_title'] = 'Consent History';
$string['imprint'] = 'Imprint';

$string['install_cat_essential_desc'] = 'Technically necessary for the operation of the platform. Cannot be declined by users.';
$string['install_cat_essential_name'] = 'Essential';
$string['install_cat_functional_desc'] = 'Enable embedded content and extended features, e.g. videos and interactive H5P elements.';
$string['install_cat_functional_name'] = 'Functional';
$string['install_cat_marketing_desc'] = 'Enable personalised content and embedding of platforms such as YouTube that include their own tracking mechanisms.';

$string['install_cat_marketing_name'] = 'Marketing';
$string['install_cat_statistics_desc'] = 'Help understand how users use the platform. Data is only processed in aggregate form.';
$string['install_cat_statistics_name'] = 'Statistics';
$string['install_svc_googlefonts_desc'] = 'Font service by Google. Loads fonts from Google servers — IP addresses are transmitted to Google in the process.';
$string['install_svc_googlemaps_desc'] = 'Map service by Google Ireland Ltd. Used for embedded Google Maps.';
$string['install_svc_h5p_desc'] = 'Hosting platform for interactive H5P content. Used when H5P content is embedded from h5p.com.';
$string['install_svc_matomo_desc'] = 'Privacy-friendly web analytics tool. Please adjust the domain pattern to match your own Matomo instance URL.';

$string['install_svc_vimeo_desc'] = 'Video platform by Vimeo, Inc. Used for embedded Vimeo videos.';
$string['install_svc_youtube_desc'] = 'Video platform by Google Ireland Ltd. Used for embedded YouTube videos. Data may be transferred to the USA.';
$string['local/consentmanager:exportlogs'] = 'Export consent audit log';
$string['local/consentmanager:giveconsent'] = 'Give or withdraw own consent';

$string['local/consentmanager:manage'] = 'Manage consent categories and services';
$string['local/consentmanager:viewreports'] = 'View consent reports';
$string['log_action_expired'] = 'Expired';

$string['log_action_given'] = 'Consent given';
$string['log_action_revoked_by_admin'] = 'Revoked by administrator';
$string['log_action_withdrawn'] = 'Consent withdrawn';
$string['managecategories'] = 'Manage Categories';
$string['manageservices'] = 'Manage Services';

$string['mypreferences'] = 'My Consents';
$string['mypreferences_intro'] = 'Here you can view and manage your current consent choices. Withdrawing consent is just as easy as giving it.';
$string['mypreferences_title'] = 'My Privacy Consents';
$string['placeholder_btn'] = 'Accept and show';
$string['placeholder_btn_label'] = 'Accept consent and show {$a} content';
$string['placeholder_category'] = 'This content requires your consent in the category: {$a}';
$string['placeholder_label'] = 'Blocked content from {$a}';
$string['placeholder_msg'] = 'To display this {$a} content, you need to give your consent.';
$string['placeholder_privacy'] = 'Privacy policy of provider';

$string['pluginname'] = 'Consent Manager';

$string['preview_btn'] = 'Preview banner';
$string['preview_close'] = '✕ Close preview';

$string['privacy:metadata:consents'] = 'Stores the current consent choice of each user per category.';
$string['privacy:metadata:consents:catid'] = 'The consent category.';
$string['privacy:metadata:consents:guesttoken'] = 'A random session-bound identifier used to link consent records of an anonymous (not-logged-in) visitor across requests. Not linked to any personal identifier.';
$string['privacy:metadata:consents:revision'] = 'The consent revision this entry applies to.';
$string['privacy:metadata:consents:status'] = 'The consent status: 0=declined, 1=given, 2=withdrawn.';
$string['privacy:metadata:consents:timecreated'] = 'When the consent record was first created.';
$string['privacy:metadata:consents:timemodified'] = 'When the consent record was last modified.';
$string['privacy:metadata:consents:userid'] = 'The ID of the user who gave or declined consent.';
$string['privacy:metadata:log'] = 'Append-only audit trail of all consent actions. User IDs are anonymised upon account deletion, but the records themselves are retained to fulfil the accountability obligation under Art. 7 GDPR.';
$string['privacy:metadata:log:action'] = 'The action taken: given, withdrawn, revoked_by_admin, or expired.';
$string['privacy:metadata:log:catid'] = 'The category the action relates to.';
$string['privacy:metadata:log:guesttoken'] = 'A random session-bound identifier used to associate audit log entries with an anonymous visitor. Not linked to any personal identifier.';
$string['privacy:metadata:log:ipaddress'] = 'The IP address of the visitor (optionally anonymised).';
$string['privacy:metadata:log:revision'] = 'The consent revision at the time of action.';
$string['privacy:metadata:log:timecreated'] = 'When this log entry was created.';

$string['privacy:metadata:log:useragent'] = 'The browser user-agent string (only recorded if enabled by admin).';
$string['privacy:metadata:log:userid'] = 'The ID of the user (nulled on account deletion).';
$string['privacy:path:consents'] = 'Current Consents';
$string['privacy:path:log'] = 'Consent Audit Log';

$string['privacypolicy'] = 'Privacy policy';
$string['settings_anonymize_ip'] = 'Anonymise IP address';
$string['settings_anonymize_ip_desc'] = 'If enabled, the last octet (IPv4) or the last 80 bits (IPv6) of the IP address are zeroed before storage.';
$string['settings_banner'] = 'Banner appearance';
$string['settings_banner_desc'] = 'Appearance of the consent banner. The intro text appears directly in the banner and should briefly explain in plain language why consent is being collected.';

$string['settings_banner_intro'] = 'Banner introduction text';
$string['settings_banner_intro_desc'] = 'HTML text shown at the top of the consent banner. If empty, a default text is used.';
$string['settings_enabled'] = 'Enable Consent Manager';
$string['settings_enabled_desc'] = 'Show the consent banner and activate iframe filtering.';
$string['settings_general'] = 'General';
$string['settings_general_desc'] = 'Basic settings. While the Consent Manager is disabled, no banner is shown and all iframes load without any consent check.';
$string['settings_imprint_url'] = 'Imprint URL';
$string['settings_imprint_url_desc'] = 'Link to your site\'s imprint/legal notice, shown in the banner footer.';

$string['settings_log_ipaddress'] = 'Log IP address';
$string['settings_log_ipaddress_desc'] = 'Store the visitor\'s IP address in the consent audit log.';
$string['settings_log_useragent'] = 'Log user agent';
$string['settings_log_useragent_desc'] = 'Store the browser user-agent string in the audit log. Disabled by default to minimise personal data.';
$string['settings_logretention'] = 'Log retention period';
$string['settings_logretention_desc'] = 'Audit log entries older than this duration will be deleted by the scheduled cleanup task.';
$string['settings_privacy'] = 'Privacy & Logging';
$string['settings_privacy_desc'] = 'Defines which personal data is stored in the audit log. IP anonymisation is recommended (last octet set to 0). The log is required for accountability purposes under GDPR Art. 7.';
$string['settings_privacypolicy_url'] = 'Privacy policy URL';
$string['settings_privacypolicy_url_desc'] = 'Link to your site\'s privacy policy, shown in the banner footer.';
$string['settings_revision'] = 'Revision management';
$string['settings_revision_current'] = 'Current revision';
$string['settings_revision_current_desc'] = 'Increment this number to re-ask all users for consent (e.g. after adding a new service). Users will see the banner again on their next visit.';
$string['settings_revision_desc'] = 'Increment the revision number after substantive changes to services or categories. Users will then be asked for consent again on their next page load. Mark minor editorial corrections as "Minor" to skip re-consent.';
$string['settings_revision_minor'] = 'Minor revision';
$string['settings_revision_minor_desc'] = 'If checked, the revision increase is considered minor (e.g. only a text correction) and users will not be re-asked.';
$string['settings_sitename'] = 'Site name (in banner)';
$string['settings_sitename_desc'] = 'Override the site name shown in the banner introduction text. Leave blank to use the Moodle site name.';
$string['status_declined'] = 'Not consented';
$string['status_given'] = 'Consent given';
$string['task_cleanup_logs'] = 'Clean up expired consent audit log entries';
