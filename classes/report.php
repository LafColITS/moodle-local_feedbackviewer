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
 * Build the report for display.
 *
 * @package   local_feedbackviewer
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_feedbackviewer;

defined('MOODLE_INTERNAL') || die();

/**
 * Build the report for display.
 *
 * @package   local_feedbackviewer
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report {

    /**
     * Return all the users in the course.
     *
     * @param object $coursecontext The course context.
     * @return array $users
     */
    public static function get_all_users($coursecontext) {
        $users = array();
        $userlist = get_enrolled_users($coursecontext, '', 0, \user_picture::fields('u', null, 0, 0, true));
        $suspended = get_suspended_userids($coursecontext);
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $coursecontext);
        foreach ($userlist as $user) {
            if (!in_array($user->id, $suspended)) {
                $users[$user->id] = fullname($user, $canviewfullnames);
            }
        }
        return $users;
    }

    /**
     * Build and display the report.
     *
     * @param object $course The course object.
     * @param integer $uid The user id.
     */
    public static function build_report($course, $uid) {
        global $DB, $OUTPUT;

        $feedbacks = get_all_instances_in_course('feedback', $course);
        foreach ($feedbacks as $feedback) {
            $params = array('feedback' => $feedback->id,
                    'userid' => $uid,
                    'anonymous_response' => FEEDBACK_ANONYMOUS_NO);
            $feedbackcompleted = $DB->get_record('feedback_completed', $params);

            echo $OUTPUT->heading(format_string($feedback->name));
            if ($feedbackcompleted) {
                echo $OUTPUT->heading(userdate($feedbackcompleted->timemodified), 3);
            } else {
                echo $OUTPUT->heading(get_string('not_completed_yet', 'local_feedbackviewer'), 3);
                continue;
            }

            $feedbackstructure = new \mod_feedback_completion($feedback, get_coursemodule_from_id(null, $feedback->coursemodule),
                0, true, $feedbackcompleted->id, $uid);
            $responsestable = new display($feedbackstructure, $uid);
            $responsestable->display();
        }
    }
}
