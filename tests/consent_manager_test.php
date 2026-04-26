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
 * Unit tests for the central consent_manager service.
 *
 * @package    local_consentmanager
 * @copyright  2026 Tessa Demel
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager;

use local_consentmanager\local\consent_manager;
use local_consentmanager\local\consent_record;

/**
 * Unit tests for the consent_manager service.
 *
 * @covers \local_consentmanager\local\consent_manager
 */
final class consent_manager_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        consent_manager::reset_for_testing();
        set_config('enabled', 1, 'local_consentmanager');
        set_config('revision', 1, 'local_consentmanager');
    }

    /**
     * Helper: shortname → category id from the default install.
     *
     * @param string $shortname Category shortname.
     * @return int Category id.
     */
    private function catid(string $shortname): int {
        $cat = consent_manager::instance()->get_category_by_shortname($shortname);
        $this->assertNotNull($cat, "Category '$shortname' missing");
        return $cat->id;
    }

    public function test_default_categories_installed(): void {
        $cats = consent_manager::instance()->get_categories();
        $shortnames = array_map(fn($c) => $c->shortname, $cats);
        $this->assertContains('essential', $shortnames);
        $this->assertContains('functional', $shortnames);
        $this->assertContains('statistics', $shortnames);
        $this->assertContains('marketing', $shortnames);
    }

    public function test_essential_category_is_required(): void {
        $cat = consent_manager::instance()->get_category_by_shortname('essential');
        $this->assertTrue($cat->required);
    }

    public function test_essential_consent_is_implicit(): void {
        $manager = consent_manager::instance();
        $this->assertTrue($manager->has_consent($this->catid('essential')));
    }

    public function test_required_category_cannot_be_withdrawn(): void {
        $manager = consent_manager::instance();
        $this->setAdminUser();
        $this->expectException(\moodle_exception::class);
        $manager->withdraw_consent($this->catid('essential'));
    }

    public function test_logged_in_user_store_and_read_consent(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');

        $this->assertFalse($manager->has_consent($marketingid));
        $this->assertTrue($manager->needs_consent());

        $manager->store_consent([$marketingid]);
        consent_manager::reset_for_testing();

        $this->assertTrue(consent_manager::instance()->has_consent($marketingid));
        $this->assertFalse(consent_manager::instance()->needs_consent());
    }

    public function test_accept_all_grants_every_category(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $manager->store_consent([], true);
        consent_manager::reset_for_testing();

        $consents = consent_manager::instance()->get_user_consents();
        foreach (consent_manager::instance()->get_categories() as $cat) {
            $this->assertTrue(
                $consents[$cat->id] ?? false,
                "Expected consent for '{$cat->shortname}' after accept-all"
            );
        }
    }

    public function test_revision_bump_re_prompts(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $manager->store_consent([], true);
        consent_manager::reset_for_testing();
        $this->assertFalse(consent_manager::instance()->needs_consent());

        // Admin bumps revision.
        set_config('revision', 2, 'local_consentmanager');
        consent_manager::reset_for_testing();

        $this->assertTrue(consent_manager::instance()->needs_consent());
    }

    public function test_withdraw_consent_clears_status(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');
        $manager->store_consent([$marketingid]);
        consent_manager::reset_for_testing();

        consent_manager::instance()->withdraw_consent($marketingid);
        consent_manager::reset_for_testing();

        $this->assertFalse(consent_manager::instance()->has_consent($marketingid));
    }

    public function test_guest_consent_via_provided_token(): void {
        global $DB;

        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');
        $token = str_repeat('a', 64);

        $manager->store_consent([$marketingid], false, $token);

        $rec = $DB->get_record('local_consentmanager_consents', [
            'guesttoken' => $token,
            'catid'      => $marketingid,
        ]);
        $this->assertNotFalse($rec);
        $this->assertEquals(consent_record::STATUS_GIVEN, (int)$rec->status);
        $this->assertNull($rec->userid);
    }

    public function test_audit_log_entry_written(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');
        $manager->store_consent([$marketingid]);

        $log = $DB->get_records('local_consentmanager_log', [
            'userid' => $user->id,
            'catid'  => $marketingid,
            'action' => 'given',
        ]);
        $this->assertCount(1, $log);
    }

    public function test_user_deletion_anonymises_log_and_clears_consents(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');
        $manager->store_consent([$marketingid]);

        // Trigger user deletion observer directly.
        $event = \core\event\user_deleted::create([
            'objectid' => $user->id,
            'context'  => \context_user::instance($user->id),
            'other'    => [
                'username'   => $user->username,
                'email'      => $user->email,
                'idnumber'   => $user->idnumber,
                'picture'    => $user->picture,
                'mnethostid' => $user->mnethostid,
            ],
        ]);
        consent_manager::on_user_deleted($event);

        $this->assertEquals(0, $DB->count_records('local_consentmanager_consents', [
            'userid' => $user->id,
        ]));
        $this->assertEquals(0, $DB->count_records('local_consentmanager_log', [
            'userid' => $user->id,
        ]));
    }

    public function test_iframe_replacement_blocks_unconsented(): void {
        $manager = consent_manager::instance();

        // Enable YouTube service (created disabled by install.php).
        global $DB;
        $DB->set_field('local_consentmanager_services', 'enabled', 1, ['name' => 'YouTube']);
        consent_manager::reset_for_testing();

        $context = \context_system::instance();
        $html = '<iframe src="https://www.youtube.com/embed/abc"></iframe>';

        $out = consent_manager::instance()->replace_unconsented_iframes($html, $context);

        // The live <iframe> tag must be replaced; data-src on the placeholder
        // is intentional (click-to-load needs it).
        $this->assertStringNotContainsString('<iframe', $out);
        $this->assertStringContainsString('consentmanager-unlock-iframe', $out);
    }

    public function test_iframe_replacement_passes_through_unknown_src(): void {
        $manager = consent_manager::instance();
        $context = \context_system::instance();
        $html = '<iframe src="https://example.org/trusted"></iframe>';

        $out = $manager->replace_unconsented_iframes($html, $context);

        $this->assertEquals($html, $out);
    }

    public function test_iframe_replacement_no_iframes_short_circuits(): void {
        $manager = consent_manager::instance();
        $context = \context_system::instance();
        $html = '<p>Just text. No iframes here.</p>';

        $this->assertEquals($html, $manager->replace_unconsented_iframes($html, $context));
    }

    public function test_invalid_token_format_rejected(): void {
        $manager = consent_manager::instance();
        $marketingid = $this->catid('marketing');

        // Inject a syntactically invalid token.
        $_COOKIE['cm_guesttoken'] = 'not-a-hex-token';

        // Get_user_consents should fall back to "no consent" for guests.
        $consents = $manager->get_user_consents();
        $this->assertFalse($consents[$marketingid] ?? false);

        unset($_COOKIE['cm_guesttoken']);
    }
}
