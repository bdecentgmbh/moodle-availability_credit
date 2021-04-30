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
 * Credit condition.
 *
 * @package availability_credit
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @developed by 2020 Derick Turner derick@e-learndesign.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_credit;

defined('MOODLE_INTERNAL') || die();

/**
 * Credit condition.
 *
 * @package availability_credit
 */
class condition extends \core_availability\condition {

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        if (isset($structure->cost)) {
            $this->cost = $structure->cost;
        }
    }

    /**
     * Returns info to be saved.
     * @return stdClass
     */
    public function save() {
        $result = (object)array('type' => 'credit');
        if ($this->cost) {
            $result->cost = $this->cost;
        }
        return $result;
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $businessemail The email of credit to be credited
     * @param string $currency      The currency to charge the user
     * @param string $cost          The cost to charge the user
     * @return stdClass Object representing condition
     */
    public static function get_json($businessemail, $currency, $cost) {
        return (object)array('type' => 'credit', 'cost' => $cost);
    }

    /**
     * Returns true if the user can access the context, false otherwise
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;
        // Should double-check with credit everytime ?
        $context = $info->get_context();
        $allow = $DB->record_exists('availability_credit_tnx',
                                  array('userid' => $userid,
                                        'contextid' => $context->id));
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not  True if NOT is in force
     * @param \core_availability\info $info Information about the availability condition and module context
     * @return string    The string about the condition and it's status
     */
    public function get_description($full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, false, $info);
    }
    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not        True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     * @param bool $info       Information about the availability condition and module context
     * @return string          The string about the condition and it's status
     */
    protected function get_either_description($not, $standalone, $info) {
        $context = $info->get_context();
        $url = new \moodle_url('/availability/condition/credit/view.php?contextid='.$context->id);
        if ($not) {
            return get_string('notdescription', 'availability_credit', $url->out());
        } else {
            return get_string('eitherdescription', 'availability_credit', $url->out());
        }
    }

    /**
     * Function used by backup restore
     *
     * @param int $restoreid
     * @param int $courseid
     * @param \base_logger $logger
     * @param string $name
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        // Update the date, if restoring with changed date.
        $dateoffset = \core_availability\info::get_restore_date_offset($restoreid);
        if ($dateoffset) {
            $this->time += $dateoffset;
            return true;
        }
        return false;
    }

    /**
     * Returns a string to debug
     * @return string
     */
    protected function get_debug_string() {
        return gmdate('Y-m-d H:i:s');
    }
}
