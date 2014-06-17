<?php

namespace local_coursedeletion;

class event_base extends \core\event\base {
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_coursedeletion';
    }

    public function get_url() {
        return new \moodle_url('/local/coursedeletion/coursesettings.php', array('id' => $this->courseid));
    }

    public function get_legacy_logdata() {
        // parameters passed to add_to_log: $courseid, $module, $action, $url, $info, $cm, $user;
        // determine the action from the classname (without namespace
        $action = join('', array_slice(explode('\\', get_class($this)), -1));
        return array($this->courseid, 'coursedeletion', $action,
            $this->get_url(), $this->get_description(), 0, 0);
    }
}