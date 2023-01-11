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
 * An overview of all courses the currently logged in user
 * either teacher or has booked.
 *
 * @package local_musi
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Bernhard Fischer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// No guest autologin.
require_login(0, false);

global $DB, $PAGE, $OUTPUT, $USER;

if (!$context = context_system::instance()) {
    throw new moodle_exception('badcontext');
}

// Check if optionid is valid.
$PAGE->set_context($context);

$title = get_string('mycourses', 'local_musi');

$PAGE->set_url('/local/musi/meinekurse.php');
$PAGE->navbar->add($title);
$PAGE->set_title(format_string($title));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('local_musi-meinekurse');

echo $OUTPUT->header();

echo "<div class='text-center h1'>$title</div>";
echo "<hr class='w-100 border border-light'/>";

if ($DB->get_records('booking_teachers', ['userid' => $USER->id])) {
    echo html_writer::div(get_string('coursesiteach', 'local_musi'), 'h2 mt-2 mb-2 text-center');
    echo format_text("[trainerkursekarten filter=1 search=1]", FORMAT_HTML);
    echo "<hr class='w-100 border border-light'/>";
}

echo html_writer::div(get_string('coursesibooked', 'local_musi'), 'h2 mt-2 mb-2 text-center');
echo format_text("[meinekursekarten filter=1 search=1]", FORMAT_HTML);

echo $OUTPUT->footer();
