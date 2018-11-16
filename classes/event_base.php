<?php
// This file is part of local/coursedeletion
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

/**
 * @package local/coursedeletion
 * @copyright 2014-2018 Liip AG <https://www.liip.ch/>
 * @author Brian King <brian.king@liip.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursedeletion;

defined('MOODLE_INTERNAL') || die();

class event_base extends \core\event\base {
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_coursedeletion';
    }

    public function get_url() {
        return new \moodle_url('/local/coursedeletion/coursesettings.php', array('id' => $this->courseid));
    }

    public function get_legacy_logdata() {
        // @codingStandardsIgnoreLine .
        // Parameters passed to add_to_log: $courseid, $module, $action, $url, $info, $cm, $user;
        // Determine the action from the classname (without namespace.
        $action = join('', array_slice(explode('\\', get_class($this)), -1));
        return array($this->courseid, 'coursedeletion', $action,
            $this->get_url(), $this->get_description(), 0, 0);
    }
}
