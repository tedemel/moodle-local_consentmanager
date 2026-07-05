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
 * Deutsche Sprachstrings für local_consentmanager.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admin_categories_intro'] = 'Kategorien gruppieren Dienste nach Zweck. Jeder Dienst gehört zu genau einer Kategorie. Nutzer stimmen pro Kategorie zu oder lehnen ab – nicht pro einzelnem Dienst. Die Kategorie <strong>Essenziell</strong> ist immer aktiv und kann nicht abgewählt werden.';
$string['admin_dashboard_intro'] = 'Übersicht über alle Einwilligungsaktivitäten auf dieser Plattform. Die Tabelle zeigt Opt-in-Raten je Kategorie sowie die zuletzt protokollierten Aktionen.';
$string['admin_services_intro'] = 'Dienste sind Drittanbieter, deren Inhalte auf dieser Plattform eingebettet werden (z. B. YouTube, Vimeo). Pro Dienst wird ein Domain-Muster (regulärer Ausdruck) hinterlegt. Der Filter prüft jeden Iframe gegen diese Muster und blendet ihn aus, solange keine Einwilligung für die zugehörige Kategorie vorliegt. Aktivieren Sie nur die Dienste, die tatsächlich verwendet werden.';
$string['admindashboard'] = 'Consent-Übersicht';
$string['banner_default_intro'] = '{$a} verwendet Cookies und ähnliche Technologien. Bitte wählen Sie, welchen Kategorien Sie zustimmen.';
$string['banner_settings_title'] = 'Detaillierte Datenschutzeinstellungen';
$string['banner_title'] = 'Ihre Datenschutz-Einstellungen';
$string['btn_acceptall'] = 'Alle akzeptieren';
$string['btn_acceptall_label'] = 'Allen Cookies und Tracking zustimmen';
$string['btn_back'] = 'Zurück';
$string['btn_close_label'] = 'Schließen – nur essenzielle Cookies zulassen';
$string['btn_essential'] = 'Nur essenzielle';
$string['btn_essential_label'] = 'Optionale Cookies ablehnen – nur essenzielle zulassen';
$string['btn_export_csv'] = 'Auditprotokoll exportieren (CSV)';

$string['btn_give'] = 'Zustimmen';
$string['btn_save'] = 'Auswahl speichern';
$string['btn_settings'] = 'Einstellungen';
$string['btn_withdraw'] = 'Widerrufen';
$string['btn_withdraw_confirm'] = 'Möchten Sie Ihre Einwilligung für diese Kategorie wirklich widerrufen?';
$string['cat_required'] = 'Erforderlich';

$string['dashboard_col_optin_rate'] = 'Opt-in-Rate';
$string['dashboard_col_userid'] = 'Nutzer-ID';
$string['dashboard_recentlog'] = 'Aktuelle Einwilligungsaktivitäten';
$string['dashboard_revision'] = 'Aktuelle Revision';
$string['dashboard_stats'] = 'Einwilligungsstatistik';
$string['dashboard_title'] = 'Consent Manager – Admin-Dashboard';
$string['error_cannot_withdraw_required'] = 'Einwilligungen für erforderliche (essenzielle) Kategorien können nicht widerrufen werden.';
$string['error_invalid_regex'] = 'Ungültiger regulärer Ausdruck: {$a}';

$string['event_consent_given'] = 'Einwilligung erteilt';
$string['event_consent_withdrawn'] = 'Einwilligung widerrufen';
$string['event_revision_published'] = 'Consent-Revision veröffentlicht';

$string['form_cat_description'] = 'Beschreibung';
$string['form_cat_name'] = 'Anzeigename';
$string['form_cat_required'] = 'Erforderlich (essenziell)';
$string['form_cat_required_help'] = 'Wenn aktiviert, können Nutzer diese Kategorie nicht ablehnen (z. B. technisch notwendige Cookies).';
$string['form_cat_shortname'] = 'Kurzname';
$string['form_cat_shortname_help'] = 'Technische Bezeichnung (Buchstaben, Ziffern, Unterstriche). Beispiel: <code>marketing</code>';
$string['form_cat_sortorder'] = 'Reihenfolge';
$string['form_svc_category'] = 'Kategorie';
$string['form_svc_description'] = 'Beschreibung';
$string['form_svc_domainpatterns'] = 'Domain-Pattern (Regex)';
$string['form_svc_domainpatterns_help'] = 'Ein regulärer Ausdruck pro Zeile, der gegen das <code>src</code>-Attribut von Iframes geprüft wird. Beispiel: <code>youtube\\.com|youtu\\.be</code>';
$string['form_svc_enabled'] = 'Aktiviert';

