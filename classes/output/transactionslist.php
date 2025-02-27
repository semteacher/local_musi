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
 * This file contains the definition for the renderable classes for transactions list
 *
 * @package   local_musi
 * @copyright 2023 Christian Badusch {@link http://www.wunderbyte.at}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_musi\output;

use local_musi\table\musi_transactions_table;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * This class prepares to data to render transactionstable in mustache template
 */
class transactionslist implements renderable, templatable {
    private $tabledata = [];

    public function __construct() {
        global $DB;

        // Create instance of transactions wb_table and specify columns and headers.
        $table = new musi_transactions_table('musi_transactions_table');

        // Headers.
        $table->define_headers([get_string('id', 'local_musi'), get_string('transactionid', 'local_musi'),
        get_string('itemid', 'local_musi'), get_string('username', 'local_musi'),
        get_string('price', 'local_musi'), get_string('status', 'local_musi'), get_string('names', 'local_musi'),
        get_string('action', 'local_musi')]);

        // Columns.
        $table->define_columns(['id', 'tid', 'itemid', 'username', 'price', 'status', 'names', 'action']);

        // Pass SQL to table.
        // TODO: Add functionality for other providers.
        list($fields, $from, $where) = self::return_payunity_sql_transaction();
        $table->set_filter_sql($fields, $from, $where, '');

        $table->sortable(true, 'id', SORT_ASC);

        // Define Filters.
        $table->define_filtercolumns([
            'status' => [
                'localizedname' => get_string('status', 'local_musi'),
                '0' => get_string('openorder', 'local_musi'),
                '3' => get_string('bookedorder', 'local_musi'),
            ]
        ]);

        // Full text search columns.
        $table->define_fulltextsearchcolumns(['id', 'tid', 'itemid', 'username', 'price', 'status', 'names']);

        // Sortable columns.
        $table->define_sortablecolumns(['id', 'tid', 'itemid', 'username', 'price', 'status', 'names']);

        $table->define_cache('local_musi', 'cachedpaymenttable');

        // Pass html to render.
        list($idstring, $encodedtable, $html) = $table->lazyouthtml(20, true);
        $this->tabledata = $html;
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        // Not lazy laod : $this->tabledata = $table->outhtml(20, true).

    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->table = $this->tabledata;
        return $data;
    }


    /**
     * Return SQL Query in correct format for wb_table
     *
     * @return array
     */
    private static function return_payunity_sql_transaction():array {
        global $DB;

        // TODO: Check database exists and / or loop over payment providers!
        // DB table_exists.
        $gatewaynames = ['payunity'];
        // TODO: check for open orders tables in all gateways.
        // TODO: use all gateway tables.
        $concatsql = $DB->sql_group_concat("so.itemname", "<br>", "so.itemname");
        $concatusername = $DB->sql_fullname("u.lastname", "u.firstname");
        $fields = '*';
        $from = "(SELECT oo.*, $concatusername AS username, $concatsql AS names FROM
            {paygw_payunity_openorders} oo
            LEFT JOIN {local_shopping_cart_history} so
            ON oo.itemid = so.identifier AND oo.userid=so.userid
            LEFT JOIN {user} u
            ON u.id = oo.userid
            GROUP BY oo.id, u.firstname, u.lastname
            ) as s1 ";

        $where = "1 = 1";

        return [$fields, $from, $where];
    }

}
