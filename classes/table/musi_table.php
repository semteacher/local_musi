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

namespace local_musi\table;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../../lib.php');
require_once($CFG->libdir.'/tablelib.php');

use coding_exception;
use context_module;
use dml_exception;
use html_writer;
use local_wunderbyte_table\wunderbyte_table;
use mod_booking\booking;
use mod_booking\booking_option;
use mod_booking\output\button_notifyme;
use mod_booking\output\col_action;
use mod_booking\output\col_availableplaces;
use mod_booking\output\col_price;
use mod_booking\output\col_teacher;
use mod_booking\price;
use mod_booking\singleton_service;
use moodle_exception;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Search results for managers are shown in a table (student search results use the template searchresults_student).
 */
class musi_table extends wunderbyte_table {

    private $outputbooking = null;

    private $outputmusi = null;

    private $bookingsoptionsettings = [];

    private $booking = null;

    private $buyforuser = null;

    private $context = null;

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     * @param booking $booking the booking instance
     */
    public function __construct($uniqueid, $booking = null) {
        parent::__construct($uniqueid);

        global $PAGE;
        $this->baseurl = $PAGE->url;

        if ($booking) {
            $this->booking = $booking;
        }

        $this->outputbooking = $PAGE->get_renderer('mod_booking');
        $this->outputmusi = $PAGE->get_renderer('local_musi');

        // We set buyforuser here for better performance.
        $this->buyforuser = price::return_user_to_buy_for();

        // Columns and headers are not defined in constructor, in order to keep things as generic as possible.
    }

    /**
     * This function is called for each data row to allow processing of the
     * invisible value. It's called 'invisibleoption' so it does not interfere with
     * the bootstrap class 'invisible'.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $invisible Returns visibility of the booking option as string.
     * @throws coding_exception
     */
    public function col_invisibleoption($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        if (!empty($settings->invisible)) {
            return get_string('invisibleoption', 'mod_booking');
        } else {
            return '';
        }
    }

    public function col_image($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        if (empty($settings->imageurl)) {
            return null;
        }

        return $settings->imageurl;
    }

    /**
     * This function is called for each data row to allow processing of the
     * teacher value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $string Return name of the booking option.
     * @throws dml_exception
     */
    public function col_teacher($values) {

        // Render col_teacher using a template.
        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        $data = new col_teacher($values->id, $settings);

        return $this->outputmusi->render_col_teacher($data);
    }

    /**
     * This function is called for each data row to allow processing of the
     * price value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $string Return name of the booking option.
     * @throws dml_exception
     */
    public function col_price($values) {

        // Render col_price using a template.
        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        // First we check if the user is booked already.
        $bookinganswer = singleton_service::get_instance_of_booking_answers($settings, $values->id);

        // Make sure we have the context when we need it.
        if (!$this->context) {
            $this->context = context_module::instance($settings->cmid);
        }

        $bookingstatus = $bookinganswer->user_status($this->buyforuser->id);

        if ($bookingstatus == MUSI_STATUSPARAM_BOOKED) {
            // We need to return a class so shopping_cart will know where to put the addtocartbutton after cancelation.
            return html_writer::span(get_string('booked', 'mod_booking') , "price_mod_booking_" . $values->id);
        } else if ($bookingstatus == MUSI_STATUSPARAM_WAITINGLIST) {
            // We need to return a class so shopping_cart will know where to put the addtocartbutton after cancelation.
            return html_writer::span(get_string('waitinglist', 'mod_booking') , "price_mod_booking_" . $values->id);
        }

        // If we are not yet booked not on the waiting list...
        // ... but the list is already full we can't buy, but we might be able to get on the notification list.

        $usenotificationlist = get_config('booking', 'usenotificationlist');

        $bookinginformation = $bookinganswer->return_all_booking_information($this->buyforuser->id);
        if (isset($bookinginformation['notbooked'])
            && $bookinginformation['notbooked']['fullybooked']) {
            if ($usenotificationlist) {
                $data = new button_notifyme($this->buyforuser->id, $values->id, $bookinginformation['notbooked']['onnotifylist']);
                return $this->outputbooking->render_notifyme_button($data);
            } else {
                return get_string('nobookingpossible', 'mod_booking');
            }
        }

        // We pass on the id of the booking option.
        $data = new col_price($values, $settings, $this->buyforuser, $this->context);

        return $this->outputbooking->render_col_price($data);
    }

