<?php
#usage: phpunit --no-configuration --bootstrap=tests/bootstrap.php `pwd`/tests/CourseDeletionTest
define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/course/lib.php');

$CDTESTCONFIG = new stdClass;
$CDTESTCONFIG->category = 8; # category to use for creating temporary test courses.