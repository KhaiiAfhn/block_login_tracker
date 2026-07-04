<?php
defined('MOODLE_INTERNAL') || die();
$capabilities = [
    'block/login_tracker:addinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => ['editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW]
    ],
    'block/login_tracker:myaddinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => ['user' => CAP_ALLOW] // Allows students to have it on their personal dashboard
    ]
];