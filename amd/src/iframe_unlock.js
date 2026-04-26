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
 * Click-to-load handler for blocked iframes.
 *
 * When a user clicks the "Accept and show" button on an iframe placeholder,
 * this module:
 * 1. Stores consent for the required category via web service.
 * 2. Replaces the placeholder with a real <iframe>.
 *
 * @module    local_consentmanager/iframe_unlock
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Pending from 'core/pending';

/**
 * Attach click handlers to all existing placeholders and use event delegation
 * for any that might be added dynamically.
 */
export const init = () => {
    document.addEventListener('click', async(e) => {
        const btn = e.target.closest('.consentmanager-unlock-iframe');
        if (!btn) { return; }

        const catid = parseInt(btn.dataset.catid, 10);
        const src   = btn.dataset.src;
        if (!catid || !src) { return; }

        const placeholder = btn.closest('.local-consentmanager-placeholder');
        if (!placeholder) { return; }

        const pending = new Pending('local_consentmanager/iframe_unlock/click');
        btn.disabled = true;

        try {
            const [result] = await Ajax.call([{
                methodname: 'local_consentmanager_set_consent',
                args: {catids: [catid], acceptall: false},
            }]);

            if (!result.success) {
                Notification.alert('', result.message);
                btn.disabled = false;
                pending.resolve();
                return;
            }

            // Build a real iframe to replace the placeholder.
            const iframe = document.createElement('iframe');
            iframe.src             = src;
            iframe.width           = '100%';
            iframe.height          = placeholder.offsetHeight || 315;
            iframe.style.border    = 'none';
            iframe.allow           = 'autoplay; encrypted-media; picture-in-picture';
            iframe.allowFullscreen = true;
            iframe.loading         = 'lazy';

            placeholder.replaceWith(iframe);
        } catch (err) {
            Notification.exception(err);
            btn.disabled = false;
        } finally {
            pending.resolve();
        }
    });
};
