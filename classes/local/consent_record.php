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
 * Consent record entity.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\local;

/**
 * Represents the current consent state of a user/guest for one category.
 */
class consent_record {
    /** @var int Status: user actively declined this category. */
    public const STATUS_DECLINED   = 0;
    /** @var int Status: user has given consent for this category. */
    public const STATUS_GIVEN      = 1;
    /** @var int Status: user previously consented but withdrew consent. */
    public const STATUS_WITHDRAWN  = 2;

    /** @var int */
    public int $id;
    /** @var int|null */
    public ?int $userid;
    /** @var string|null */
    public ?string $guesttoken;
    /** @var int */
    public int $catid;
    /** @var int */
    public int $status;
    /** @var int */
    public int $revision;
    /** @var int */
    public int $timecreated;
    /** @var int */
    public int $timemodified;

    /**
     * Construct from a DB record.
     *
     * @param \stdClass $record
     */
    public function __construct(\stdClass $record) {
        $this->id           = (int)$record->id;
        $this->userid       = isset($record->userid) ? (int)$record->userid : null;
        $this->guesttoken   = $record->guesttoken ?? null;
        $this->catid        = (int)$record->catid;
        $this->status       = (int)$record->status;
        $this->revision     = (int)$record->revision;
        $this->timecreated  = (int)($record->timecreated ?? 0);
        $this->timemodified = (int)($record->timemodified ?? 0);
    }

    /**
     * Whether consent has been actively given.
     *
     * @return bool
     */
    public function is_given(): bool {
        return $this->status === self::STATUS_GIVEN;
    }
}
