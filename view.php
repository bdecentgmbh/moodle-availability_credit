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
 * @developed by 2020 Derick Turner derick@e-learndesign.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/enrol/credit/lib.php');
$pay = optional_param('pay', 0, PARAM_BOOL);

$contextid = required_param('contextid', PARAM_INT);

$context = context::instance_by_id($contextid);
$instanceid = $context->instanceid;
if ($context instanceof context_module) {
    $availability = $DB->get_field('course_modules', 'availability', array('id' => $instanceid), MUST_EXIST);
    $availability = json_decode($availability);
    foreach ($availability->c as $condition) {
        if ($condition->type == 'credit') {
            // TODO: handle more than one credit for this context.
            $credit = $condition;
            break;
        } else {
            throw new moodle_exception('No credit condition for this context.');
        }
    }
} else {
    // TODO: handle sections.
    throw new moodle_exception('Support for sections not yet implemented.');
}
$coursecontext = $context->get_course_context();
$course = $DB->get_record('course', array('id' => $coursecontext->instanceid));

require_login($course);

if ($paymenttnx = $DB->get_record('availability_credit_tnx', array('userid' => $USER->id, 'contextid' => $contextid))) {
    redirect($context->get_url(), get_string('paymentcompleted', 'availability_credit'));
}

$PAGE->set_url('/availability/condition/credit/view.php', array('contextid' => $contextid));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);


if ($paymenttnx && ($paymenttnx->payment_status == 'Pending')) {
    echo get_string('paymentpending', 'availability_credit');
} else {

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
        echo $OUTPUT->header();
        echo '<div class="mdl-align"><p>'.get_string('paymentrequired', 'availability_credit').'</p>';
        echo '<div class="mdl-align"><p>'.get_string('paymentwaitremider', 'availability_credit').'</p>';
        echo '<p><a href="'.$wwwroot.'/login/">'.get_string('loginsite').'</a></p>';
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
            new moodle_url('/course/view.php', array('id' => $course->id)));
            // Finish the page.
            echo $OUTPUT->footer();
        }
        // Check if we are paying for this.
        if (!empty($pay) && confirm_sesskey()) {
            // Process the payment.
            $DB->insert_record('availability_credit_tnx',
                array('userid' => $USER->id, 'contextid' => $contextid, 'timeupdated' => time()));
            enrol_credit_plugin::deduct_credits($USER->id, $cost);
            redirect($context->get_url(), get_string('paymentcompleted', 'availability_credit', $usercredits - $cost));
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->confirm(
                          get_string('checkout', 'enrol_credit', [
                                     'credit_cost' => $cost,
                                     'user_credits' => $usercredits]),
                                      new moodle_url('/availability/condition/credit/view.php',
                                      array('contextid' => $contextid, 'pay' => true)),
                                      $context->get_url());
            // Finish the page.
            echo $OUTPUT->footer();
        }
    }
}
