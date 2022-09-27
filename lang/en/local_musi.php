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
 * Plugin strings are defined here.
 *
 * @package     local_musi
 * @category    string
 * @copyright   2022 Wunderbyte Gmbh <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['dashboard'] = 'Dashboard';
$string['pluginname'] = 'M:USI Dashboard';

$string['shortcodeslistofbookingoptions'] = 'All courses as list';
$string['shortcodeslistofbookingoptionsascards'] = 'All courses as cards';
$string['shortcodeslistofmybookingoptionsascards'] = 'My courses as cards';
$string['shortcodeslistofteachersascards'] = 'List of teachers as cards';

// List of all courses.
$string['allcourses'] = 'All courses';

// Cards.
$string['listofsports'] = 'List of sports';
$string['listofsports_desc'] = 'View and edit the list of sports on this system';

$string['numberofcourses'] = 'Number of courses';
$string['numberofcourses_desc'] = 'Information about courses and bookings on this platform.';

$string['numberofentities'] = 'Number of entities';
$string['numberofentities_desc'] = 'Information about the sport facilities on the platform.';

$string['coursesbooked'] = 'Courses booked';
$string['coursesincart'] = 'Courses in shopping cart';
$string['coursesdeleted'] = 'Deleted courses';
$string['coursesboughtcard'] = 'Courses bought online';
$string['coursespending'] = 'Courses pending';
$string['coursesboughtcashier'] = 'Courses bought at cashier';

$string['editsettings'] = 'Edit settings';
$string['editentities'] = 'Edit entities';
$string['editentitiescategories'] = 'Edit categories of entities';
$string['editbookinginstance'] = 'Edit semester instance';
$string['editbookings'] = 'Booking overview';
$string['viewteachers'] = 'Trainer overview';
$string['sapdailysums'] = 'SAP booking files';

$string['addbookinginstance'] = '<span class="bg-danger font-weight-bold">No semester instance! Click here to choose one.</span>';
$string['editpricecategories'] = 'Edit price categories';
$string['editsemesters'] = 'Edit semesters';
$string['changebookinginstance'] = 'Change semester instance';
$string['editbotags'] = 'Edit tags';
$string['createbotag'] = 'Create new tag...';
$string['createbotag:helptext'] = '<p>
<a data-toggle="collapse" href="#collapseTagsHelptext" role="button" aria-expanded="false" aria-controls="collapseTagsHelptext">
  <i class="fa fa-question-circle" aria-hidden="true"></i><span>&nbsp;Help: How to configure tags...</span>
</a>
</p>
<div class="collapse" id="collapseTagsHelptext">
<div class="card card-body">
  <p>In order to use tags, you have to create a <a href="/mod/booking/customfield.php" target="_blank">"Booking customfield for booking options"</a> of the type "Dynamic Dropdown menu" which has the following settings:</p>
  <ul>
  <li><strong>Category: </strong>Tags</li>
  <li><strong>Name: </strong>Tags</li>
  <li><strong>Short name: </strong>botags</li>
  <li><strong>SQL query: </strong><code>SELECT botag as id, botag as data FROM {local_musi_botags}</code></li>
  <li><strong>Auto-complete: </strong><span class="text-success">active</span></li>
  <li><strong>Multi select: </strong><span class="text-success">active</span></li>
  </ul>
  <p>Now you can apply the tags you have created here to your booking options.<br>You need to have created at least one tag, in order to be able to use tagging.</p>
</div>
</div>';

// Edit sports.
$string['editsports'] = 'Edit sports';

// Teachers.
$string['teacher'] = 'Teacher';
$string['allteachers'] = 'All teachers';
$string['showallteachers'] = '&gt;&gt; Show all teachers';
$string['showcoursesofteacher'] = 'Courses';
$string['messagebutton'] = 'Message';
$string['messagingteacherimpossible'] = 'You cannot send messages to this teacher
 because you are not enrolled in any courses of her/him.';
$string['sendmail'] = 'Mail';
$string['teachernotfound'] = 'Teacher could not be found or does not exist.';
$string['notateacher'] = 'The user selected is not teaching any courses and is probably not a teacher.';

// Shortcodes.
$string['shortcodeslistofbookingoptions'] = 'List of booking options';
$string['shortcodeslistofbookingoptionsascards'] = 'List of booking options as cards';
$string['shortcodeslistofmybookingoptionsascards'] = 'List of my booked booking options as cards';
$string['shortcodessetdefaultinstance'] = 'Set default instance for shortcodes implementation';
$string['shortcodessetdefaultinstancedesc'] = 'This allows you to change instances quickly when you want to change
a lot of them at once. One example would be that you have a lot of teaching categories and they are listed on different
pages, but you need to change the booking options form one semester to the next.';
$string['shortcodessetinstance'] = 'Set the moodle ID of the booking instance which should be used by default';
$string['shortcodessetinstancedesc'] = 'If you use this setting, you can use the shortcode like this: [listofbookings category="philosophy"]
So no need to specify the ID';
$string['courses'] = 'Courses';

// Access.php.
$string['musi:canedit'] = 'User can edit';

// Filter.
$string['dayofweek'] = "Weekday";
$string['sport'] = "Sport";

// Nav.
$string['musi'] = 'MUSI';
$string['cachier'] = 'Cashiers desk';
$string['entities'] = 'Sport locations';
$string['coursename'] = "Coursename";
