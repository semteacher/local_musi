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
 * Functions for SAP text files (daily SAP sums for M:USI).
 *
 * @package local_musi
 * @since Moodle 4.0
 * @copyright 2022 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Bernhard Fischer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_musi;

/**
 * Deals with local_shortcodes regarding booking.
 */
class sap_daily_sums {
    /**
     * Helper function to create SAP text files for M:USI.
     *
     * @param string $day formatted day to generate the SAP text file for
     * @return string the file content
     */
    public static function generate_sap_text_file_for_date(string $day) {
        global $DB;

        $startofday = strtotime($day . ' 00:00');
        $endofday = strtotime($day . ' 24:00');

        // Get payment account from settings.
        $accountid = get_config('local_shopping_cart', 'accountid');
        $account = null;
        if (!empty($accountid)) {
            $account = new \core_payment\account($accountid);
        }

        // Create selects for each payment gateway.
        $colselects = [];
        // Create an array of table names for the payment gateways.
        if (!empty($account)) {
            foreach ($account->get_gateways() as $gateway) {
                $gwname = $gateway->get('gateway');
                if ($gateway->get('enabled')) {
                    $tablename = "paygw_" . $gwname;

                    $cols = $DB->get_columns($tablename);
                    // Only use tables with exactly 5 columns: id, paymentid, <pgw>_orderid, paymentbrand, pboriginal.
                    if (count($cols) <> 5) {
                        continue;
                    }

                    // Generate a select for each table.
                    // Only do this, if an orderid exists.
                    foreach ($cols as $key => $value) {
                        if (strpos($key, 'orderid') !== false) {
                            $colselects[] =
                                "SELECT $gwname.paymentid, $gwname.$key orderid, paymentbrand
                                FROM {paygw_$gwname} $gwname";
                        }
                    }
                }
            }
        }

        $selectorderidpart = "";
        if (!empty($colselects)) {
            $selectorderidpart = ", pgw.orderid, pgw.paymentbrand";
            $colselectsstring = implode(' UNION ', $colselects);
            $gatewayspart = "LEFT JOIN ($colselectsstring) pgw ON p.id = pgw.paymentid";
        }

        // SQL query. The subselect will fix the "Did you remember to make the first column something...
        // ...unique in your call to get_records?" bug.
        $sql = "SELECT scl.id, scl.identifier, scl.price, scl.discount, scl.credits, scl.fee, scl.currency,
                u.lastname, u.firstname, u.email, scl.itemid, scl.itemname, scl.payment, scl.paymentstatus, " .
                $DB->sql_concat("um.firstname", "' '", "um.lastname") . " as usermodified, scl.timecreated, scl.timemodified,
                p.gateway$selectorderidpart
                FROM {local_shopping_cart_ledger} scl
                LEFT JOIN {user} u
                ON u.id = scl.userid
                LEFT JOIN {user} um
                ON um.id = scl.usermodified
                LEFT JOIN {payments} p
                ON p.itemid = scl.identifier
                $gatewayspart
                WHERE scl.timecreated BETWEEN :startofday AND :endofday
                AND scl.paymentstatus = :paymentsuccess
                AND scl.itemid <> 0";

        $params = [
            'startofday' => $startofday,
            'endofday' => $endofday,
            'paymentsuccess' => PAYMENT_SUCCESS
        ];

        $content = '';
        if ($records = $DB->get_records_sql($sql, $params)) {
            foreach ($records as $record) {
                // We only want to log online payments.
                // We ignore payments at the cashier's desk.
                if ($record->payment != PAYMENT_METHOD_ONLINE) {
                    continue;
                }
                /*
                 * Mandant - 3 Stellen alphanumerisch - immer "101"
                 * Buchungskreis - 4 Stellen alphanumerisch - immer "VIE1"
                 * Währung - 3 Stellen alphanumerisch - immer "EUR"
                 * Belegart - 2 Stellen alphanumerisch - Immer "DR"
                 */
                $content .= "101#VIE1#EUR#DR#";
                // Referenzbelegnummer - 16 Stellen alphanumerisch.
                // TODO: Muss der Identifier aus der merchantTransactionId geholt werden?
                // TODO: Falls ja, wie kommt der (richtige) Identifier in die merchantTransactionId???
                $content .= str_pad($record->identifier, 16, " ", STR_PAD_LEFT) . '#';
                // Buchungsdatum - 10 Stellen.
                $content .= date('d.m.Y', $record->timemodified) . '#';
                // Belegdatum - 10 Stellen.
                $content .= date('d.m.Y', $record->timecreated) . '#';
                // Belegkopftext - 25 Stellen alphanumerisch - in unserem Fall immer "US".
                $content .= str_pad('US', 25, " ", STR_PAD_LEFT) . '#';
                // Betrag - 14 Stellen alphanumerisch - Netto-Betrag.
                $renderedprice = (string) $record->price;
                $renderedprice = str_replace('.', ',', $renderedprice);
                $content .= str_pad($renderedprice, 14, " ", STR_PAD_LEFT) . '#';
                // Steuer rechnen - 1 Stelle alphanumerisch - immer "X".
                $content .= 'X#';
                /* Steuerbetrag - 14 Stellen alphanumerisch - derzeit sind keine Geschäftsfälle mit Umsatzsteuer vorgesehen,
                daher bleibt das Feld immer leer. */
                $content .= str_pad('', 14, " ") . '#';
                /* Werbeabgabe - 14 Stellen alphanumerisch - kein bekannter Geschäftsfall im Moment,
                daher bleibt das Feld immer leer. */
                $content .= str_pad('', 14, " ") . '#';
                // Buchungsschlüssel - 2 Stellen alphanumerisch - 50 - bei Rechnungen, 40 - bei Gutschriften
                // Derzeit können nur Rechnungen geloggt werden, daher immer 50.
                $content .= '50#';
                // Geschäftsfall-Code - 3 Stellen alphanumerisch - immer "US0".
                $content .= 'US0#';
                // Zahlungscode - 3 Stellen alphanumerisch.
                if (!empty($record->paymentbrand)) {
                    $content .= str_pad($record->paymentbrand, 3, " ", STR_PAD_LEFT) . '#';
                } else {
                    $content .= str_pad('', 3, " ", STR_PAD_LEFT) . '#';
                }
                // Zeilenumbruch.
                $content .= "\r\n";
            }
        }

        return $content;
    }
}
