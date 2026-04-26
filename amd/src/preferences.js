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
 * User preferences page JS.
 *
 * @module    local_consentmanager/preferences
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Pending from 'core/pending';
import {getString} from 'core/str';

/**
 * Initialise the preferences page.
 */
export const init = () => {
    document.querySelectorAll('.consentmanager-withdraw').forEach(btn => {
        btn.addEventListener('click', handleWithdraw);
    });
    document.querySelectorAll('.consentmanager-give').forEach(btn => {
        btn.addEventListener('click', handleGive);
    });
};

const handleWithdraw = async(e) => {
    const btn = e.currentTarget;
    const catid = parseInt(btn.dataset.catid, 10);
    const label = await getString('btn_withdraw_confirm', 'local_consentmanager');
    // eslint-disable-next-line no-alert
    if (!window.confirm(label)) {
        return;
    }

    const pending = new Pending('local_consentmanager/preferences/withdraw');
    btn.disabled = true;
    try {
        const [result] = await Ajax.call([{
            methodname: 'local_consentmanager_withdraw_consent',
            args: {catid},
        }]);
        if (result.success) {
            window.location.reload();
        } else {
            Notification.alert('', result.message);
        }
    } catch (err) {
        Notification.exception(err);
    } finally {
        btn.disabled = false;
        pending.resolve();
    }
};

const handleGive = async(e) => {
    const btn = e.currentTarget;
    const catid = parseInt(btn.dataset.catid, 10);

    const pending = new Pending('local_consentmanager/preferences/give');
    btn.disabled = true;
    try {
        const [result] = await Ajax.call([{
            methodname: 'local_consentmanager_set_consent',
            args: {catids: [catid], acceptall: false},
        }]);
        if (result.success) {
            window.location.reload();
        } else {
            Notification.alert('', result.message);
        }
    } catch (err) {
        Notification.exception(err);
    } finally {
        btn.disabled = false;
        pending.resolve();
    }
};
