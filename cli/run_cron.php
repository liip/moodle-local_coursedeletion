<?php

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

/**
Run the task that is run
 */
function run_cron($run_even_if_already_ran_today=true, $verbose=true) {
    $task = new local_coursedeletion\task\workflow();
    $task->execute($run_even_if_already_ran_today, $verbose);
}

// Get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'noforcerun'        => false,
        'noverbose'         => false,
        'help'              => false
    ),
    array(
        'h' => 'help',
        'F' => 'noforcerun',
        'V' => 'noverbose'
    )
);


if ($options['help']) {
    $help =
    "Runs the local_coursedeletion workflow task, which is usually called from the Moodle cron job.

Options:
-F, --noforcerun      Do not force the job to run.  If this option is given, the task will
					  use it's default behaviour (the one it uses when called from Moodle's
					  cron job) of only running once per day, no matter how many times it is
				      invoked.
-V, --noverbose       Do not be verbose.
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php local/coursedeletion/cli/run_cron.php -V
";
    echo $help;
    die;
}

$run_even_if_already_ran_today = !$options['noforcerun'];
$verbose = intval(!$options['noverbose']);

run_cron($run_even_if_already_ran_today, $verbose);
