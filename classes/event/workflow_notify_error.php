<?php

namespace local_coursedeletion\event;

use local_coursedeletion\event_base as base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class workflow_notify_error
 * @package local_coursedeletion\event
 *
 * create() param $other: array(
 *                  'courseid' => int courseid
 *                  'detail' => 'additional info'
 *              )
 *
 */
class workflow_notify_error extends base {
    protected function init() {
        parent::init();
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['objecttable'] = 'course';
        $this->context = \context_system::instance();

    }

    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->other['courseid']));
    }

    public static function get_name() {
        return get_string('workflow_notify_error', 'local_coursedeletion');
    }

    public function get_description() {
        $info = isset($this->other['detail']) ? ' ' . $this->other['detail'] : '';
        return "Course {$this->courseid} workflow mail send failed." . $info;
    }

    public function get_legacy_logdata() {
        // parameters passed to add_to_log: $courseid, $module, $action, $url, $info, $cm, $user;
        // determine the action from the classname (without namespace
        $action = join('', array_slice(explode('\\', get_class($this)), -1));
        return array(0, 'coursedeletion', $action,
            $this->get_url(), $this->get_description(), 0, 0);
    }

}