<?php

namespace local_coursedeletion\event;

use local_coursedeletion\event_base as base;

defined('MOODLE_INTERNAL') || die();

class course_delete extends base {
    protected function init() {
        parent::init();
        $this->data['crud'] = 'd'; // c(reate), r(ead), u(pdate), d(elete)
    }

    public static function get_name() {
        return get_string('course_delete', 'local_coursedeletion');
    }

    public function get_description() {
        $info = isset($this->other['detail']) ? ' ' . $this->other['detail'] : '';
        return "Course {$this->courseid} deleted." . $info;
    }
}