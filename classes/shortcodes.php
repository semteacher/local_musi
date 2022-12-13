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
 * Shortcodes for local_musi
 *
 * @package local_musi
 * @subpackage db
 * @since Moodle 3.11
 * @copyright 2022 Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_musi;

use context_module;
use local_musi\output\page_allteachers;
use local_musi\table\musi_table;
use mod_booking\booking;
use mod_booking\singleton_service;
use moodle_url;

/**
 * Deals with local_shortcodes regarding booking.
 */
class shortcodes {

    /**
     * Prints out list of bookingoptions.
     * Argumtents can be 'category' or 'perpage'.
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return void
     */
    public static function allcourseslist($shortcode, $args, $content, $env, $next) {

        // If the id argument was not passed on, we have a fallback in the connfig.
        if (!isset($args['id'])) {
            $args['id'] = get_config('local_musi', 'shortcodessetinstance');
        }

        // To prevent misconfiguration, id has to be there and int.
        if (!(isset($args['id']) && $args['id'] && is_int((int)$args['id']))) {
            return 'Set id of booking instance';
        }

        if (!$booking = singleton_service::get_instance_of_booking_by_cmid($args['id'])) {
            return 'Couldn\'t find right booking instance ' . $args['id'];
        }

        if (!isset($args['category']) || !$category = ($args['category'])) {
            $category = '';
        }

        if (!isset($args['filter']) || !$showfilter = ($args['filter'])) {
            $showfilter = false;
        }

        if (!isset($args['search']) || !$showsearch = ($args['search'])) {
            $showsearch = false;
        }

        if (!isset($args['sort']) || !$showsort = ($args['sort'])) {
            $showsort = false;
        }

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!isset($args['infinitescrollpage']) || !$infinitescrollpage = ($args['infinitescrollpage'])) {
            $infinitescrollpage = 20;
        } */

        if (
            !isset($args['perpage'])
            || !is_int((int)$args['perpage'])
            || !$perpage = ($args['perpage'])
        ) {
            $perpage = 1000;
        }

        $tablename = bin2hex(random_bytes(12));

        $table = new musi_table($tablename, $booking);

        $wherearray = ['bookingid' => (int)$booking->id];

        if (!empty($category)) {
            $wherearray['sport'] = $category;
        };

        // If we want to find only the teacher relevant options, we chose different sql.
        if (isset($args['teacherid']) && (is_int((int)$args['teacherid']))) {
            $wherearray['teacherobjects'] = '%"id":' . $args['teacherid'] . ',%';
            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        } else {

            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        }

        $table->set_filter_sql($fields, $from, $where, $params, $filter);

        $table->use_pages = false;

        $table->define_cache('mod_booking', 'bookingoptionstable');

        $table->add_subcolumns('cardbody', ['sport', 'text', 'action', 'botags', 'dayofweektime', 'teacher', 'location',
            'bookings', 'price']);