    /**
     * This function is called for each data row to allow processing of the
     * text value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $string Return name of the booking option.
     * @throws dml_exception
     */
    public function col_text($values) {

        if (!$this->booking) {
            $this->booking = singleton_service::get_instance_of_booking_by_optionid($values->id);
        }

        // Get rid of separator and identifier in this view.
        booking_option::transform_unique_bookingoption_name_to_display_name($values);

        $data = new stdClass();

        if ($this->booking) {
            $url = new moodle_url('/mod/booking/optionview.php', ['optionid' => $values->id,
                                                                  'cmid' => $this->booking->cmid,
                                                                  'userid' => $this->buyforuser->id]);
            $data->url = $url->out(false);
            $data->cmid = $this->booking->cmid;
        } else {
            $data->url = '#';
        }
        $data->title = $values->text;
        if (!empty($values->titleprefix)) {
            $data->title = $values->titleprefix . ' - ' . $values->text;
        }

        // We will have a number of modals on this site, therefore we have to distinguish them.
        // This is in case we render modal.
        $data->modalcounter = $values->id;
        $data->modaltitle = $values->text;
        $data->userid = $this->buyforuser->id;

        // Get the URL to edit the option.
        if (!empty($values->id)) {
            $bookingsoptionsettings = singleton_service::get_instance_of_booking_option_settings($values->id);
            if (!empty($bookingsoptionsettings)) {

                if (!$this->context) {
                    $this->context = context_module::instance($bookingsoptionsettings->cmid);
                }

                // If the user has no capability to editoptions, the URLs will not be added.
                if ((has_capability('mod/booking:updatebooking', $this->context) ||
                        has_capability('mod/booking:addeditownoption', $this->context))) {
                    if (isset($bookingsoptionsettings->editoptionurl)) {
                        // Get the URL to edit the option.

                        $data->editoptionurl = self::add_return_url($bookingsoptionsettings->editoptionurl);
                    }
                    if (isset($bookingsoptionsettings->editteachersurl)) {
                        // Get the URL to edit the teachers for the option.
                        $data->editteachersurl = $bookingsoptionsettings->editteachersurl;
                    }
                    if (isset($bookingsoptionsettings->optiondatesteachersurl)) {
                        // Get the URL for the optiondates-teachers-report.
                        $data->optiondatesteachersurl = $bookingsoptionsettings->optiondatesteachersurl;
                    }
                }
            }
        }

        // To easily switch to modal view again.
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* return $this->output->render_col_text_modal_js($data); */

        return $this->outputbooking->render_col_text_link($data);
    }


