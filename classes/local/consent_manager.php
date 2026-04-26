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
 * Central consent manager service class.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\local;

use local_consentmanager\event\consent_given;
use local_consentmanager\event\consent_withdrawn;

/**
 * Singleton service — all consent read/write operations go through here.
 */
class consent_manager {
    /** @var self|null */
    private static ?self $instance = null;

    /** @var category[]|null Cached categories keyed by id. */
    private ?array $categories = null;

    /** @var service[]|null Cached enabled services. */
    private ?array $services = null;

    /** @var array|null In-request consent cache [userid_or_guest => [catid => bool]] */
    private ?array $requestcache = null;

    /**
     * Private constructor — use {@see self::instance()}.
     */
    private function __construct() {
    }

    /**
     * Return the singleton instance.
     *
     * @return self
     */
    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return all categories ordered by sortorder.
     *
     * @return category[]
     */
    public function get_categories(): array {
        if ($this->categories === null) {
            global $DB;
            $this->categories = [];
            $records = $DB->get_records('local_consentmanager_cats', null, 'sortorder ASC, id ASC');
            foreach ($records as $rec) {
                $this->categories[$rec->id] = new category($rec);
            }
        }
        return $this->categories;
    }

    /**
     * Return a single category by id, or null.
     *
     * @param int $id
     * @return category|null
     */
    public function get_category(int $id): ?category {
        $cats = $this->get_categories();
        return $cats[$id] ?? null;
    }

