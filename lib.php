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
 * Library functions for the feedback viewer.
 *
 * @package   local_feedbackviewer
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Extends core navigation to display the feedbackviewer link in the course administration.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course The course object
 * @param context         $context The course context
 */
function local_feedbackviewer_extend_navigation_course($navigation, $course, $context) {

    if (!has_capability('local/feedbackviewer:view', $context) &&
        !has_capability('local/feedbackviewer:viewmyfeedback', $context)) {
        return true;
    }

    $feedback = $navigation->add(get_string('pluginname', 'local_feedbackviewer'));

    if (has_capability('local/feedbackviewer:view', $context)) {
        $url = new moodle_url('/local/feedbackviewer/index.php', array('id' => $course->id));
        $feedback->add(get_string('all', 'local_feedbackviewer'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
    if (has_capability('local/feedbackviewer:viewmyfeedback', $context)) {
        $url = new moodle_url('/local/feedbackviewer/my.php', array('id' => $course->id));
        $feedback->add(get_string('my', 'local_feedbackviewer'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
