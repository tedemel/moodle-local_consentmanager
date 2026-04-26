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
 * Consent banner AMD module.
 *
 * Responsibilities:
 * - Show/hide the banner
 * - Handle "Accept all", "Essential only", "Save selection"
 * - Toggle simple ↔ detail view
 * - Trap focus inside the modal when open
 * - Call the web service via core/ajax
 *
 * @module    local_consentmanager/banner
 * @copyright 2026 Tessa Demel
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Pending from 'core/pending';


let bannerEl    = null;
let config      = {};
let eventsbound = false;
let previewMode = false;

/**
 * Initialise the banner module. Called from PHP via $PAGE->requires->js_call_amd().
 *
 * @param {Object} cfg Plugin configuration from PHP
 * @param {string} cfg.wwwroot
 * @param {string} cfg.sesskey
 * @param {number} cfg.revision
 */
export const init = async(cfg) => {
    const pending = new Pending('local_consentmanager/banner/init');
    config = cfg;

    bannerEl = document.getElementById('local-consentmanager-banner');
    if (!bannerEl) {
        pending.resolve();
        return;
    }

    // needs_consent flag is supplied by PHP — no AJAX round-trip required.
    if (!cfg.needsconsent) {
        pending.resolve();
        return;
    }

    showBanner();
    bindEvents();
    pending.resolve();
};

// ---------------------------------------------------------------------------
// Banner visibility
// ---------------------------------------------------------------------------

const showBanner = () => {
    bannerEl.style.display = 'flex';
    bannerEl.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    // Move focus to the first focusable element inside the dialog.
    const firstFocusable = bannerEl.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (firstFocusable) {
        firstFocusable.focus();
    }
};

const hideBanner = () => {
    bannerEl.style.display = 'none';
    bannerEl.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};

// ---------------------------------------------------------------------------
// Event binding
// ---------------------------------------------------------------------------

const bindEvents = () => {
    if (eventsbound) {
        return;
    }
    eventsbound = true;
    // Accept all.
    bannerEl.querySelectorAll('[data-action="acceptall"]').forEach(btn => {
        btn.addEventListener('click', handleAcceptAll);
    });

    // Essential only.
    bannerEl.querySelectorAll('[data-action="acceptessential"]').forEach(btn => {
        btn.addEventListener('click', handleEssentialOnly);
    });

    // Show settings.
    bannerEl.querySelectorAll('[data-action="showdetails"]').forEach(btn => {
        btn.addEventListener('click', showDetails);
    });

    // Hide settings.
    bannerEl.querySelectorAll('[data-action="hidesettings"]').forEach(btn => {
        btn.addEventListener('click', hideDetails);
    });

    // Save selection.
    bannerEl.querySelectorAll('[data-action="saveselection"]').forEach(btn => {
        btn.addEventListener('click', handleSaveSelection);
    });

    // Focus trap.
    bannerEl.addEventListener('keydown', handleKeydown);
};

// ---------------------------------------------------------------------------
// Simple ↔ Detail view
// ---------------------------------------------------------------------------

const showDetails = () => {
    const simple  = bannerEl.querySelector('.consentmanager-simple');
    const details = bannerEl.querySelector('.consentmanager-details');
    if (simple)  { simple.setAttribute('aria-hidden', 'true'); simple.style.display = 'none'; }
    if (details) { details.removeAttribute('aria-hidden'); details.style.display = 'block'; }
    const showBtn = bannerEl.querySelector('[data-action="showdetails"]');
    if (showBtn) { showBtn.setAttribute('aria-expanded', 'true'); }
};

const hideDetails = () => {
    const simple  = bannerEl.querySelector('.consentmanager-simple');
    const details = bannerEl.querySelector('.consentmanager-details');
    if (simple)  { simple.removeAttribute('aria-hidden'); simple.style.display = ''; }
    if (details) { details.setAttribute('aria-hidden', 'true'); details.style.display = 'none'; }
    const showBtn = bannerEl.querySelector('[data-action="showdetails"]');
    if (showBtn) { showBtn.setAttribute('aria-expanded', 'false'); }
};

// ---------------------------------------------------------------------------
// Consent actions
// ---------------------------------------------------------------------------

const handleAcceptAll = async() => {
    await sendConsent([], true);
};

const handleEssentialOnly = async() => {
    await sendConsent([], false);
};

const handleSaveSelection = async() => {
    const checked = Array.from(
        bannerEl.querySelectorAll('.consentmanager-toggle:checked:not(:disabled)')
    ).map(el => parseInt(el.dataset.catid, 10));
    await sendConsent(checked, false);
};

const sendConsent = async(catids, acceptall) => {
    if (previewMode) {
        hideBanner();
        previewMode = false;
        return;
    }
    const pending = new Pending('local_consentmanager/banner/sendConsent');
    try {
        await Ajax.call([{
            methodname: 'local_consentmanager_set_consent',
            args: {catids, acceptall, guesttoken: config.guesttoken || ''},
        }], true, false)[0];
        hideBanner();
        // Reload to display previously blocked iframes.
        window.location.reload();
    } catch (e) {
        Notification.exception(e);
    } finally {
        pending.resolve();
    }
};

// ---------------------------------------------------------------------------
// Admin preview
// ---------------------------------------------------------------------------

/**
 * Bind the admin preview button found on the dashboard page.
 *
 * Shows the banner without triggering consent storage or AJAX checks.
 * Called via $PAGE->requires->js_call_amd on dashboard.php.
 */
export const initPreviewButton = (cfg = {}) => {
    const btn = document.getElementById('local-consentmanager-preview-btn');
    if (!btn) {
        return;
    }
    btn.addEventListener('click', () => {
        bannerEl = document.getElementById('local-consentmanager-banner');
        if (!bannerEl) {
            return;
        }
        previewMode = true;
        hideDetails();

        // Inject a close button the first time, re-use on subsequent previews.
        const dialog = bannerEl.querySelector('.local-consentmanager-dialog');
        if (dialog && !dialog.querySelector('.consentmanager-preview-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'btn btn-sm btn-outline-secondary consentmanager-preview-close';
            closeBtn.setAttribute('style', 'position:absolute;top:0.75rem;right:0.75rem;z-index:10');
            closeBtn.textContent = cfg.closelabel || '✕';
            closeBtn.addEventListener('click', () => {
                hideBanner();
                previewMode = false;
            });
            dialog.style.position = 'relative';
            dialog.prepend(closeBtn);
        }

        showBanner();
        bindEvents();
    });
};

// ---------------------------------------------------------------------------
// Accessibility: focus trap
// ---------------------------------------------------------------------------

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        if (previewMode) {
            hideBanner();
            previewMode = false;
            return;
        }
        handleEssentialOnly();
        return;
    }
    if (e.key !== 'Tab') {
        return;
    }
    const focusable = Array.from(
        bannerEl.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')
    ).filter(el => el.offsetParent !== null); // Only visible.

    if (!focusable.length) { return; }
    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (e.shiftKey) {
        if (document.activeElement === first) { e.preventDefault(); last.focus(); }
    } else {
        if (document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
};
