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
 * Event observers.
 *
 * @package     local_musi
 * @copyright   2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author      Bernhard Fischer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_musi;

use cache_helper;
use local_shopping_cart\event\payment_rebooked;
use stdClass;

/**
 * Event observer for local_musi.
 */
class observer {

    /**
     * Observer for the payment_added event
     */
    public static function payment_added() {
        cache_helper::purge_by_event('setbackcachedpaymenttable');
    }

    /**
     * Observer for the payment_completed event
     */
    public static function payment_completed() {
        cache_helper::purge_by_event('setbackcachedpaymenttable');
    }

    /**
     * Observer for the payment_rebooked event
     */
    public static function payment_rebooked(payment_rebooked $event) {
        global $DB;

        $identifier = $event->other['identifier'];
        $userid = $event->other['userid'];
        $usermodified = $event->other['usermodified'];
        $annotation = $event->other['annotation'];

        // TODO let's continue here tomorrow...

        /*$orderidelements = explode(' ', $annotation);

        // Some security checks.
        if (count($orderidelements) < 6) {
            return;
        } else if (substr($orderidelements[2], 0, 1) != "K") {
            return;
        }

        $timecreated = (int) $orderidelements[count($orderidelements) - 1];
        $amount = (float) $orderidelements[count($orderidelements) - 2];
        $accountid = $DB->get_field('payment_gateways', 'accountid', ['gateway' => 'payunity', 'enabled' => '1']);

        // 1000105 16 K327 79.00 82.00 1686577767

        $paymentrecord = new stdClass;
        $paymentrecord->component = 'local_shopping_cart';
        $paymentrecord->paymentarea = null;
        $paymentrecord->itemid = $identifier;
        $paymentrecord->userid = $userid;
        $paymentrecord->amount = $amount;
        $paymentrecord->currency = 'EUR';
        $paymentrecord->accountid = $accountid;
        $paymentrecord->gateway = 'payunity';
        $paymentrecord->timecreated = $timecreated;
        $paymentrecord->timemodified = time();

        $payunityrecord = new stdClass;

        $ordersrecord = new stdClass;*/

        /* Important notice: We store the correct identifier in tables so we do not corrupt table consistency.
        But the identifier which is part of the order id will be a different one! */

        // TODO: from here, we can start writing into the tables...

        return;
    }
}
