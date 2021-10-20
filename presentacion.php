<?php 

require_once(__DIR__ . '/../../config.php');
global $DB, $CFG;

require_login();

$context = context_system::instance();
$PAGE->set_url(new moodle_url('/local/academy/presentacion.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('SAP ACADEMY');
$PAGE->set_heading('SAP ACADEMY');


$templateContext = (object)[
    'sesskey' => sesskey(),
    'url' => $CFG->wwwroot
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_academy/presentacion', $templateContext);
echo $OUTPUT->footer();