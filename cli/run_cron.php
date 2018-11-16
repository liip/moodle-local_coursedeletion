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

define('CLI_SCRIPT', true);

require('../../../config.php');
require_once($CFG->libdir . '/clilib.php');

/**
Run the task that is run
 */
function run_cron($runevenifalreadyrantoday=true, $verbose=true) {
    $task = new local_coursedeletion\task\workflow();
    $task->execute($runevenifalreadyrantoday, $verbose);
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

$runevenifalreadyrantoday = !$options['noforcerun'];
$verbose = intval(!$options['noverbose']);

run_cron($runevenifalreadyrantoday, $verbose);