        // This avoids showing all keys in list view.
        $table->add_classes_to_subcolumns('cardbody', ['columnkeyclass' => 'd-none']);

        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-12 col-md-12 text-left'], ['sport']);
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'sport-badge rounded-sm bg-light text-dark
            pl-1 pr-1 pb-0 pt-0 mr-1'], ['sport']);

        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-md-9 col-sm-9 text-left'], ['text']);
        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-3 col-md-3 text-right'], ['action']);

        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-12 col-md-12 text-left'], ['botags']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-tags'], ['botags']);

        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-3 col-md-3 text-left'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-clock-o'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-2 col-md-2 text-left'], ['teacher']);
        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-2 col-md-2 text-left'], ['location']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-map-marker'], ['location']);
        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-2 col-md-2 text-left'], ['bookings']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-map-ticket'], ['bookings']);
        $table->add_classes_to_subcolumns('cardbody', ['columnclass' => 'col-sm-3 col-md-3 text-right'], ['price']);

        // Override naming for columns. one could use getstring for localisation here.
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_text', 'booking')],
            ['text']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_teacher', 'booking')],
            ['teacher']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_maxanswers', 'booking')],
            ['maxanswers']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_maxoverbooking', 'booking')],
            ['maxoverbooking']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_coursestarttime', 'booking')],
            ['coursestarttime']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_courseendtime', 'booking')],
            ['courseendtime']
        );

        $table->is_downloading('', 'List of booking options');

        // Id is not really something one wants to filter, but we need the dataset on the html element.
        // The key "id" won't be rendered in filter json, though.
        if ($showfilter !== false) {
            $table->define_filtercolumns([
                'id', 'sport' => [
                    'localizedname' => get_string('sport', 'local_musi')
                ], 'dayofweek' => [
                    'localizedname' => get_string('dayofweek', 'local_musi'),
                    'monday' => get_string('monday', 'mod_booking'),
                    'tuesday' => get_string('tuesday', 'mod_booking'),
                    'wednesday' => get_string('wednesday', 'mod_booking'),
                    'thursday' => get_string('thursday', 'mod_booking'),
                    'friday' => get_string('friday', 'mod_booking'),
                    'saturday' => get_string('saturday', 'mod_booking'),
                    'sunday' => get_string('sunday', 'mod_booking')
                ],  'location' => [
                    'localizedname' => get_string('location', 'mod_booking')
                ],  'botags' => [
                    'localizedname' => get_string('tags', 'core')
                ]
            ]);
        }

        if ($showsearch !== false) {
            $table->define_fulltextsearchcolumns(['titleprefix', 'text', 'sport', 'description', 'location', 'teacherobjects']);
        }

        if ($showsort !== false) {
            $table->define_sortablecolumns([
                'text' => get_string('coursename', 'local_musi'),
                'sport' => get_string('sport', 'local_musi'),
                'location',
                'dayofweek'
            ]);
        } else {
            $table->sortable(true, 'text');
        }

        // It's important to have the baseurl defined, we use it as a return url at one point.
        $baseurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $table->define_baseurl($baseurl->out());

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 60;

        $table->tabletemplate = 'local_musi/table_list';

        // If we find "nolazy='1'", we return the table directly, without lazy loading.
        if (isset($args['lazy']) && ($args['lazy'] == 1)) {

            list($idstring, $encodedtable, $out) = $table->lazyouthtml($perpage, true);

            return $out;
        }

        $out = $table->outhtml($perpage, true);

        return $out;
    }

    /**
     * Prints out grid of bookingoptions.
     * Argumtents can be 'category' or 'perpage'.
     * Templates table_grid...
     * Styles Tablegrid
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return void
     */
    public static function allcoursesgrid($shortcode, $args, $content, $env, $next) {

        // If the id argument was not passed on, we have a fallback in the connfig.
        if (!isset($args['id'])) {
            $args['id'] = get_config('local_musi', 'shortcodessetinstance');
        }

        // To prevent misconfiguration, id has to be there and int.
        if (!(isset($args['id']) && $args['id'] && is_int((int)$args['id']))) {
            return 'Set id of booking instance';
        }

        if (!$booking = singleton_service::get_instance_of_booking_by_cmid($args['id'])) {
            return 'Couldn\'t find right booking instance ' . $args['id'];
        }

        if (!isset($args['category']) || !$category = ($args['category'])) {
            $category = '';
        }

        if (!isset($args['filter']) || !$showfilter = ($args['filter'])) {
            $showfilter = false;
        }

        if (!isset($args['search']) || !$showsearch = ($args['search'])) {
            $showsearch = false;
        }

        if (!isset($args['sort']) || !$showsort = ($args['sort'])) {
            $showsort = false;
        }

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!isset($args['infinitescrollpage']) || !$infinitescrollpage = ($args['infinitescrollpage'])) {
            $infinitescrollpage = 20;
        } */

        if (
            !isset($args['perpage'])
            || !is_int((int)$args['perpage'])
            || !$perpage = ($args['perpage'])
        ) {
            $perpage = 1000;
        }

        $tablename = bin2hex(random_bytes(12));

        $table = new musi_table($tablename, $booking);

        $wherearray = ['bookingid' => (int)$booking->id];

        if (!empty($category)) {
            $wherearray['sport'] = $category;
        };

        // If we want to find only the teacher relevant options, we chose different sql.
        if (isset($args['teacherid']) && (is_int((int)$args['teacherid']))) {
            $wherearray['teacherobjects'] = '%"id":' . $args['teacherid'] . ',%';
            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        } else {

            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        }

        $table->set_filter_sql($fields, $from, $where, $params, $filter);

        $table->use_pages = false;

        $table->define_cache('mod_booking', 'bookingoptionstable');

        $table->add_subcolumns('entrybody', ['text', 'dayofweektime', 'sport', 'teacher', 'location', 'bookings',
            'price', 'action']);

        // This avoids showing all keys in list view.
        $table->add_classes_to_subcolumns('entrybody', ['columnkeyclass' => 'd-md-none']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-text'], ['text']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-dayofweektime'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('entrybody', ['columniclassbefore' => 'fa fa-clock-o'], ['dayofweektime']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-sport'], ['sport']);
        $table->add_classes_to_subcolumns('entrybody', ['columnvalueclass' => 'sport-badge bg-info text-light'], ['sport']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-teacher'], ['teacher']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-location'], ['location']);
        $table->add_classes_to_subcolumns('entrybody', ['columniclassbefore' => 'fa fa-map-marker'], ['location']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-booking'], ['bookings']);

        $table->add_classes_to_subcolumns('entrybody', ['columnclass' => 'grid-area-price'], ['price']);

        // Override naming for columns. one could use getstring for localisation here.
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_text', 'booking')],
            ['text']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_teacher', 'booking')],
            ['teacher']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_maxanswers', 'booking')],
            ['maxanswers']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_maxoverbooking', 'booking')],
            ['maxoverbooking']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_coursestarttime', 'booking')],
            ['coursestarttime']
        );
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['keystring' => get_string('tableheader_courseendtime', 'booking')],
            ['courseendtime']
        );

        $table->is_downloading('', 'List of booking options');

        // Id is not really something one wants to filter, but we need the dataset on the html element.
        // The key "id" won't be rendered in filter json, though.
        if ($showfilter !== false) {
            $table->define_filtercolumns([
                'id', 'sport' => [
                    'localizedname' => get_string('sport', 'local_musi')
                ], 'dayofweek' => [
                    'localizedname' => get_string('dayofweek', 'local_musi'),
                    'monday' => get_string('monday', 'mod_booking'),
                    'tuesday' => get_string('tuesday', 'mod_booking'),
                    'wednesday' => get_string('wednesday', 'mod_booking'),
                    'thursday' => get_string('thursday', 'mod_booking'),
                    'friday' => get_string('friday', 'mod_booking'),
                    'saturday' => get_string('saturday', 'mod_booking'),
                    'sunday' => get_string('sunday', 'mod_booking')
                ],  'location' => [
                    'localizedname' => get_string('location', 'mod_booking')
                ],  'botags' => [
                    'localizedname' => get_string('tags', 'core')
                ]
            ]);
        }

        if ($showsearch !== false) {
            $table->define_fulltextsearchcolumns(['titleprefix', 'text', 'sport', 'description', 'location', 'teacherobjects']);
        }

        if ($showsort !== false) {
            $table->define_sortablecolumns([
                'text' => get_string('coursename', 'local_musi'),
                'sport' => get_string('sport', 'local_musi'),
                'location',
                'dayofweek'
            ]);
        } else {
            $table->sortable(true, 'text');
        }

        // It's important to have the baseurl defined, we use it as a return url at one point.
        $baseurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $table->define_baseurl($baseurl->out());

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 100;

        $table->tabletemplate = 'local_musi/table_grid_list';

        // If we find "nolazy='1'", we return the table directly, without lazy loading.
        if (isset($args['lazy']) && ($args['lazy'] == 1)) {

            list($idstring, $encodedtable, $out) = $table->lazyouthtml($perpage, true);

            return $out;
        }

        $out = $table->outhtml($perpage, true);

        return $out;
    }

    /**
     * Prints out list of bookingoptions.
     * Argumtents can be 'category' or 'perpage'.
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return void
     */
    public static function allcoursescards($shortcode, $args, $content, $env, $next) {

        // TODO: Define capality.
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!has_capability('moodle/site:config', $env->context)) {
            return '';
        } */

        // If the id argument was not passed on, we have a fallback in the connfig.
        if (!isset($args['id'])) {
            $args['id'] = get_config('local_musi', 'shortcodessetinstance');
        }

        // To prevent misconfiguration, id has to be there and int.
        if (!(isset($args['id']) && $args['id'] && is_int((int)$args['id']))) {
            return 'Set id of booking instance';
        }

        if (!$booking = singleton_service::get_instance_of_booking_by_cmid($args['id'])) {
            return 'Couldn\'t find right booking instance ' . $args['id'];
        }

        if (!isset($args['category']) || !$category = ($args['category'])) {
            $category = '';
        }

        if (!isset($args['filter']) || !$showfilter = ($args['filter'])) {
            $showfilter = false;
        }

        if (!isset($args['search']) || !$showsearch = ($args['search'])) {
            $showsearch = false;
        }

        if (!isset($args['sort']) || !$showsort = ($args['sort'])) {
            $showsort = false;
        }

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!isset($args['infinitescrollpage']) || !$infinitescrollpage = ($args['infinitescrollpage'])) {
            $infinitescrollpage = 20;
        } */

        if (
            !isset($args['perpage'])
            || !is_int((int)$args['perpage'])
            || !$perpage = ($args['perpage'])
        ) {
            $perpage = 1000;
        }

        $tablename = bin2hex(random_bytes(12));

        $table = new musi_table($tablename, $booking);

        $wherearray = ['bookingid' => (int)$booking->id];

        if (!empty($category)) {
            $wherearray['sport'] = $category;
        };

        // If we want to find only the teacher relevant options, we chose different sql.
        if (isset($args['teacherid']) && (is_int((int)$args['teacherid']))) {
            $wherearray['teacherobjects'] = '%"id":' . $args['teacherid'] . ',%';
            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        } else {

            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray);
        }

        $table->set_filter_sql($fields, $from, $where, $params, $filter);

        $table->use_pages = false;

        $table->define_cache('mod_booking', 'bookingoptionstable');

        $table->add_subcolumns('itemcategory', ['sport']);
        $table->add_subcolumns('itemday', ['dayofweektime']);
        $table->add_subcolumns('cardimage', ['image']);
        $table->add_subcolumns('optioninvisible', ['invisibleoption']);

        $table->add_subcolumns('cardbody', ['action', 'invisibleoption', 'sport', 'text', 'botags', 'teacher']);
        $table->add_classes_to_subcolumns('cardbody', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'float-right m-1'], ['action']);
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['columnvalueclass' => 'shortcodes_option_info_invisible'],
            ['invisibleoption']
        );
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h6 sport-badge rounded-sm bg-light text-dark
        pl-1 pr-1 pb-0 pt-0 mr-1'], ['sport']);
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h5'], ['text']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-tag'], ['botags']);

        $table->add_subcolumns('cardlist', ['dayofweektime', 'location', 'bookings']);
        $table->add_classes_to_subcolumns('cardlist', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-map-marker'], ['location']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-clock-o'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-users'], ['bookings']);

        $table->add_subcolumns('cardfooter', ['price']);
        $table->add_classes_to_subcolumns('cardfooter', ['columnkeyclass' => 'd-none']);

        $table->set_tableclass('cardimageclass', 'w-100');

        $table->is_downloading('', 'List of booking options');

        // Id is not really something one wants to filter, but we need the dataset on the html element.
        // The key "id" won't be rendered in filter json, though.
        if ($showfilter !== false) {
            $table->define_filtercolumns([
                'id', 'sport' => [
                    'localizedname' => get_string('sport', 'local_musi')
                ], 'dayofweek' => [
                    'localizedname' => get_string('dayofweek', 'local_musi'),
                    'monday' => get_string('monday', 'mod_booking'),
                    'tuesday' => get_string('tuesday', 'mod_booking'),
                    'wednesday' => get_string('wednesday', 'mod_booking'),
                    'thursday' => get_string('thursday', 'mod_booking'),
                    'friday' => get_string('friday', 'mod_booking'),
                    'saturday' => get_string('saturday', 'mod_booking'),
                    'sunday' => get_string('sunday', 'mod_booking')
                ],  'location' => [
                    'localizedname' => get_string('location', 'mod_booking')
                ],  'botags' => [
                    'localizedname' => get_string('tags', 'core')
                ]
            ]);
        }

        if ($showsearch !== false) {
            $table->define_fulltextsearchcolumns(['titleprefix', 'text', 'sport', 'description', 'location', 'teacherobjects']);
        }

        if ($showsort !== false) {
            $table->define_sortablecolumns([
                'text' => get_string('coursename', 'local_musi'),
                'sport' => get_string('sport', 'local_musi'),
                'location',
                'dayofweek'
            ]);
        } else {
            $table->sortable(true, 'text');
        }

        // It's important to have the baseurl defined, we use it as a return url at one point.
        $baseurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $table->define_baseurl($baseurl->out());

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 30;

        $table->tabletemplate = 'local_musi/table_card';

        // If we find "nolazy='1'", we return the table directly, without lazy loading.
        if (isset($args['lazy']) && ($args['lazy'] == 1)) {

            list($idstring, $encodedtable, $out) = $table->lazyouthtml($perpage, true);

            return $out;
        }

        $out = $table->outhtml($perpage, true);

        return $out;
    }


    /**
     * Prints out list of bookingoptions.
     * Arguments can be 'id', 'category' or 'perpage'.
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return void
     */
    public static function mycoursescards($shortcode, $args, $content, $env, $next) {

        global $USER;

        // If the id argument was not passed on, we have a fallback in the connfig.
        if (!isset($args['id'])) {
            $args['id'] = get_config('local_musi', 'shortcodessetinstance');
        }

        // To prevent misconfiguration, id has to be there and int.
        if (!(isset($args['id']) && $args['id'] && is_int((int)$args['id']))) {
            return 'Set id of booking instance';
        }

        if (!$booking = singleton_service::get_instance_of_booking_by_cmid($args['id'])) {
            return 'Couldn\'t find right booking instance ' . $args['id'];
        }

        if (!isset($args['category']) || !$category = ($args['category'])) {
            $category = '';
        }

        if (!isset($args['filter']) || !$showfilter = ($args['filter'])) {
            $showfilter = false;
        }

        if (!isset($args['search']) || !$showsearch = ($args['search'])) {
            $showsearch = false;
        }

        if (!isset($args['sort']) || !$showsort = ($args['sort'])) {
            $showsort = false;
        }

        if (
            !isset($args['perpage'])
            || !is_int((int)$args['perpage'])
            || !$perpage = ($args['perpage'])
        ) {
            $perpage = 1000;
        }

        $tablename = bin2hex(random_bytes(12));

        $table = new musi_table($tablename, $booking);

        $wherearray = ['bookingid' => (int)$booking->id];

        if (!empty($category)) {
            $wherearray['sport'] = $category;
        };

        $userid = $USER->id;

        // If we want to find only the teacher relevant options, we chose different sql.
        if (isset($args['teacherid']) && (is_int((int)$args['teacherid']))) {
            $wherearray['teacherobjects'] = '%"id":' . $args['teacherid'] . ',%';
            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray, $userid);
        } else {

            list($fields, $from, $where, $params, $filter) =
                booking::get_options_filter_sql(0, 0, '', null, $booking->context, [], $wherearray, $userid);
        }

        $table->set_filter_sql($fields, $from, $where, $params, $filter);

        $table->use_pages = false;

        $table->define_cache('mod_booking', 'bookingoptionstable');

        $table->add_subcolumns('itemcategory', ['sport']);
        $table->add_subcolumns('itemday', ['dayofweektime']);
        $table->add_subcolumns('cardimage', ['image']);
        $table->add_subcolumns('optioninvisible', ['invisibleoption']);

        $table->add_subcolumns('cardbody', ['invisibleoption', 'sport', 'text', 'teacher', 'botags']);
        $table->add_classes_to_subcolumns('cardbody', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['columnvalueclass' => 'shortcodes_option_info_invisible'],
            ['invisibleoption']
        );
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h6'], ['sports']);
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h5'], ['text']);
        $table->add_classes_to_subcolumns('cardbody', ['columniclassbefore' => 'fa fa-tag'], ['botags']);

        $table->add_subcolumns('cardlist', ['dayofweektime', 'location', 'bookings']);
        $table->add_classes_to_subcolumns('cardlist', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-map-marker'], ['location']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-clock-o'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-users'], ['bookings']);

        $table->add_subcolumns('cardfooter', ['price']);
        $table->add_classes_to_subcolumns('cardfooter', ['columnkeyclass' => 'd-none']);

        $table->set_tableclass('cardimageclass', 'w-100');

        $table->is_downloading('', 'List of booking options');

        // Id is not really something one wants to filter, but we need the dataset on the html element.
        // The key "id" won't be rendered in filter json, though.
        if ($showfilter !== false) {
            $table->define_filtercolumns([
                'id', 'sport' => [
                    'localizedname' => get_string('sport', 'local_musi')
                ], 'dayofweek' => [
                    'localizedname' => get_string('dayofweek', 'local_musi'),
                    'monday' => get_string('monday', 'mod_booking'),
                    'tuesday' => get_string('tuesday', 'mod_booking'),
                    'wednesday' => get_string('wednesday', 'mod_booking'),
                    'thursday' => get_string('thursday', 'mod_booking'),
                    'friday' => get_string('friday', 'mod_booking'),
                    'saturday' => get_string('saturday', 'mod_booking'),
                    'sunday' => get_string('sunday', 'mod_booking')
                ],  'location' => [
                    'localizedname' => get_string('location', 'mod_booking')
                ],  'botags' => [
                    'localizedname' => get_string('tags', 'core')
                ]
            ]);
        }

        if ($showsearch !== false) {
            $table->define_fulltextsearchcolumns(['titleprefix', 'text', 'sport', 'description', 'location', 'teacherobjects']);
        }

        if ($showsort !== false) {
            $table->define_sortablecolumns([
                'text' => get_string('coursename', 'local_musi'),
                'sport' => get_string('sport', 'local_musi'),
                'location',
                'dayofweek'
            ]);
        } else {
            $table->sortable(true, 'text');
        }

        // It's important to have the baseurl defined, we use it as a return url at one point.
        $baseurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $table->define_baseurl($baseurl->out());

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 30;

        $table->tabletemplate = 'local_musi/table_card';

        // If we find "nolazy='1'", we return the table directly, without lazy loading.
        if (isset($args['lazy']) && ($args['lazy'] == 1)) {

            list($idstring, $encodedtable, $out) = $table->lazyouthtml($perpage, true);

            return $out;
        }

        $out = $table->outhtml($perpage, true);

        return $out;
    }

    /**
     * Prints out all teachers as cards.
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return void
     */
    public static function allteacherscards($shortcode, $args, $content, $env, $next) {
        global $DB, $PAGE;

        $teacherids = [];

        // Now get all teachers that we're interested in.
        $sqlteachers =
            "SELECT DISTINCT bt.userid, u.lastname
            FROM {booking_teachers} bt
            LEFT JOIN {user} u
            ON u.id = bt.userid
            ORDER BY u.lastname ASC";

        if ($teacherrecords = $DB->get_records_sql($sqlteachers)) {
            foreach ($teacherrecords as $teacherrecord) {
                $teacherids[] = $teacherrecord->userid;
            }
        }

        // Now prepare the data for all teachers.
        $data = new page_allteachers($teacherids);
        $output = $PAGE->get_renderer('local_musi');
        // And return the rendered page showing all teachers.
        return $output->render_allteacherspage($data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $shortcode
     * @param [type] $args
     * @param [type] $content
     * @param [type] $env
     * @param [type] $next
     * @return array
     */
    private static function return_base_table($shortcode, $args, $content, $env, $next) {

        // TODO: Define capality.
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!has_capability('moodle/site:config', $env->context)) {
            return '';
        } */

        // If the id argument was not passed on, we have a fallback in the connfig.
        if (!isset($args['id'])) {
            $args['id'] = get_config('local_musi', 'shortcodessetinstance');
        }

        // To prevent misconfiguration, id has to be there and int.
        if (!(isset($args['id']) && $args['id'] && is_int((int)$args['id']))) {
            return 'Set id of booking instance';
        }

        if (!$booking = singleton_service::get_instance_of_booking_by_cmid($args['id'])) {
            return 'Couldn\'t find right booking instance ' . $args['id'];
        }

        if (!isset($args['category']) || !$category = ($args['category'])) {
            $category = '';
        }

        if (!isset($args['filter']) || !$showfilter = ($args['filter'])) {
            $showfilter = false;
        }

        if (!isset($args['search']) || !$showsearch = ($args['search'])) {
            $showsearch = false;
        }

        if (!isset($args['sort']) || !$showsort = ($args['sort'])) {
            $showsort = false;
        }

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /* if (!isset($args['infinitescrollpage']) || !$infinitescrollpage = ($args['infinitescrollpage'])) {
            $infinitescrollpage = 20;
        } */

        if (
            !isset($args['perpage'])
            || !is_int((int)$args['perpage'])
            || !$perpage = ($args['perpage'])
        ) {
            $perpage = 1000;
        }

        $tablename = bin2hex(random_bytes(12));

        $table = new musi_table($tablename, $booking);

        $wherearray = ['bookingid' => (int)$booking->id];

        if (!empty($category)) {
            $wherearray['sport'] = $category;
        };

        $table->use_pages = false;

        $table->define_cache('mod_booking', 'bookingoptionstable');

        $table->add_subcolumns('itemcategory', ['sport']);
        $table->add_subcolumns('itemday', ['dayofweektime']);
        $table->add_subcolumns('cardimage', ['image']);
        $table->add_subcolumns('optioninvisible', ['invisibleoption']);

        $table->add_subcolumns('cardbody', ['invisibleoption', 'sport', 'text', 'teacher']);
        $table->add_classes_to_subcolumns('cardbody', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns(
            'cardbody',
            ['columnvalueclass' => 'shortcodes_option_info_invisible'],
            ['invisibleoption']
        );
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h6'], ['sports']);
        $table->add_classes_to_subcolumns('cardbody', ['columnvalueclass' => 'h5'], ['text']);

        $table->add_subcolumns('cardlist', ['dayofweektime', 'location', 'bookings', 'botags']);
        $table->add_classes_to_subcolumns('cardlist', ['columnkeyclass' => 'd-none']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-map-marker'], ['location']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-clock-o'], ['dayofweektime']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-users'], ['bookings']);
        $table->add_classes_to_subcolumns('cardlist', ['columniclassbefore' => 'fa fa-tag'], ['botags']);

        $table->add_subcolumns('cardfooter', ['price']);
        $table->add_classes_to_subcolumns('cardfooter', ['columnkeyclass' => 'd-none']);

        $table->set_tableclass('cardimageclass', 'w-100');

        $table->is_downloading('', 'List of booking options');

        // Id is not really something one wants to filter, but we need the dataset on the html element.
        // The key "id" won't be rendered in filter json, though.
        if ($showfilter !== false) {
            $table->define_filtercolumns([
                'id', 'sport' => [
                    'localizedname' => get_string('sport', 'local_musi')
                ], 'dayofweek' => [
                    'localizedname' => get_string('dayofweek', 'local_musi'),
                    'monday' => get_string('monday', 'mod_booking'),
                    'tuesday' => get_string('tuesday', 'mod_booking'),
                    'wednesday' => get_string('wednesday', 'mod_booking'),
                    'thursday' => get_string('thursday', 'mod_booking'),
                    'friday' => get_string('friday', 'mod_booking'),
                    'saturday' => get_string('saturday', 'mod_booking'),
                    'sunday' => get_string('sunday', 'mod_booking')
                ],  'location' => [
                    'localizedname' => get_string('location', 'mod_booking')
                ],  'botags' => [
                    'localizedname' => get_string('tags', 'core')
                ]
            ]);
        }

        if ($showsearch !== false) {
            $table->define_fulltextsearchcolumns(['titleprefix', 'text', 'sport', 'description', 'location', 'teacherobjects']);
        }

        if ($showsort !== false) {
            $table->define_sortablecolumns([
                'text' => get_string('coursename', 'local_musi'),
                'sport' => get_string('sport', 'local_musi'),
                'location',
                'dayofweek'
            ]);
        } else {
            $table->sortable(true, 'text');
        }

        // It's important to have the baseurl defined, we use it as a return url at one point.
        $baseurl = new moodle_url(
            $_SERVER['REQUEST_URI'],
            $_GET
        );

        $table->define_baseurl($baseurl->out());

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 100;

        // This allows us to use infinite scrolling, No pages will be used.
        $table->infinitescroll = 100;

        $table->tabletemplate = 'local_musi/nolazytable';

        return [$table, $booking, $category];
    }
}