$string['form_svc_name'] = 'Dienstname';
$string['form_svc_privacyurl'] = 'Datenschutzerklärung des Anbieters (URL)';
$string['form_svc_provider'] = 'Anbieter (juristische Person)';
$string['history_col_action'] = 'Aktion';
$string['history_col_category'] = 'Kategorie';
$string['history_col_revision'] = 'Revision';
$string['history_col_time'] = 'Zeitpunkt';
$string['history_empty'] = 'Keine Einwilligungshistorie vorhanden.';

$string['history_title'] = 'Einwilligungshistorie';
$string['imprint'] = 'Impressum';

$string['install_cat_essential_desc'] = 'Technisch notwendig für den Betrieb der Plattform. Kann von Nutzern nicht abgelehnt werden.';
$string['install_cat_essential_name'] = 'Essenziell';
$string['install_cat_functional_desc'] = 'Ermöglichen eingebettete Inhalte und erweiterte Funktionen, z. B. Videos und interaktive H5P-Elemente.';
$string['install_cat_functional_name'] = 'Funktional';
$string['install_cat_marketing_desc'] = 'Ermöglichen personalisierte Inhalte und das Einbetten von Plattformen wie YouTube, die eigene Tracking-Mechanismen mitbringen.';

$string['install_cat_marketing_name'] = 'Marketing';
$string['install_cat_statistics_desc'] = 'Helfen zu verstehen, wie Nutzer die Plattform verwenden. Daten werden nur aggregiert ausgewertet.';
$string['install_cat_statistics_name'] = 'Statistik';
$string['install_svc_googlefonts_desc'] = 'Schriftarten-Dienst von Google. Lädt Schriftarten von Google-Servern – dabei werden IP-Adressen an Google übertragen.';
$string['install_svc_googlemaps_desc'] = 'Kartendienst von Google Ireland Ltd. Wird für eingebettete Google Maps-Karten verwendet.';
$string['install_svc_h5p_desc'] = 'Hosting-Plattform für interaktive H5P-Inhalte. Wird verwendet, wenn H5P-Inhalte von h5p.com eingebettet werden.';
$string['install_svc_matomo_desc'] = 'Datenschutzfreundliches Webanalyse-Tool. Domainmuster bitte auf die eigene Matomo-Instanz anpassen.';

$string['install_svc_vimeo_desc'] = 'Videoplattform von Vimeo, Inc. Wird für eingebettete Vimeo-Videos verwendet.';
$string['install_svc_youtube_desc'] = 'Videoplattform von Google Ireland Ltd. Wird für eingebettete YouTube-Videos verwendet. Daten werden ggf. in die USA übertragen.';
$string['local/consentmanager:exportlogs'] = 'Consent-Auditprotokoll exportieren';
$string['local/consentmanager:giveconsent'] = 'Eigene Einwilligung erteilen oder widerrufen';

$string['local/consentmanager:manage'] = 'Consent-Kategorien und -Dienste verwalten';
$string['local/consentmanager:viewreports'] = 'Consent-Berichte einsehen';
$string['log_action_expired'] = 'Abgelaufen';

$string['log_action_given'] = 'Einwilligung erteilt';
$string['log_action_revoked_by_admin'] = 'Durch Administrator widerrufen';
$string['log_action_withdrawn'] = 'Einwilligung widerrufen';
$string['managecategories'] = 'Kategorien verwalten';
$string['manageservices'] = 'Dienste verwalten';

