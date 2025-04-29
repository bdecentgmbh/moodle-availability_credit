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
 * Prints a particular instance of credit
 *
 * @package    availability_credit
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/enrol/credit/lib.php');
$pay = optional_param('pay', 0, PARAM_BOOL);

$contextid = required_param('contextid', PARAM_INT);
$id = required_param('id', PARAM_INT);

$context = \context::instance_by_id($contextid);
$availability = [];

if ($context instanceof \context_module) {
    $moduleavailability = $DB->get_field('course_modules', 'availability', ['id' => $id], MUST_EXIST);
    $availability = json_decode($moduleavailability);
} else {
    $sectionsavailability = $DB->get_field('course_sections', 'availability', ['id' => $id], MUST_EXIST);
    $availability = json_decode($sectionsavailability);
}

foreach ($availability->c as $condition) {
    if (isset($condition->type)) {
        if ($condition->type === 'credit') {
            $credit = $condition;
            break;
        }
    } else { // This may be a restriction set.
        foreach ($condition->c as $innercondition) {
            if ($innercondition->type === 'credit') {
                $credit = $innercondition;
                break;
            }
        }
    }
}

if (!isset($credit)) {
    throw new \moodle_exception('No credit condition for this context.');
}

$coursecontext = $context->get_course_context();
$course = $DB->get_record('course', ['id' => $coursecontext->instanceid]);

require_login($course);

if ($paymenttnx = $DB->get_record('availability_credit_tnx', ['userid' => $USER->id, 'contextid' => $contextid])) {
    redirect($context->get_url(), get_string('paymentcompleted', 'availability_credit'));
}

$PAGE->set_url('/availability/condition/credit/view.php', ['contextid' => $contextid, 'id' => $id]);
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

if (!empty($credit->cost)) {
    // Calculate localised and "." cost, make sure we send Credit the same value,
    // please note Credit expects amount with 2 decimal places and "." separator.
    $localisedcost = format_float($credit->cost, 2, true);
    $cost = (int) $credit->cost;

    if (isguestuser()) { // Force login only for guest user, not real users with guest role.
        if (empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
        } else {
            // This actually is not so secure ;-), 'cause we're in unencrypted connection...
            $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        }
        $loginurl = new \moodle_url('/login/index.php');
        echo $OUTPUT->header();
        echo '<div class="mdl-align"><p>'.get_string('paymentrequired', 'availability_credit').'</p>';
        echo '<div class="mdl-align"><p>'.get_string('paymentwaitremider', 'availability_credit').'</p>';
        echo '<p><a href="'.$loginurl.'">'.get_string('loginsite').'</a></p>';
        echo '</div>';
        // Finish the page.
        echo $OUTPUT->footer();
        die;
    } else {
        $usercredits = enrol_credit_plugin::get_user_credits($USER->id);
        // Can they afford it?
        if ($cost > $usercredits) {
            echo $OUTPUT->header();
            notice(
            get_string('insufficient_credits', 'enrol_credit', [
            'credit_cost' => $cost,
            'user_credits' => $usercredits]),
            new \moodle_url('/course/view.php', ['id' => $course->id]));
            // Finish the page.
            echo $OUTPUT->footer();
        }
        // Check if we are paying for this.
        if (!empty($pay) && confirm_sesskey()) {

            if ($context instanceof \context_module) {
                // Process the payment.
                $DB->insert_record('availability_credit_tnx',
                ['userid' => $USER->id, 'contextid' => $contextid, 'timeupdated' => time()]);
            } else {
                // Process the payment.
                $DB->insert_record('availability_credit_tnx',
                    ['userid' => $USER->id, 'contextid' => $id, 'timeupdated' => time()]);
            }

            enrol_credit_plugin::deduct_credits($USER->id, $cost);
            redirect($context->get_url(), get_string('paymentcompleted', 'availability_credit', $usercredits - $cost));
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->confirm(
                          get_string('checkout', 'enrol_credit', [
                                     'credit_cost' => $cost,
                                     'user_credits' => $usercredits]),
                                      new \moodle_url('/availability/condition/credit/view.php',
                                      ['contextid' => $contextid, 'id' => $id, 'pay' => true]),
                                      $context->get_url());
            // Finish the page.
            echo $OUTPUT->footer();
        }
    }
}
