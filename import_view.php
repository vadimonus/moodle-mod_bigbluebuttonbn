<?php
/**
 * View for BigBlueButton interaction  
 *
 * @package   mod_bigbluebuttonbn
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2015 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT);  // course_module ID, or
$bn = optional_param('bn', 0, PARAM_INT);  // bigbluebuttonbn instance ID

if ($id) {
    $cm = get_coursemodule_from_id('bigbluebuttonbn', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bigbluebuttonbn = $DB->get_record('bigbluebuttonbn', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($bn) {
    $bigbluebuttonbn = $DB->get_record('bigbluebuttonbn', array('id' => $bn), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $bigbluebuttonbn->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebuttonbn->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or a BigBlueButtonBN instance ID');
}

$context = bigbluebuttonbn_get_context_module($cm->id);

if ( isset($SESSION) && isset($SESSION->bigbluebuttonbn_bbbsession)) {
    require_login($course, true, $cm);
    $bbbsession = $SESSION->bigbluebuttonbn_bbbsession;
}

/// Print the page header
$PAGE->set_context($context);
$PAGE->set_url('/mod/bigbluebuttonbn/import_view.php', array('id' => $cm->id, 'bigbluebuttonbn' => $bigbluebuttonbn->id));
$PAGE->set_title(format_string($bigbluebuttonbn->name));
$PAGE->set_cacheable(false);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

// Create view object which collects all the information the renderer will need.
//$viewobj = new mod_bigbluebuttonbn_view_object();

$output = $PAGE->get_renderer('mod_bigbluebuttonbn');

echo $OUTPUT->header();

//echo $output->view_page($course, $bigbluebuttonbn, $cm, $context, $viewobj);

$output = '';

$output .= '<h4>Import recording links</h4>';

$options = bigbluebuttonbn_import_get_courses_for_select($bbbsession);
$name = 'import_recording_links_select';
$selected = array_keys($options)[0];
$attributes = null;
$output .= html_writer::start_tag('div');
//$output .= html_writer::select($options, $name, $selected, true, $attributes);
$output .= html_writer::select($options, $name, $selected, true);
$output .= html_writer::end_tag('div');
$output .= html_writer::tag('span', '', ['id' => 'import_recording_links_table' ,'name'=>'import_recording_links_table']);

$recordings = bigbluebuttonbn_getRecordingsArrayByCourse($selected, $bbbsession['endpoint'], $bbbsession['shared_secret']);
$output .= bigbluebutton_output_recording_table($bbbsession, $recordings);

echo $output;


echo $OUTPUT->footer();