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

/* eslint-disable no-console */

/*
 * @package    local_musi
 * @author     Bernhard Fischer
 * @copyright  2022 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Modal form to manually rebook users and gather data for payment tables.
 *
 * @module     local_musi/rebookmodal
 * @copyright  2023 Wunderbyte GmbH
 * @author     Bernhard Fischer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';

/**
 * Modal to enter OrderID for manual rebookings.
 * @param {int} identifier
 * @param {int} userid
 * @param {int} usermodified
 */
export const init = (identifier, userid, usermodified) => {

    console.log('rebookOrderidModal');

    const modalForm = new ModalForm({

        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: "local_shopping_cart\\form\\modal_cashier_manual_rebook",
        // Add as many arguments as you need, they will be passed to the form:
        args: {
            'identifier': identifier,
            'userid': userid,
            'usermodified': usermodified,
        },
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('orderid_rebook_desc', 'local_shopping_cart')},
        // DOM element that should get the focus after the modal dialogue is closed:
        // returnFocus: button
    });
    // Listen to events if you want to execute something on form submit.
    // Event detail will contain everything the process() function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => {
        const response = e.detail;
        // eslint-disable-next-line no-console
        console.log('rebookOrderidModal response: ', response);
    });

    // Show the form.
    modalForm.show();
};