$string['mypreferences'] = 'Meine Einwilligungen';
$string['mypreferences_intro'] = 'Hier können Sie Ihre aktuellen Einwilligungen einsehen und verwalten. Der Widerruf ist genauso einfach wie die Erteilung der Einwilligung (Art. 7 Abs. 3 DSGVO).';
$string['mypreferences_title'] = 'Meine Datenschutz-Einwilligungen';
$string['placeholder_btn'] = 'Zustimmen und anzeigen';
$string['placeholder_btn_label'] = 'Einwilligung erteilen und {$a}-Inhalt anzeigen';
$string['placeholder_category'] = 'Dieser Inhalt erfordert Ihre Einwilligung in der Kategorie: {$a}';
$string['placeholder_label'] = 'Blockierter Inhalt von {$a}';
$string['placeholder_msg'] = 'Um diesen {$a}-Inhalt anzuzeigen, ist Ihre Einwilligung erforderlich.';
$string['placeholder_privacy'] = 'Datenschutzerklärung des Anbieters';

$string['pluginname'] = 'Consent Manager';

$string['preview_btn'] = 'Banner-Vorschau';
$string['preview_close'] = '✕ Vorschau schließen';

$string['privacy:metadata:consents'] = 'Speichert den aktuellen Einwilligungsstatus jedes Nutzers je Kategorie.';
$string['privacy:metadata:consents:catid'] = 'Die Einwilligungskategorie.';
$string['privacy:metadata:consents:guesttoken'] = 'Eine zufällig erzeugte, sitzungsgebundene Kennung, mit der Einwilligungseinträge eines nicht angemeldeten Besuchers über mehrere Anfragen hinweg verknüpft werden. Es besteht keine Verknüpfung zu personenbezogenen Identifikatoren.';
$string['privacy:metadata:consents:revision'] = 'Die Consent-Revision, auf die sich dieser Eintrag bezieht.';
$string['privacy:metadata:consents:status'] = 'Status der Einwilligung: 0=abgelehnt, 1=erteilt, 2=widerrufen.';
$string['privacy:metadata:consents:timecreated'] = 'Zeitpunkt der Erstanlage des Einwilligungseintrags.';
$string['privacy:metadata:consents:timemodified'] = 'Zeitpunkt der letzten Änderung des Einwilligungseintrags.';
$string['privacy:metadata:consents:userid'] = 'ID des Nutzers, der die Einwilligung erteilt oder abgelehnt hat.';
$string['privacy:metadata:log'] = 'Unveränderliches Auditprotokoll aller Einwilligungsaktionen. Nutzer-IDs werden bei Kontolöschung anonymisiert, die Einträge selbst bleiben zur Erfüllung der Rechenschaftspflicht nach Art. 7 DSGVO erhalten.';
$string['privacy:metadata:log:action'] = 'Die durchgeführte Aktion: given, withdrawn, revoked_by_admin oder expired.';
$string['privacy:metadata:log:catid'] = 'Die Kategorie, auf die sich die Aktion bezieht.';
$string['privacy:metadata:log:guesttoken'] = 'Eine zufällig erzeugte, sitzungsgebundene Kennung zur Verknüpfung von Auditprotokoll-Einträgen mit einem anonymen Besucher. Es besteht keine Verknüpfung zu personenbezogenen Identifikatoren.';
$string['privacy:metadata:log:ipaddress'] = 'IP-Adresse des Besuchers (optional anonymisiert).';
$string['privacy:metadata:log:revision'] = 'Die Consent-Revision zum Zeitpunkt der Aktion.';
$string['privacy:metadata:log:timecreated'] = 'Zeitpunkt der Erstellung dieses Protokolleintrags.';

$string['privacy:metadata:log:useragent'] = 'Browser-User-Agent-String (nur erfasst, wenn vom Admin aktiviert).';
$string['privacy:metadata:log:userid'] = 'ID des Nutzers (wird bei Kontolöschung auf NULL gesetzt).';
$string['privacy:path:consents'] = 'Aktuelle Einwilligungen';
$string['privacy:path:log'] = 'Einwilligungsauditprotokoll';

