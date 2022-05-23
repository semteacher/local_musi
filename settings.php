<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_musi
 * @category    admin
 * @copyright   2022 Wunderbyte Gmbh <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

     // TODO: Define the plugin settings page - {@link https://docs.moodle.org/dev/Admin_settings}.

     $settings = new admin_settingpage('Musi', get_string('pluginname', 'local_musi'));
     $ADMIN->add('localplugins', new admin_category('local_musi', get_string('pluginname', 'local_musi')));
     $ADMIN->add('local_musi', $settings);

     $settings->add(
         new admin_setting_heading('shortcodessetdefaultinstance',
             get_string('shortcodessetdefaultinstance', 'local_musi'),
             get_string('shortcodessetdefaultinstancedesc', 'local_musi')));

     $settings->add(
         new admin_setting_configtext('local_musi/shortcodessetinstance',
             get_string('shortcodessetinstance', 'local_musi'),
             get_string('shortcodessetinstancedesc', 'local_musi'),
             '', PARAM_INT));

    if ($ADMIN->fulltree) {

    }
}
