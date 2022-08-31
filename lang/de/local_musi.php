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
$string['messageprovider:sendmessages'] = 'Verschicke Nachrichten';
$string['musi:cansendmessages'] = 'Kann Nachrichten schicken.';
$string['pluginname'] = 'M:USI Dashboard';

$string['shortcodeslistofbookingoptions'] = 'Alle Kurse als Liste';
$string['shortcodeslistofbookingoptionsascards'] = 'Alle Kurse als Karten';
$string['shortcodeslistofmybookingoptionsascards'] = 'Meine Kurse als Karten';
$string['shortcodeslistofteachersascards'] = 'Liste aller Trainer als Karten';

// List of all courses.
$string['allcourses'] = 'Alle Kurse';

// Cards.
$string['listofsports'] = 'Liste der Sportarten';
$string['listofsports_desc'] = 'Zeige und editiere die Liste der Sportarten auf diesem System.';

$string['numberofcourses'] = 'Anzahl der Kurse';
$string['numberofcourses_desc'] = 'Informationen über die Kurse und Buchungen auf der Plattform.';

$string['numberofentities'] = 'Anzahl der Sportstätten';
$string['numberofentities_desc'] = 'Informationen über die Sportstätten auf der Plattform.';

$string['coursesbooked'] = 'Gebuchte Kurse';
$string['coursesincart'] = 'Im Warenkorb';
$string['coursesdeleted'] = 'Gelöschte Kurse';
$string['coursesboughtcard'] = 'Gekaufte Kurse (Online)';
$string['coursespending'] = 'Noch unbestätigte Kurse';
$string['coursesboughtcashier'] = 'Gekaufte Kurse (Kassa)';

$string['editsettings'] = 'Einstellungen bearbeiten';
$string['editentities'] = 'Sportstätten bearbeiten';
$string['editentitiescategories'] = 'Kategorien der Sportstätten bearbeiten';
$string['editbookinginstance'] = 'Semester-Instanz bearbeiten';
$string['editbookings'] = 'Booking-Übersicht';
$string['viewteachers'] = 'Trainer*innen-Übersicht';

$string['addbookinginstance'] = '<span class="bg-danger font-weight-bold">Keine Semester-Instanz! Hier klicken, um eine einzustellen.</span>';
$string['editpricecategories'] = 'Preiskategorien bearbeiten';
$string['editsemesters'] = 'Semester bearbeiten';
$string['changebookinginstance'] = 'Semester-Instanz ändern';
$string['editbotags'] = 'Tags verwalten';
$string['createbotag'] = 'Neuen Tag anlegen...';
$string['createbotag:helptext'] = '<p>
<a data-toggle="collapse" href="#collapseTagsHelptext" role="button" aria-expanded="false" aria-controls="collapseTagsHelptext">
  <i class="fa fa-question-circle" aria-hidden="true"></i><span>&nbsp;Hilfe: So können Sie Tags konfigurieren...</span>
</a>
</p>
<div class="collapse" id="collapseTagsHelptext">
<div class="card card-body">
  <p>Damit Sie Tags verwenden können, müssen Sie ein <a href="/mod/booking/customfield.php" target="_blank">"Benutzerdefiniertes Buchungsoptionsfeld"</a> vom Typ "Dynamic Dropdown menu" mit folgenden Einstellungen anlegen:</p>
  <ul>
  <li><strong>Kategorie: </strong>Tags</li>
  <li><strong>Name: </strong>Tags</li>
  <li><strong>Kurzname: </strong>botags</li>
  <li><strong>SQL query: </strong><code>SELECT botag as id, botag as data FROM {local_musi_botags}</code></li>
  <li><strong>Auto-complete: </strong><span class="text-success">aktiviert</span></li>
  <li><strong>Multi select: </strong><span class="text-success">aktiviert</span></li>
  </ul>
  <p>Nun können Sie die hier angelegten Tags den Buchungsoptionen zuordnen.<br>Sie müssen hier mindestens einen Tag angelegt haben, damit Sie Tagging verwenden können.</p>
</div>
</div>';

// Edit sports.
$string['editsports'] = 'Sportarten bearbeiten';

// Teachers.
$string['teacher'] = 'Kursleiter*in';
$string['allteachers'] = 'Alle Kursleiter*innen';
$string['showallteachers'] = '&gt;&gt; Alle Kursleiter*innen anzeigen';
$string['showcoursesofteacher'] = 'Kurse';
$string['messagebutton'] = 'Nachricht';
$string['messagingteacherimpossible'] = 'Sie können dieser Trainerin / diesem Trainer keine Nachrichten senden,
 weil Sie in keinen Kurs von ihr/ihm eingeschrieben sind.';
$string['sendmail'] = 'Mail';
$string['teachernotfound'] = 'Kursleiter*in konnte nicht gefunden werden oder existiert nicht.';
$string['notateacher'] = 'Die ausgewählte Person unterrichtet keine buchbaren Kurse und kann daher nicht angezeigt werden.';

// Shortcodes.
$string['shortcodeslistofbookingoptions'] = 'Liste der buchbaren Kurse';
$string['shortcodeslistofbookingoptionsascards'] = 'Liste der buchbaren Kurse als Karten';
$string['shortcodeslistofmybookingoptionsascards'] = 'Liste meiner gebuchte Kurse als Karten';
$string['shortcodessetdefaultinstance'] = 'Setze eine Standard-Instanz für Shortcodes';
$string['shortcodessetdefaultinstancedesc'] = 'Damit kann eine Standard-Buchungsinstanz definiert werden, die dann verwendet wird,
wenn keine ID definiert wurde. Dies erlaubt den schnellen Wechsel (zum Beispiel von einem Semster zum nächsten), wenn es Überblicks-
Seiten für unterschiedliche Kategorien gibt.';
$string['shortcodessetinstance'] = 'Definiere die Moodle-ID jener Buchungsinstanz, die standardmäßig verwendet werden soll.';
$string['shortcodessetinstancedesc'] = 'Wenn Du hier einen Wert setzt, kann der Shortcode so verwendet werden: [listofbookings category="philosophy"]
Es ist also nicht mehr nötig, eine ID zu übergeben.';
$string['courses'] = 'Kurse';

// Access.php.
$string['musi:canedit'] = 'Nutzer_in darf verwalten';

// Nav.
$string['musi'] = 'MUSI';
$string['cachier'] = 'Kassa';
$string['entities'] = 'Sportstätten';

// Filter.
$string['dayofweek'] = "Wochentag";
$string['sport'] = "Sportart";
$string['coursename'] = "Kursname";