    /**
     * Return a single category by shortname, or null.
     *
     * @param string $shortname
     * @return category|null
     */
    public function get_category_by_shortname(string $shortname): ?category {
        foreach ($this->get_categories() as $cat) {
            if ($cat->shortname === $shortname) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Save (insert or update) a category. Clears the in-memory cache.
     *
     * @param category $cat
     * @return int new or existing id
     */
    public function save_category(category $cat): int {
        global $DB;
        $rec = $cat->to_record();
        if (empty($rec->id)) {
            $id = $DB->insert_record('local_consentmanager_cats', $rec);
        } else {
            $DB->update_record('local_consentmanager_cats', $rec);
            $id = $rec->id;
        }
        $this->categories = null; // Invalidate.
        return $id;
    }

    /**
     * Delete a category and its associated consent records.
     *
     * @param int $catid
     */
    public function delete_category(int $catid): void {
        global $DB;
        $DB->delete_records('local_consentmanager_services', ['catid' => $catid]);
        $DB->delete_records('local_consentmanager_consents', ['catid' => $catid]);
        $DB->delete_records('local_consentmanager_cats', ['id' => $catid]);
        $this->categories = null;
        $this->services   = null;
        $this->invalidate_user_consent_cache();
    }

    /**
     * Return all enabled services.
     *
     * @return service[]
     */
    public function get_services(): array {
        if ($this->services === null) {
            global $DB;
            $cache = \cache::make('local_consentmanager', 'serviceregistry');
            $cached = $cache->get('all');
            if ($cached !== false) {
                $this->services = $cached;
                return $this->services;
            }
            $this->services = [];
            $records = $DB->get_records('local_consentmanager_services', ['enabled' => 1]);
            foreach ($records as $rec) {
                $this->services[$rec->id] = new service($rec);
            }
            $cache->set('all', $this->services);
        }
        return $this->services;
    }

    /**
     * Return all services (including disabled) — for admin UI.
     *
     * @return service[]
     */
    public function get_all_services(): array {
        global $DB;
        $services = [];
        $records = $DB->get_records('local_consentmanager_services', null, 'catid ASC, name ASC');
        foreach ($records as $rec) {
            $services[$rec->id] = new service($rec);
        }
        return $services;
    }

    /**
     * Save a service record.
     *
     * @param service $svc
     * @return int
     */
    public function save_service(service $svc): int {
        global $DB;
        $rec = $svc->to_record();
        if (empty($rec->id)) {
            $id = $DB->insert_record('local_consentmanager_services', $rec);
        } else {
            $DB->update_record('local_consentmanager_services', $rec);
            $id = $rec->id;
        }
        $this->services = null;
        \cache::make('local_consentmanager', 'serviceregistry')->delete('all');
        return $id;
    }

    /**
     * Delete a service.
     *
     * @param int $serviceid
     */
    public function delete_service(int $serviceid): void {
        global $DB;
        $DB->delete_records('local_consentmanager_services', ['id' => $serviceid]);
        $this->services = null;
        \cache::make('local_consentmanager', 'serviceregistry')->delete('all');
    }

    /**
     * Return the consent status array for the current user/guest.
     * Keys are category IDs, values are bool (true = given).
     *
     * @return bool[]
     */
    public function get_user_consents(): array {
        global $DB, $USER;

        $cachekey = $this->get_cache_key();
        if (isset($this->requestcache[$cachekey])) {
            return $this->requestcache[$cachekey];
        }

        // Try MUC application cache.
        $appcache = \cache::make('local_consentmanager', 'userconsents');
        $cached   = $appcache->get($cachekey);
        if ($cached !== false) {
            $this->requestcache[$cachekey] = $cached;
            return $cached;
        }

        $consents = [];
        // All essential categories are always given.
        foreach ($this->get_categories() as $cat) {
            if ($cat->required) {
                $consents[$cat->id] = true;
            }
        }

        // Load from DB.
        if (isloggedin() && !isguestuser()) {
            $records = $DB->get_records('local_consentmanager_consents', ['userid' => $USER->id]);
        } else {
            $token = $this->get_guest_token();
            $records = $token
                ? $DB->get_records('local_consentmanager_consents', ['guesttoken' => $token])
                : [];
        }

        foreach ($records as $rec) {
            $consents[$rec->catid] = ((int)$rec->status === consent_record::STATUS_GIVEN);
        }

        $appcache->set($cachekey, $consents);
        $this->requestcache[$cachekey] = $consents;
        return $consents;
    }

    /**
     * Return whether the user has consented to a category.
     *
     * @param int $catid
     * @return bool
     */
    public function has_consent(int $catid): bool {
        $cat = $this->get_category($catid);
        if ($cat && $cat->required) {
            return true;
        }
        return $this->get_user_consents()[$catid] ?? false;
    }

    /**
     * Store or update consent for one or more categories.
     *
     * @param int[] $catids    Category IDs to grant consent to.
     * @param bool  $acceptall If true, all categories are accepted.
     * @param string|null $providedtoken Optional guest token from the page render.
     */
    public function store_consent(array $catids, bool $acceptall = false, ?string $providedtoken = null): void {
        global $DB, $USER;

        $revision   = (int)get_config('local_consentmanager', 'revision');
        $allcatids  = array_keys($this->get_categories());
        $targetcats = $acceptall ? $allcatids : $catids;

        $isloggedin   = isloggedin() && !isguestuser();
        if ($isloggedin) {
            $guesttoken = null;
            $userid     = (int)$USER->id;
        } else {
            // Prefer client-provided token (works under NO_MOODLE_COOKIES on service-nologin.php).
            $guesttoken = $providedtoken ?: $this->get_or_create_guest_token();
            $userid     = null;
        }

        foreach ($targetcats as $catid) {
            $catid = (int)$catid;
            $this->upsert_consent($userid, $guesttoken, $catid, consent_record::STATUS_GIVEN, $revision);
            $this->write_log($userid, $guesttoken, 'given', $catid, $revision);
            // Fire event.
            $event = consent_given::create([
                'context' => \context_system::instance(),
                'userid'  => $userid ?? 0,
                'other'   => ['catid' => $catid, 'revision' => $revision],
            ]);
            $event->trigger();
        }

        $this->invalidate_user_consent_cache();
    }

    /**
     * Withdraw consent for a specific category.
     *
     * @param int $catid
     */
    public function withdraw_consent(int $catid): void {
        global $DB, $USER;

        $cat = $this->get_category($catid);
        if ($cat && $cat->required) {
            throw new \moodle_exception('error_cannot_withdraw_required', 'local_consentmanager');
        }

        $revision   = (int)get_config('local_consentmanager', 'revision');
        $isloggedin = isloggedin() && !isguestuser();
        $guesttoken = $isloggedin ? null : $this->get_guest_token();
        $userid     = $isloggedin ? (int)$USER->id : null;

        $this->upsert_consent($userid, $guesttoken, $catid, consent_record::STATUS_WITHDRAWN, $revision);
        $this->write_log($userid, $guesttoken, 'withdrawn', $catid, $revision);

        $event = consent_withdrawn::create([
            'context' => \context_system::instance(),
            'userid'  => $userid ?? 0,
            'other'   => ['catid' => $catid, 'revision' => $revision],
        ]);
        $event->trigger();

        $this->invalidate_user_consent_cache();
    }

    /**
     * Check whether the user needs to see the consent banner.
     * Returns true if no consents exist for the current revision.
     *
     * @return bool
     */
    public function needs_consent(): bool {
        global $DB, $USER;

        if (!get_config('local_consentmanager', 'enabled')) {
            return false;
        }

        $revision = (int)get_config('local_consentmanager', 'revision');

        if (isloggedin() && !isguestuser()) {
            $count = $DB->count_records_select(
                'local_consentmanager_consents',
                'userid = :userid AND revision >= :revision',
                ['userid' => $USER->id, 'revision' => $revision]
            );
        } else {
            $token = $this->get_guest_token();
            if (!$token) {
                return true; // No token → definitely no consent yet.
            }
            $count = $DB->count_records_select(
                'local_consentmanager_consents',
                'guesttoken = :token AND revision >= :revision',
                ['token' => $token, 'revision' => $revision]
            );
        }
        return $count === 0;
    }

    /**
     * Scan $text for iframes matching registered service patterns and replace
     * unconsented ones with an opt-in placeholder.
     *
     * @param string   $text
     * @param \context $context
     * @return string
     */
    public function replace_unconsented_iframes(string $text, \context $context): string {
        // Quick bail-out: no iframe → nothing to do.
        if (stripos($text, '<iframe') === false) {
            return $text;
        }

        $services  = $this->get_services();
        $consents  = $this->get_user_consents();
        $categories = $this->get_categories();

        // For performance, build a map catid => bool.
        $catconsent = [];
        foreach ($categories as $cat) {
            $catconsent[$cat->id] = $cat->required || ($consents[$cat->id] ?? false);
        }

        // Use a callback to replace each iframe individually.
        $text = preg_replace_callback(
            '/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>.*?<\/iframe>/is',
            function (array $matches) use ($services, $catconsent, $categories) {
                $src     = $matches[1];
                $fulltag = $matches[0];

                foreach ($services as $svc) {
                    if (!$svc->enabled) {
                        continue;
                    }
                    if (!$svc->matches_src($src)) {
                        continue;
                    }
                    // Found a matching service. Check consent.
                    if (!empty($catconsent[$svc->catid])) {
                        return $fulltag; // Consent given → show as-is.
                    }
                    // No consent → render placeholder.
                    $cat = $categories[$svc->catid] ?? null;
                    return $this->render_placeholder($svc, $cat, $src);
                }
                return $fulltag; // No matching service → pass through.
            },
            $text
        );

        return $text;
    }

    /**
     * Render the HTML placeholder for a blocked iframe.
     *
     * @param service       $svc
     * @param category|null $cat
     * @param string        $src
     * @return string
     */
    private function render_placeholder(service $svc, ?category $cat, string $src): string {
        global $OUTPUT;
        $data = [
            'servicename' => s($svc->name),
            'catname'     => $cat ? s($cat->name) : '',
            'catid'       => $svc->catid,
            'src'         => s($src),
            'privacyurl'  => s($svc->privacyurl),
        ];
        return $OUTPUT->render_from_template('local_consentmanager/iframe_placeholder', $data);
    }

    /**
     * Event observer: anonymise consent data when a user is deleted.
     *
     * @param \core\event\user_deleted $event
     */
    public static function on_user_deleted(\core\event\user_deleted $event): void {
        global $DB;
        $userid = $event->relateduserid ?? $event->userid;
        if (!$userid) {
            return;
        }
        // Delete current consent entries (they are re-created if needed).
        $DB->delete_records('local_consentmanager_consents', ['userid' => $userid]);
        // Anonymise audit log (DSGVO: Nachweispflicht bleibt, Personenbezug entfällt).
        $DB->set_field('local_consentmanager_log', 'userid', null, ['userid' => $userid]);
    }

    /**
     * Insert or update a consent row.
     *
     * @param int|null $userid User id, or null for guests.
     * @param string|null $guesttoken Guest token, or null for logged-in users.
     * @param int $catid Category id.
     * @param int $status Status constant from {@see consent_record}.
     * @param int $revision Current revision number.
     */
    private function upsert_consent(?int $userid, ?string $guesttoken, int $catid, int $status, int $revision): void {
        global $DB;

        $conditions = ['catid' => $catid];
        if ($userid) {
            $conditions['userid'] = $userid;
        } else {
            $conditions['guesttoken'] = $guesttoken;
        }

        $existing = $DB->get_record('local_consentmanager_consents', $conditions);
        $now = time();

        if ($existing) {
            $existing->status       = $status;
            $existing->revision     = $revision;
            $existing->timemodified = $now;
            $DB->update_record('local_consentmanager_consents', $existing);
        } else {
            $rec               = new \stdClass();
            $rec->userid       = $userid;
            $rec->guesttoken   = $guesttoken;
            $rec->catid        = $catid;
            $rec->status       = $status;
            $rec->revision     = $revision;
            $rec->timecreated  = $now;
            $rec->timemodified = $now;
            $DB->insert_record('local_consentmanager_consents', $rec);
        }
    }

    /**
     * Write a row to the audit log.
     *
     * @param int|null $userid User id, or null for guests.
     * @param string|null $guesttoken Guest token, or null for logged-in users.
     * @param string $action One of: given, withdrawn, declined.
     * @param int $catid Category id.
     * @param int $revision Current revision number.
     */
    private function write_log(?int $userid, ?string $guesttoken, string $action, int $catid, int $revision): void {
        global $DB;

        if (!get_config('local_consentmanager', 'enabled')) {
            return;
        }

        $rec             = new \stdClass();
        $rec->userid     = $userid;
        $rec->guesttoken = $guesttoken;
        $rec->action     = $action;
        $rec->catid      = $catid;
        $rec->revision   = $revision;
        $rec->timecreated = time();

        // IP logging.
        if (get_config('local_consentmanager', 'log_ipaddress')) {
            $ip = getremoteaddr('0.0.0.0');
            if (get_config('local_consentmanager', 'anonymize_ip')) {
                $ip = $this->anonymize_ip($ip);
            }
            $rec->ipaddress = $ip;
        }

        // User-agent logging.
        if (get_config('local_consentmanager', 'log_useragent')) {
            $rec->useragent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000);
        }

        $DB->insert_record('local_consentmanager_log', $rec);
    }

    /**
     * Anonymise an IP address by zeroing the last octet(s).
     *
     * @param string $ip
     * @return string
     */
    private function anonymize_ip(string $ip): string {
        if (strpos($ip, ':') !== false) {
            // IPv6: zero last 80 bits (keep /48 prefix).
            $parts = explode(':', $ip);
            $parts = array_slice($parts, 0, 3);
            return implode(':', $parts) . '::';
        }
        // IPv4: zero last octet.
        $parts    = explode('.', $ip);
        $parts[3] = '0';
        return implode('.', $parts);
    }

    /**
     * Return a stable cache key for the current user/guest.
     *
     * @return string
     */
    private function get_cache_key(): string {
        global $USER;
        $revision = (int)get_config('local_consentmanager', 'revision');
        if (isloggedin() && !isguestuser()) {
            return "u_{$USER->id}_r{$revision}";
        }
        return 'g_' . ($this->get_guest_token() ?? 'none') . "_r{$revision}";
    }

    /** Cookie name used to persist a guest token across browser sessions. */
    private const GUEST_COOKIE = 'cm_guesttoken';

    /** Cookie lifetime in seconds (12 months). */
    private const GUEST_COOKIE_LIFETIME = 31536000;

    /**
     * Return the current guest token, preferring the long-lived cookie over the
     * session value (cookie persists across browser restarts).
     *
     * @return string|null
     */
    private function get_guest_token(): ?string {
        global $SESSION;
        if (!empty($_COOKIE[self::GUEST_COOKIE]) && self::is_valid_token($_COOKIE[self::GUEST_COOKIE])) {
            // Mirror into session so subsequent reads in this request stay consistent.
            if (empty($SESSION->consentmanager_guesttoken)) {
                $SESSION->consentmanager_guesttoken = $_COOKIE[self::GUEST_COOKIE];
            }
            return $_COOKIE[self::GUEST_COOKIE];
        }
        return $SESSION->consentmanager_guesttoken ?? null;
    }

    /**
     * Return or create a guest token. Persists in both the Moodle session AND a
     * 12-month first-party cookie so anonymous visitors are not re-asked for
     * consent after closing their browser.
     *
     * @return string
     */
    public function get_or_create_guest_token(): string {
        global $SESSION, $CFG;
        $token = $this->get_guest_token();
        if (empty($token)) {
            $token = hash('sha256', session_id() . random_bytes(16));
            $SESSION->consentmanager_guesttoken = $token;
        }
        // Reset the cookie on every request to extend the rolling expiry.
        if (!headers_sent()) {
            $secure = !empty($CFG->cookiesecure);
            setcookie(self::GUEST_COOKIE, $token, [
                'expires'  => time() + self::GUEST_COOKIE_LIFETIME,
                'path'     => $CFG->sessioncookiepath ?? '/',
                'domain'   => $CFG->sessioncookiedomain ?? '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $_COOKIE[self::GUEST_COOKIE] = $token;
        }
        return $token;
    }

    /**
     * Validate a guest token (must be a 64-char hex string from sha256).
     *
     * @param string $token
     * @return bool
     */
    private static function is_valid_token(string $token): bool {
        return (bool)preg_match('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Invalidate the MUC application cache for the current user.
     */
    private function invalidate_user_consent_cache(): void {
        $cachekey = $this->get_cache_key();
        \cache::make('local_consentmanager', 'userconsents')->delete($cachekey);
        $this->requestcache = null;
    }

    /**
     * Reset the singleton and all in-process caches. Test-helper only.
     *
     * Without this, PHPUnit's resetAfterTest() wipes the DB but the singleton
     * keeps its stale category/service/consent arrays in memory.
     */
    public static function reset_for_testing(): void {
        if (!defined('PHPUNIT_TEST') || !PHPUNIT_TEST) {
            throw new \coding_exception('reset_for_testing() must only be called from PHPUnit tests');
        }
        self::$instance = null;
    }
}
