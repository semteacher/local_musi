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
 * This file contains the definition for the renderable classes for the booking instance
 *
 * @package   local_musi
 * @copyright 2021 Georg Maißer {@link http://www.wunderbyte.at}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_musi\output;

use context_system;
use local_shopping_cart\shopping_cart;
use mod_booking\singleton_service;
use renderer_base;
use renderable;
use stdClass;
use templatable;

/**
 * This class prepares data for displaying user information
 *
 * @package local_musi
 * @copyright 2021 Georg Maißer {@link http://www.wunderbyte.at}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userinformation implements renderable, templatable {

    /** @var stdClass $data */
    public $data = [];

    /**
     * Constructor.
     *
     * @param integer $userid
     */
    public function __construct(int $userid, string $fields) {

        global $CFG, $DB;

        if (empty($fields)) {
            return 'You can add fields like this in the shortcode \'fields="firstname,lastname"\'';
        }

        require_once("$CFG->dirroot/user/profile/lib.php");

        $user = singleton_service::get_instance_of_user($userid);

        $fields = explode(',', $fields);

        foreach ($user as $key => $value) {

            if (!in_array($key, $fields)) {
                continue;
            }
            $this->data[] = [
                'key' => get_string($key, 'core'),
                'value' => $value,
            ];
        }

        profile_load_custom_fields($user);

        foreach ($user->profile as $key => $value) {

            if (!in_array($key, $fields)) {
                continue;
            }
            $localized = $DB->get_field('user_info_field', 'name', ['shortname' => $key]);
            $this->data[] = [
                'key' => $localized,
                'value' => $value,
            ];
        }
    }

    /**
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        $returnarray = array(
                'user' => (array)$this->data
        );

        return $returnarray;
    }
}
