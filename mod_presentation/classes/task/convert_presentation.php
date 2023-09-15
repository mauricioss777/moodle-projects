<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_presentation\task;

use core\task\adhoc_task;

/**
 * Synchronise pending recordings from the server.
 *
 * @package   mod_presentation
 * @copyright 2023 Artur Henrique Welp <ahwelp@universo.univates.br>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class convert_presentation extends adhoc_task
{

    protected $cmid;

    /**
     * Get the name of the task for use in the interface.
     *
     * @return string
     */
    public function get_name(): string
    {
        return get_string('taskname:convert_presentation', 'mod_presentation');
    }

    /**
     * Summary of set_cm
     * @param mixed $cmid
     * @return void
     */
    public function set_cmid($cmid)
    {
        $this->cmid = $cmid;
    }

    /**
     * Run the conversion task
     */
    public function execute()
    {
        mtrace($this->cmid);
    }

}