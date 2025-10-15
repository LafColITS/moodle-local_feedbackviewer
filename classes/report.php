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
        global $DB, $USER;

        // Set user fields.
        $userfields = \core_user\fields::for_userpic();
        $selects    = $userfields->get_sql('u', false, '', 'id', false)->selects;
        $selects    = str_replace(', ', ',', $selects);

        $users = [];
        $userlist = get_enrolled_users($coursecontext, '', 0, $selects);

        $course = $DB->get_record('course', ['id' => $coursecontext->instanceid], '*', MUST_EXIST);

        if (
            $course->groupmode == SEPARATEGROUPS
            && !has_capability('moodle/site:accessallgroups', $coursecontext)
        ) {
            $userlist = [];
            $groupids = array_keys(groups_get_all_groups($course->id, $USER->id));
            foreach ($groupids as $groupid) {
                $groupusers = get_enrolled_users($coursecontext, '', $groupid, $selects);

                // Go over group users and save new ones into final list.
                foreach ($groupusers as $groupuser) {
                    if (!array_key_exists($groupuser->id, $userlist)) {
                        $userlist[$groupuser->id] = $groupuser;
                    }
                }
            }
        }

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

        $visibleusers = self::get_all_users(\context_course::instance($course->id));
        if (!array_key_exists($uid, $visibleusers)) {
            return;
        }

        $feedbacks = get_all_instances_in_course('feedback', $course);
        foreach ($feedbacks as $feedback) {
            $feedbackmodule = get_coursemodule_from_instance('feedback', $feedback->id);
            if (!has_capability('mod/feedback:complete', \context_module::instance($feedbackmodule->id), $uid)) {
                continue;
            }

            $params = ['feedback' => $feedback->id,
                    'userid' => $uid,
                    'anonymous_response' => FEEDBACK_ANONYMOUS_NO];
            $feedbackcompleted = $DB->get_record('feedback_completed', $params);

            echo $OUTPUT->heading(format_string($feedback->name));
            if ($feedbackcompleted) {
                echo $OUTPUT->heading(userdate($feedbackcompleted->timemodified), 3);
            } else {
                echo $OUTPUT->heading(get_string('not_completed_yet', 'local_feedbackviewer'), 3);
                continue;
            }

            $feedbackstructure = new \mod_feedback_completion(
                $feedback,
                get_coursemodule_from_id(null, $feedback->coursemodule),
                0,
                true,
                $feedbackcompleted->id,
                $uid
            );
            $responsestable = new display($feedbackstructure, $uid);
            $responsestable->display();
        }
    }
}
