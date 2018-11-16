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

namespace local_coursedeletion\task;

defined('MOODLE_INTERNAL') || die();

use \CourseDeletion;
use \set_config;
use \get_string;
use \mtrace;

class workflow extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('workflow_task_name', 'local_coursedeletion');
    }

    public function execute($forcerun = false, $verbose = 1) {
        require_once(__DIR__ . '/../../locallib.php');

        $now = time();
        if (!$lastrun = get_config('local_coursedeletion', 'local_lastcron')) {
            $lastrun = 0;
        }
        if (!$forcerun && date('Y-m-d', $now) == date('Y-m-d', $lastrun)) {
            mtrace('coursedeletion already ran today ... skipping');
            return;
        }
        set_config('local_lastcron', $now, 'local_coursedeletion');

        $cd = new CourseDeletion(null, $verbose);
        $cd->remove_records_for_missing_courses();
        $cd->reset_status_for_unstaged_courses();
        $cd->prenotify_users_of_staging();
        $cd->stage_courses_for_deletion();
        $cd->delete_courses();
    }
}
