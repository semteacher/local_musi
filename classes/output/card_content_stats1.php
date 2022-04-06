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

use renderer_base;
use renderable;
use stdClass;
use templatable;

// Define booking status parameters.
define('STATUSPARAM_BOOKED', 0);
define('STATUSPARAM_WAITINGLIST', 1);
define('STATUSPARAM_RESERVED', 2);
define('STATUSPARAM_NOTBOOKED', 4);
define('STATUSPARAM_DELETED', 5);

/**
 * This class prepares data for displaying a booking option instance
 *
 * @package local_musi
 * @copyright 2021 Georg Maißer {@link http://www.wunderbyte.at}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class card_content_stats1 implements renderable, templatable {

    /** @var stdClass $title */
    public $data = null;

    /**
     * In the Constructor, we gather all the data we need ans store it in the data property.
     */
    public function __construct() {

        $this->data = self::return_booking_stats();
    }

    /**
     * Generate the stats for this website
     *
     * @return stdClass
     */
    private static function return_booking_stats() {
        global $DB;

        $coursesbooked = $DB->count_records('booking_answers', ['waitinglist' => STATUSPARAM_BOOKED]);
        $coursesincart = $DB->count_records('booking_answers', ['waitinglist' => STATUSPARAM_RESERVED]);
        $courseswaitinglist = $DB->count_records('booking_answers', ['waitinglist' => STATUSPARAM_WAITINGLIST]);
        $coursesdeleted = $DB->count_records('booking_answers', ['waitinglist' => STATUSPARAM_WAITINGLIST]);

        $coursesboughtcard = $DB->count_records('local_shopping_cart_history', ['payment' => 'success']);
        $coursespending = $DB->count_records('local_shopping_cart_history', ['payment' => 'pending']);
        $coursesboughtcashier = $DB->count_records('local_shopping_cart_history', ['payment' => 'cash']);

        $data = new stdClass();
        $data->coursesbooked = $coursesbooked;
        $data->coursesincart = $coursesincart;
        $data->courseswaitinglist = $courseswaitinglist;
        $data->coursesdeleted = $coursesdeleted;

        $data->coursesboughtcard = $coursesboughtcard;
        $data->coursespending = $coursespending;
        $data->coursesboughtcashier = $coursesboughtcashier;

        return $data;
    }

    /**
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {

        // We transform the data object to an array where we can read key & value.
        foreach ($this->data as $key => $value) {
            $returnarray['item'][] = [
                'key' => get_string($key, 'local_musi'),
                'value' => $value
            ];
        }

        return $returnarray;
    }
}
