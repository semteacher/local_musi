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

        $optionid = $this->_ajaxformdata['optionid'];

        $mform->addElement('html', 'TODO: add form elements here! - optionid ' . $optionid);
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

        // TODO: Set data.
        // $data->botags = self::get_existing_botags_array();

        $this->set_data($data);
    }

    public function process_dynamic_submission() {
        global $DB;

        $data = $this->get_data();

        // TODO: ...

        return $data;
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
