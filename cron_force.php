<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login();

$can_access = false;
if (isset($CFG->local_coursedeletion_force_users)) {
    $userids = explode(',', clean_param($CFG->local_coursedeletion_force_users, PARAM_SEQUENCE));
    $can_access = in_array($USER->id, $userids);
}
if (!$can_access) {
    send_file_not_found();
    exit;
}

$task = new local_coursedeletion\task\workflow();
$run_even_if_already_ran_today = true;
$verbose = true;
echo '<pre>';
$task->execute($run_even_if_already_ran_today, $verbose);
