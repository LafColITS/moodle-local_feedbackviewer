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
 * Output display. This overrides various parts of mod_feedback_responses_table
 * and table_sql in order to display key:value pairs with filtering enabled.
 *
 * @package   local_feedbackviewer
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_feedbackviewer;

defined('MOODLE_INTERNAL') || die();

class display extends \mod_feedback_responses_table {
    protected $userid;

    public function __construct($feedbackstructure, $userid) {
        $this->userid = $userid;
        parent::__construct($feedbackstructure);
    }

    // Overridden to narrow on the given user.
    protected function init($group=0) {
        parent::init($group);
        $this->sql->where .= 'AND u.id = :userid';
        $this->sql->params['userid'] = $this->userid;
    }

    public function build_table() {
        $headers = array();
        foreach ($this->feedbackstructure->get_items() as $id => $item) {
            $headers[$id] = $item->name;
        }

        foreach ($this->rawdata as $row) {
            $formattedrow = $this->format_row($row);
        }

        echo \html_writer::start_tag('div', array('class' => 'mform'));
        foreach ($headers as $id => $header) {
            echo \html_writer::start_tag('div', array('class' => 'fitem'));
            echo \html_writer::tag('div', $header, array('class' => 'fitemtitle'));
            echo \html_writer::tag('div', format_text($formattedrow["val$id"]), array('class' => 'felement fstatic'));
            echo \html_writer::end_tag('div');
        }
        echo \html_writer::end_tag('div');
    }

    public function display() {
        $this->out(1, false, '');
    }

    public function finish_output($closeexportclassdoc = true) {
        return '';
    }

    public function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $DB;

        $this->add_all_values_to_output();
        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from}
                    WHERE {$this->sql->where}", $this->sql->params);
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->finish_output();
    }
}