$string['privacypolicy'] = 'Datenschutzerklärung';
$string['settings_anonymize_ip'] = 'IP-Adresse anonymisieren';
$string['settings_anonymize_ip_desc'] = 'Wenn aktiviert, wird das letzte Oktett (IPv4) bzw. die letzten 80 Bit (IPv6) der IP-Adresse vor der Speicherung auf 0 gesetzt.';
$string['settings_banner'] = 'Banner-Erscheinungsbild';
$string['settings_banner_desc'] = 'Gestaltung des Consent-Banners. Der Einleitungstext erscheint direkt im Banner und sollte kurz und verständlich erklären, warum Einwilligungen eingeholt werden.';

$string['settings_banner_intro'] = 'Banner-Einleitungstext';
$string['settings_banner_intro_desc'] = 'HTML-Text, der am Anfang des Consent-Banners angezeigt wird. Leer lassen für den Standardtext.';
$string['settings_enabled'] = 'Consent Manager aktivieren';
$string['settings_enabled_desc'] = 'Consent-Banner anzeigen und Iframe-Filterung aktivieren.';
$string['settings_general'] = 'Allgemein';
$string['settings_general_desc'] = 'Grundlegende Einstellungen. Solange der Consent Manager deaktiviert ist, werden keine Banner angezeigt und alle Iframes laden ohne Einwilligungsprüfung.';
$string['settings_imprint_url'] = 'URL des Impressums';
$string['settings_imprint_url_desc'] = 'Link zum Impressum Ihrer Seite, angezeigt im Banner-Footer.';

$string['settings_log_ipaddress'] = 'IP-Adresse protokollieren';
$string['settings_log_ipaddress_desc'] = 'Die IP-Adresse des Besuchers im Consent-Auditprotokoll speichern.';
$string['settings_log_useragent'] = 'User-Agent protokollieren';
$string['settings_log_useragent_desc'] = 'Den Browser-User-Agent-String im Auditprotokoll speichern. Standardmäßig deaktiviert, um personenbezogene Daten zu minimieren.';
$string['settings_logretention'] = 'Aufbewahrungsdauer der Protokolle';
$string['settings_logretention_desc'] = 'Auditprotokoll-Einträge, die älter als diese Dauer sind, werden durch den geplanten Bereinigungstask gelöscht.';
$string['settings_privacy'] = 'Datenschutz & Protokollierung';
$string['settings_privacy_desc'] = 'Legt fest, welche personenbezogenen Daten im Auditprotokoll gespeichert werden. IP-Anonymisierung ist empfohlen (letztes Oktett wird auf 0 gesetzt). Das Protokoll wird für Nachweiszwecke nach Art. 7 DSGVO benötigt.';
$string['settings_privacypolicy_url'] = 'URL der Datenschutzerklärung';
$string['settings_privacypolicy_url_desc'] = 'Link zur Datenschutzerklärung Ihrer Seite, angezeigt im Banner-Footer.';
$string['settings_revision'] = 'Revisionsverwaltung';
$string['settings_revision_current'] = 'Aktuelle Revision';
$string['settings_revision_current_desc'] = 'Diese Nummer erhöhen, um alle Nutzer erneut um Einwilligung zu bitten (z. B. nach Hinzufügen eines neuen Dienstes).';
$string['settings_revision_desc'] = 'Bei substanziellen Änderungen an Diensten oder Kategorien die Revisionsnummer erhöhen. Nutzer werden dann beim nächsten Seitenaufruf erneut um Zustimmung gebeten. Kleinere redaktionelle Korrekturen als „Minor" kennzeichnen – dann entfällt die erneute Abfrage.';
$string['settings_revision_minor'] = 'Geringfügige Revision';
$string['settings_revision_minor_desc'] = 'Wenn aktiviert, gilt die Revisionserhöhung als geringfügig und Nutzer werden nicht erneut gefragt.';
$string['settings_sitename'] = 'Sitename (im Banner)';
$string['settings_sitename_desc'] = 'Überschreibt den im Banner angezeigten Sitenamen. Leer lassen für den Moodle-Sitenamen.';
$string['status_declined'] = 'Nicht zugestimmt';
$string['status_given'] = 'Einwilligung erteilt';
$string['task_cleanup_logs'] = 'Abgelaufene Consent-Auditprotokoll-Einträge bereinigen';
