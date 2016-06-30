<?php
namespace local_coursedeletion\task;

use \CourseDeletion;
use \set_config;
use \get_string;
use \mtrace;

class workflow extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
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
