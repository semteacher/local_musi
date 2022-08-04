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
use stdClass;

/**
 * Modal form to create single option dates which are not part of the date series.
 *
 * @package     mod_booking
 * @copyright   2022 Wunderbyte GmbH <info@wunderbyte.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class botags_modal_form extends \core_form\dynamic_form {

    protected function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     * {@inheritdoc}
     * @see moodleform::definition()
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $existingbotags = $DB->get_records('local_musi_botags');

        if (empty($existingbotags)) {
            $existingbotags = [];
        }

        $mform->addElement('autocomplete', 'botags', get_string('editbotags', 'local_musi'),
            $existingbotags, ['tags' => true, 'multiple' => true]);
    }

    /**
     * Check access for dynamic submission.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('local/musi:canedit', $this->get_context_for_dynamic_submission());
    }


    public function set_data_for_dynamic_submission(): void {
        $data = new stdClass();

        // TODO?

        $this->set_data($data);
    }

    public function process_dynamic_submission() {
        $data = $this->get_data();

        // TODO.

        return $data;
    }

    public function validation($data, $files) {
        $errors = [];

        return $errors;
    }

    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/local/musi/dashboard.php');
    }
}
