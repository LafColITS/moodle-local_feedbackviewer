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
 * Output display.
 *
 * @package   local_feedbackviewer
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_feedbackviewer;

defined('MOODLE_INTERNAL') || die();

/**
 * Output display. This overrides various parts of mod_feedback_responses_table
 * and table_sql in order to display key:value pairs with filtering enabled.
 *
 * @package   local_feedbackviewer
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class display extends \mod_feedback_responses_table {
    /** @var int $userid The userid whose responses are displayed. */
    protected $userid;

    /**
     * Constructor for the responses table.
     *
     * @param mod_feedback_structure $feedbackstructure the feedback
     * @param int $userid The individual user id
     */
    public function __construct($feedbackstructure, $userid) {
        $this->userid = $userid;
        parent::__construct($feedbackstructure);
    }

    /**
     * Overrides parent to narrow the responses to the individual user.
     *
     * @param int $group retrieve only users from this group (optional)
     */
    protected function init($group=0) {
        parent::init($group);
        $this->sql->where .= 'AND u.id = :userid';
        $this->sql->params['userid'] = $this->userid;
    }

    /**
     * Build the response table. Echos the output.
     *
     * @return void
     */
    public function build_table() {
        $headers = array();
        foreach ($this->feedbackstructure->get_items() as $id => $item) {
            if ($item->typ == 'label') {
                $headers[$id] = \html_writer::tag('div', $item->presentation, array('class' => 'fitemlabel'));
            }
            else {
                $headers[$id] = $item->name;
            }
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

    /**
     * Invokes out(); skips all the stuff in the parent.
     */
    public function display() {
        $this->out(1, false, '');
    }

    /**
     * Inherited from class flexible_table. Nothing to do here.
     *
     * @param bool $closeexportclassdoc Included for compatibility and unused.
     * @return string empty string
     */
    public function finish_output($closeexportclassdoc = true) {
        return '';
    }

    /**
     * Build the table and output it.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @param string $downloadhelpbutton
     */
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
