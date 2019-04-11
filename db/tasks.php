<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_forceindividualemail\task\force_individual_email',
        'blocking'  => 0,

        // Run this task once an hour.
        'minute'    => '31',
        'hour'      => '*',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*',
    ),
);
