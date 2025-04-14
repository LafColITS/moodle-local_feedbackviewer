<?php
// This file is part of Moodle - http://moodle.org/
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
 * Display all feedback results from a course for a given user.
 *
 * @package   local_feedbackviewer
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id     = required_param('id', PARAM_INT);
$uid    = optional_param('uid', 0, PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_login($course);

// Setup page.
$PAGE->set_url('/local/feedbackviewer/index.php', ['id' => $id]);
$PAGE->set_pagelayout('report');
$returnurl = new moodle_url('/course/view.php', ['id' => $id]);

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('local/feedbackviewer:view', $coursecontext);

// Get users.
$users = local_feedbackviewer\report::get_all_users($coursecontext);

// Finish setting up page.
$PAGE->set_title($course->shortname .': '. get_string('feedback'));
$PAGE->set_heading($course->fullname);

// Display to the user.
echo $OUTPUT->header();
$select = new single_select($PAGE->url, 'uid', $users, $uid);
$select->label = get_string('user');
echo html_writer::tag('div', $OUTPUT->render($select));

// Display report if a user is selected.
if (!empty($uid)) {
    local_feedbackviewer\report::build_report($course, $uid);
}

// Finish.
echo $OUTPUT->footer();
