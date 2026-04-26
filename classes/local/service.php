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
 * Third-party service entity.
 *
 * @package   local_consentmanager
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_consentmanager\local;

/**
 * Represents a third-party service (e.g. YouTube, Matomo) in the registry.
 */
class service {
    /** @var int */
    public int $id;
    /** @var int Category FK */
    public int $catid;
    /** @var string */
    public string $name;
    /** @var string */
    public string $provider;
    /** @var string */
    public string $privacyurl;
    /** @var string */
    public string $description;
    /** @var int */
    public int $descriptionformat;
    /** @var string[] Domain regexes (one per line in DB) */
    public array $domainpatterns;
    /** @var bool */
    public bool $enabled;
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
        $this->catid             = (int)$record->catid;
        $this->name              = $record->name;
        $this->provider          = $record->provider ?? '';
        $this->privacyurl        = $record->privacyurl ?? '';
        $this->description       = $record->description ?? '';
        $this->descriptionformat = (int)($record->descriptionformat ?? FORMAT_HTML);
        $this->domainpatterns    = $record->domainpatterns
            ? array_filter(array_map('trim', explode("\n", $record->domainpatterns)))
            : [];
        $this->enabled           = (bool)($record->enabled ?? true);
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
        $rec->catid             = $this->catid;
        $rec->name              = $this->name;
        $rec->provider          = $this->provider;
        $rec->privacyurl        = $this->privacyurl;
        $rec->description       = $this->description;
        $rec->descriptionformat = $this->descriptionformat;
        $rec->domainpatterns    = implode("\n", $this->domainpatterns);
        $rec->enabled           = (int)$this->enabled;
        $rec->timecreated       = $this->timecreated ?: time();
        $rec->timemodified      = time();
        return $rec;
    }

    /**
     * Return true if the given iframe src URL matches any domain pattern.
     *
     * @param string $src
     * @return bool
     */
    public function matches_src(string $src): bool {
        foreach ($this->domainpatterns as $pattern) {
            if (@preg_match('/' . $pattern . '/i', $src)) {
                return true;
            }
        }
        return false;
    }
}
