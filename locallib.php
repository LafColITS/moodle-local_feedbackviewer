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
 * Local library functions for the feedbackviewer plugin.
 *
 * @package   local_feedbackviewer
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_feedbackviewer_get_all_users($coursecontext) {
    $users = array();
    $userlist = get_enrolled_users($coursecontext, '', 0, user_picture::fields('u', null, 0, 0, true));
    $suspended = get_suspended_userids($coursecontext);
    foreach ($userlist as $user) {
        if (!in_array($user->id, $suspended)) {
            $users[$user->id] = fullname($user);
        }
    }
    return $users;
}

function local_feedbackviewer_build_report($course, $uid) {
    global $DB, $OUTPUT;
    $modinfo = get_fast_modinfo($course);
    $modules = $modinfo->get_instances_of('feedback');
    foreach ($modules as $feedbackid => $cm) {
        $feedback = $DB->get_record('feedback', array('id' => $cm->instance));
        $feedbackitems = $DB->get_records('feedback_item', array('feedback' => $feedbackid), 'position');
        $params = array('feedback' => $feedbackid,
                'userid' => $uid,
                'anonymous_response' => FEEDBACK_ANONYMOUS_NO);
        $feedbackcompleted = $DB->get_record('feedback_completed', $params);

        if (is_array($feedbackitems)) {
            echo $OUTPUT->heading(format_string($feedback->name));
            if ($feedbackcompleted) {
                echo $OUTPUT->heading(userdate($feedbackcompleted->timemodified), 3);
            } else {
                echo $OUTPUT->heading(get_string('not_completed_yet', 'feedback'), 3);
                continue;
            }
            echo $OUTPUT->box_start('feedback_items');
            $itemnr = 0;
            foreach ($feedbackitems as $feedbackitem) {
                $params = array('completed' => $feedbackcompleted->id, 'item' => $feedbackitem->id);
                $value = $DB->get_record('feedback_value', $params);
                if ($feedbackitem->hasvalue == 1 && $feedback->autonumbering) {
                    $itemnr++;
                    echo $OUTPUT->box_start('feedback_item_number_left');
                    echo $itemnr;
                    echo $OUTPUT->box_end();
                }
                if ($feedbackitem->typ != 'pagebreak') {
                    echo $OUTPUT->box_start('box generalbox boxalign_left');
                    if (isset($value->value)) {
                        feedback_print_item_show_value($feedbackitem, $value->value);
                    } else {
                        feedback_print_item_show_value($feedbackitem, false);
                    }
                    echo $OUTPUT->box_end();
                }
            }
            echo $OUTPUT->box_end();
        }
    }
}
