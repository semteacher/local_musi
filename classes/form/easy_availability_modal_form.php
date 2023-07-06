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

namespace local_musi\form;

use context_module;
use mod_booking\booking_option;
use mod_booking\form\option_form;
use mod_booking\singleton_service;
use moodle_exception;
use stdClass;

/**
 * Modal form to allow simplified access to availability conditions for M:USI.
 *
 * @package     local_musi
 * @copyright   2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author      Bernhard Fischer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class easy_availability_modal_form extends \core_form\dynamic_form {

    protected function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     * {@inheritdoc}
     * @see moodleform::definition()
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('hidden', 'optionid');
        $mform->setType('optionid', PARAM_INT);

        $mform->addElement('hidden', 'formlocked');
        $mform->setType('formlocked', PARAM_BOOL);

        $optionid = $this->_ajaxformdata['optionid'];
        $formlocked = $this->_ajaxformdata['formlocked'];

        if ($formlocked) {
            // The form is locked because there are incompatible conditions.
            $mform->addElement('html', get_string('easyavailability:formlocked', 'local_musi'));
        } else {
            // The form is not locked and can be used normally.
            $mform->addElement('date_time_selector', 'bookingopeningtime', get_string('bookingopeningtime', 'mod_booking'));
            $mform->setType('bookingopeningtime', PARAM_INT);

            $mform->addElement('date_time_selector', 'bookingclosingtime', get_string('bookingclosingtime', 'mod_booking'));
            $mform->setType('bookingclosingtime', PARAM_INT);

            // Select users who can override booking_time condition.
            $mform->addElement('checkbox', 'selectuserscheckbox', get_string('easyavailability:selectusers', 'local_musi'));

            $mform->addElement('checkbox', 'selectusersoverbookcheckbox', get_string('easyavailability:overbook', 'local_musi'));
            $mform->hideIf('selectusersoverbookcheckbox', 'selectuserscheckbox', 'notchecked');

            $options = [
                'multiple' => true,
                'noselectionstring' => get_string('choose...', 'mod_booking'),
                'ajax' => 'local_shopping_cart/form_users_selector',
                'valuehtmlcallback' => function($value) {
                    global $OUTPUT;
                    $user = singleton_service::get_instance_of_user((int)$value);
                    if (!$user || !user_can_view_profile($user)) {
                        return false;
                    }
                    $details = user_get_user_details($user);
                    return $OUTPUT->render_from_template(
                            'local_shopping_cart/form-user-selector-suggestion', $details);
                }
            ];
            $mform->addElement('autocomplete', 'bo_cond_selectusers_userids',
                get_string('bo_cond_selectusers_userids', 'mod_booking'), [], $options);
            $mform->hideIf('bo_cond_selectusers_userids', 'selectuserscheckbox', 'notchecked');
        }
    }

    /**
     * Check access for dynamic submission.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {

        $context = $this->get_context_for_dynamic_submission();
        $optionid = $this->_ajaxformdata['optionid'];

        // The simplified availability menu.
        $alloweditavailability = (
            // Admin capability.
            has_capability('mod/booking:updatebooking', $context) ||
            // Or: Everyone with the M:USI editavailability capability.
            has_capability('local/musi:editavailability', $context) ||
            // Or: Teachers can edit the availability of their own option.
            (has_capability('mod/booking:limitededitownoption', $context) && $this->check_if_teacher($optionid))
        );
        if (!$alloweditavailability) {
            throw new moodle_exception('norighttoaccess', 'local_musi');
        }
    }


    public function set_data_for_dynamic_submission(): void {

        $data = new stdClass();

        /* If availability conditions are already in DB, we have to load them
        and translate them into the easy availability format.
        If the conditions in DB are somehow not compatible with the easy form,
        then we have to lock the form. */

        $data->optionid = $this->_ajaxformdata['optionid'];
        $data->formlocked = $this->_ajaxformdata['formlocked'];

        // Do nothing if the form is locked!
        if ($data->formlocked) {
            $this->set_data($data);
            return;
        }

        booking_option::purge_cache_for_option($data->optionid);
        $settings = singleton_service::get_instance_of_booking_option_settings($data->optionid);

        $data->bookingopeningtime = $settings->bookingopeningtime ?? $this->_ajaxformdata['bookingopeningtime'];
        $data->bookingclosingtime = $settings->bookingclosingtime ?? $this->_ajaxformdata['bookingclosingtime'];

        if (!empty($settings->availability)) {
            $availabilityarray = json_decode($settings->availability);
            foreach ($availabilityarray as $av) {
                switch ($av->id) {
                    case BO_COND_JSON_SELECTUSERS:
                        if (!empty($av->userids)) {
                            $data->selectuserscheckbox = true;
                            $data->bo_cond_selectusers_userids = $av->userids;
                        }
                        if (in_array(BO_COND_FULLYBOOKED, $av->overrides) && in_array(BO_COND_NOTIFYMELIST, $av->overrides)) {
                            $data->selectusersoverbookcheckbox = true;
                        } else {
                            $data->selectusersoverbookcheckbox = false;
                        }
                        break;
                }
            }
        }

        $this->set_data($data);
    }

    public function process_dynamic_submission() {

        // We get the data prepared by set_data_for_dynamic_submission().
        $data = $this->get_data();
        $optionid = $data->optionid;

        // Do nothing if the form is locked!
        if ($data->formlocked) {
            return false;
        }

        // Prepare option values.
        booking_option::purge_cache_for_option($optionid);
        $settings = singleton_service::get_instance_of_booking_option_settings($optionid);
        $cmid = $settings->cmid;
        $context = context_module::instance($cmid);
        $optionvalues = $settings->return_settings_as_stdclass();
        $optionvalues->optionid = $optionid;

        // Now we can modify with our data.
        $optionvalues->restrictanswerperiodopening = true;
        $optionvalues->restrictanswerperiodclosing = true;
        $optionvalues->bookingopeningtime = $data->bookingopeningtime;
        $optionvalues->bookingclosingtime = $data->bookingclosingtime;

        if (!empty(($data->bo_cond_selectusers_userids))) {
            $optionvalues->selectuserscheckbox = $data->selectuserscheckbox;
            $optionvalues->bo_cond_selectusers_userids = $data->bo_cond_selectusers_userids;
            $optionvalues->bo_cond_selectusers_overrideconditioncheckbox = true; // Can be hardcoded here.
            $optionvalues->bo_cond_selectusers_overrideoperator = 'OR'; // Can be hardcoded here.
            // We always override these 2 conditions, so users are always allowed to book outside time restrictions.
            $optionvalues->bo_cond_selectusers_overridecondition = [BO_COND_BOOKING_TIME, BO_COND_OPTIONHASSTARTED];

            // If the overbook checkbox has been checked, we also add the conditions so the user(s) can overbook.
            if (!empty($data->selectusersoverbookcheckbox)) {
                $optionvalues->bo_cond_selectusers_overridecondition[] = BO_COND_FULLYBOOKED;
                $optionvalues->bo_cond_selectusers_overridecondition[] = BO_COND_NOTIFYMELIST;
            }
        }

        if (booking_update_options($optionvalues, $context, UPDATE_OPTIONS_PARAM_REDUCED)) {
            return true;
        }

        return false;
    }

    public function validation($data, $files) {
        $errors = [];

        // TODO: ...

        return $errors;
    }

    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/local/musi/dashboard.php');
    }

    /**
     * Check if logged in user is a teacher of the option.
     * @param int $optionid
     * @return bool true if it's a teacher, false if not
     */
    private function check_if_teacher(int $optionid) {
        global $USER;
        $settings = singleton_service::get_instance_of_booking_option_settings($optionid);
        if (in_array($USER->id, $settings->teacherids)) {
            return true;
        } else {
            return false;
        }
    }
}
