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
 * Consent category entity.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\local;

/**
 * Represents a single consent category (e.g. essential, marketing).
 */
class category {
    /** @var int */
    public int $id;
    /** @var string */
    public string $shortname;
    /** @var string */
    public string $name;
    /** @var string */
    public string $description;
    /** @var int */
    public int $descriptionformat;
    /** @var bool */
    public bool $required;
    /** @var int */
    public int $sortorder;
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
        $this->id                = (int)$record->id;
        $this->shortname         = $record->shortname;
        $this->name              = $record->name;
        $this->description       = $record->description ?? '';
        $this->descriptionformat = (int)($record->descriptionformat ?? FORMAT_HTML);
        $this->required          = (bool)($record->required ?? false);
        $this->sortorder         = (int)($record->sortorder ?? 0);
        $this->timecreated       = (int)($record->timecreated ?? 0);
        $this->timemodified      = (int)($record->timemodified ?? 0);
    }

    /**
     * Convert to a plain stdClass suitable for DB insert/update.
     *
     * @return \stdClass
     */
    public function to_record(): \stdClass {
        $rec = new \stdClass();
        if (!empty($this->id)) {
            $rec->id = $this->id;
        }
        $rec->shortname         = $this->shortname;
        $rec->name              = $this->name;
        $rec->description       = $this->description;
        $rec->descriptionformat = $this->descriptionformat;
        $rec->required          = (int)$this->required;
        $rec->sortorder         = $this->sortorder;
        $rec->timecreated       = $this->timecreated ?: time();
        $rec->timemodified      = time();
        return $rec;
    }
}