    /**
     * This function is called for each data row to allow processing of the
     * coursestarttime value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $coursestarttime Returns course start time as a readable string.
     * @throws coding_exception
     */
    public function col_bookings($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);
        // Render col_bookings using a template.
        $data = new col_availableplaces($values, $settings, $this->buyforuser);
        return $this->outputbooking->render_col_availableplaces($data);
    }

    /**
     * This function is called for each data row to allow processing of the
     * coursestarttime value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $coursestarttime Returns course start time as a readable string.
     * @throws coding_exception
     */
    public function col_location($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        if (isset($settings->entity) && (count($settings->entity) > 0)) {

            $url = new moodle_url('/local/entities/view.php', ['id' => $settings->entity['id']]);
            return html_writer::tag('a', $settings->entity['name'], ['href' => $url->out(false)]);
        }

        return $settings->location;
    }

    /**
     * This function is called for each data row to allow processing of the
     * sports value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $sports Returns course start time as a readable string.
     * @throws coding_exception
     */
    public function col_sport($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        if (isset($settings->customfields) && isset($settings->customfields['sport'])) {
            if (is_array($settings->customfields['sport'])) {
                return implode(", ", $settings->customfields['sport']);
            } else {
                return $settings->customfields['sport'];
            }
        }
        return '';
    }

    /**
     * This function is called for each data row to allow processing of the
     * dayofweek value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $dayofweek Returns course start time as a readable string.
     * @throws coding_exception
     */
    public function col_dayofweek($values) {

        $settings = singleton_service::get_instance_of_booking_option_settings($values->id);

        if (!empty($settings->dayofweektime)) {
            return $settings->dayofweektime;
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * courseendtime value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $courseendtime Returns course end time as a readable string.
     * @throws coding_exception
     */
    public function col_coursedates($values) {

        // Prepare date string.
        if ($values->coursestarttime != 0) {
            $returnarray[] = userdate($values->coursestarttime, get_string('strftimedatetime'));
        }

        // Prepare date string.
        if ($values->courseendtime != 0) {
            $returnarray[] = userdate($values->courseendtime, get_string('strftimedatetime'));
        }

        return implode(' - ', $returnarray);
    }

    /**
     * This function is called for each data row to add a link
     * for managing responses (booking_answers).
     *
     * @param object $values Contains object with all the values of record.
     * @return string $link Returns a link to report.php (manage responses).
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function col_manageresponses($values) {
        global $CFG, $DB;

        // Link is empty on default.
        $link = '';

        if ($DB->get_records('booking_answers', ['optionid' => $values->optionid])) {
            // Add a link to redirect to the booking option.
            $link = new moodle_url($CFG->wwwroot . '/mod/booking/report.php', array(
                'id' => $values->cmid,
                'optionid' => $values->optionid
            ));
            // Use html_entity_decode to convert "&amp;" to a simple "&" character.
            $link = html_entity_decode($link->out());

            if (!$this->is_downloading()) {
                // Only format as a button if it's not an export.
                $link = '<a href="' . $link . '" class="btn btn-secondary">'
                    . get_string('bstmanageresponses', 'mod_booking')
                    . '</a>';
            }
        }
        // Do not show a link if there are no answers.

        return $link;
    }

    /**
     * This function is called for each data row to allow processing of the
     * action button.
     *
     * @param object $values Contains object with all the values of record.
     * @return string $action Returns formatted action button.
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function col_action($values) {

        // Render col_action using a template.

        // Currently, this will use dummy teachers.
        $data = new col_action($values->id);

        return $this->outputbooking->render_col_action($data);
    }

    /**
     * Override wunderbyte_table function and use own renderer.
     *
     * @return void
     */
    public function finish_html() {
        $table = new \local_wunderbyte_table\output\table($this);
        echo $this->outputbooking->render_bookingoptions_table($table);
    }

    /**
     * With this function, the table can be printed without lazy loading.
     * Can be overridden in child class with own renderer.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @param string $downloadhelpbutton
     * @return string
     */
    public function nolazyout($pagesize, $useinitialsbar, $downloadhelpbutton = '') {

        global $PAGE;
        $this->pagesize = $pagesize;
        $this->useinitialsbar = $useinitialsbar;
        $this->downloadhelpbutton = $downloadhelpbutton;

        $tableobject = $this->printtable($pagesize, $useinitialsbar);
        $output = $PAGE->get_renderer('local_musi');
        return $output->render_nolazytable($tableobject);
    }


    private static function add_return_url(string $urlstring):string {

        $returnurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $urlcomponents = parse_url($urlstring);

        parse_str($urlcomponents['query'], $params);

        $url = new moodle_url(
            $urlcomponents['path'],
            array_merge(
                $params, [
                'returnto' => 'url',
                'returnurl' => $returnurl->out(false)
                ]
            )
        );

        return $url->out(false);
    }
}
