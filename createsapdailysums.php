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
 * Add dates to option.
 *
 * @package local_musi
 * @copyright 2022 Bernhard Fischer <info@wunderbyte.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_musi\sap_daily_sums;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// No guest autologin.
require_login(0, false);

global $DB, $PAGE, $OUTPUT, $USER;

if (!$context = context_system::instance()) {
    throw new moodle_exception('badcontext');
}

// Check if optionid is valid.
$PAGE->set_context($context);

$title = 'SAP-Textdateien mit Tagessummen';

$PAGE->set_url('/local/musi/createsapdailysums.php');
$PAGE->navbar->add($title);
$PAGE->set_title(format_string($title));

$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

$now = time(); // Current timestamp.
$yesterday = strtotime('-1 day', $now);
$dateoneyearago = strtotime('-365 days', $now);

$fs = get_file_storage();

$contextid = $context->id;
$component = 'local_musi';
$filearea = 'musi_sap_dailysums';
$itemid = 0;
$filepath = '/';

$starttimestamp = $dateoneyearago;
while ($starttimestamp <= $yesterday) {
    $filename = 'SAP_USI_' . date('Ymd', $starttimestamp) . '.txt';

    // Retrieve the file from the Files API.
    $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename
        );

        $content = sap_daily_sums::generate_sap_text_file_for_date(date('Y-m-d', $starttimestamp));
        $fs->create_file_from_string($fileinfo, $content);
    }
    $starttimestamp = strtotime('+1 day', $starttimestamp);
}

// List all existing files as links.
$files = $fs->get_area_files($context->id, 'local_musi', 'musi_sap_dailysums');
foreach ($files as $file) {
    if ($file->get_filename() == '.') {
        continue;
    }

    $url = moodle_url::make_pluginfile_url(
        $contextid,
        $component,
        $filearea,
        $itemid,
        $filepath,
        $file->get_filename(),
        true // Force download of the file.
    );
    echo html_writer::link($url, $file->get_filename());
    echo '<br/>';

    // If we want to delete all files, we can use this line.
    // $file->delete(); // Workaround: delete files.
}

echo $OUTPUT->footer();
