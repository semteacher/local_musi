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
 * Moodle hooks for local_musi
 * @package    local_musi
 * @copyright  2022 Wunderbyte GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Adds module specific settings to the settings block
 *
 * @param navigation_node $modnode The node to add module settings to
 * @return void
 */
function local_musi_extend_navigation(navigation_node $navigation) {
    $context = context_system::instance();
    if (has_capability('local/musi:canedit', $context)) {
        $nodehome = $navigation->get('home');
        if (empty($nodehome)) {
            $nodehome = $navigation;
        }
        $pluginname = get_string('pluginname', 'local_musi');
        $link = new moodle_url('/local/musi/dashboard.php', array());
        $icon = new pix_icon('i/dashboard', $pluginname, 'local_musi');
        $nodecreatecourse = $nodehome->add($pluginname, $link, navigation_node::NODETYPE_LEAF, $pluginname, 'musi_editor', $icon);
        $nodecreatecourse->showinflatnavigation = true;
    }
}

/**
 * Get icon mapping for font-awesome.
 *
 * @return  array
 */
function local_musi_get_fontawesome_icon_map() {
    return [
        'local_musi:i/dashboard' => 'fa-tachometer'
    ];
}