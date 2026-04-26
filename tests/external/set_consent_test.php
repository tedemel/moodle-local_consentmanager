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
 * Unit tests for the set_consent external API.
 *
 * @package    local_consentmanager
 * @copyright  2026 Tessa Demel
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\external;

use core_external\external_api;
use local_consentmanager\local\consent_manager;
use local_consentmanager\local\consent_record;

/**
 * Unit tests for the set_consent and withdraw_consent external functions.
 *
 * @covers \local_consentmanager\external\set_consent
 * @covers \local_consentmanager\external\withdraw_consent
 */
final class set_consent_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        consent_manager::reset_for_testing();
        set_config('enabled', 1, 'local_consentmanager');
        set_config('revision', 1, 'local_consentmanager');
    }

    /**
     * Resolve a category shortname to its database id.
     *
     * @param string $shortname Category shortname.
     * @return int Category id.
     */
    private function catid(string $shortname): int {
        return consent_manager::instance()->get_category_by_shortname($shortname)->id;
    }

    public function test_set_consent_logged_in_user(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $marketingid = $this->catid('marketing');

        $result = external_api::call_external_function('local_consentmanager_set_consent', [
            'catids'    => [$marketingid],
            'acceptall' => false,
        ]);

        $this->assertFalse($result['error']);
        $this->assertTrue($result['data']['success']);
        $this->assertTrue($DB->record_exists('local_consentmanager_consents', [
            'userid' => $user->id,
            'catid'  => $marketingid,
            'status' => consent_record::STATUS_GIVEN,
        ]));
    }

    public function test_set_consent_acceptall(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = external_api::call_external_function('local_consentmanager_set_consent', [
            'catids'    => [],
            'acceptall' => true,
        ]);

        $this->assertFalse($result['error']);
        $allcatids = array_keys(consent_manager::instance()->get_categories());
        foreach ($allcatids as $catid) {
            $this->assertTrue($DB->record_exists('local_consentmanager_consents', [
                'userid' => $user->id,
                'catid'  => $catid,
                'status' => consent_record::STATUS_GIVEN,
            ]));
        }
    }

    public function test_set_consent_guest_with_token(): void {
        global $DB;

        $token = str_repeat('b', 64);
        $marketingid = $this->catid('marketing');

        $result = external_api::call_external_function('local_consentmanager_set_consent', [
            'catids'     => [$marketingid],
            'acceptall'  => false,
            'guesttoken' => $token,
        ]);

        $this->assertFalse($result['error']);
        $this->assertTrue($DB->record_exists('local_consentmanager_consents', [
            'guesttoken' => $token,
            'catid'      => $marketingid,
        ]));
    }

    public function test_set_consent_rejects_non_alphanum_token(): void {
        $marketingid = $this->catid('marketing');

        $result = external_api::call_external_function('local_consentmanager_set_consent', [
            'catids'     => [$marketingid],
            'acceptall'  => false,
            'guesttoken' => 'has-invalid-chars!',
        ]);

        $this->assertTrue($result['error']);
    }

    public function test_withdraw_consent_logged_in_user(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $marketingid = $this->catid('marketing');

        consent_manager::instance()->store_consent([$marketingid]);
        consent_manager::reset_for_testing();

        $result = external_api::call_external_function('local_consentmanager_withdraw_consent', [
            'catid' => $marketingid,
        ]);

        $this->assertFalse($result['error']);
        $this->assertTrue($result['data']['success']);
        $rec = $DB->get_record('local_consentmanager_consents', [
            'userid' => $user->id,
            'catid'  => $marketingid,
        ]);
        $this->assertEquals(consent_record::STATUS_WITHDRAWN, (int)$rec->status);
    }

    public function test_withdraw_required_category_returns_error(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $essentialid = $this->catid('essential');

        $result = external_api::call_external_function('local_consentmanager_withdraw_consent', [
            'catid' => $essentialid,
        ]);

        $this->assertFalse($result['error']);
        $this->assertFalse($result['data']['success']);
        $this->assertNotEmpty($result['data']['message']);
    }
}
