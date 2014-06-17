<?php

namespace local_coursedeletion\event;

use local_coursedeletion\event_base as base;

defined('MOODLE_INTERNAL') || die();

class settings_update extends base {
    protected function init() {
        parent::init();
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
    }

    public static function get_name() {
        return get_string('settings_update', 'local_coursedeletion');
    }

    public function get_description() {
        $info = isset($this->other['detail']) ? ' ' . $this->other['detail'] : '';
        return "Course {$this->courseid} updated." . $info;
    }
}