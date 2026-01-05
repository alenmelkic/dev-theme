/**
 * AdSense Admin JavaScript - Vanilla JavaScript (No jQuery)
 * Handles admin interface interactions for AdSense settings
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        // Handle collapsible sections (accordion functionality)
        const sectionToggles = document.querySelectorAll('.rvk-section-toggle');
        sectionToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                this.classList.toggle('collapsed');
                const content = this.nextElementSibling;
                if (content && content.classList.contains('rvk-section-content')) {
                    if (content.style.display === 'none') {
                        content.style.display = 'block';
                    } else {
                        content.style.display = 'none';
                    }
                }
            });
        });

        // Initialize all sections as expanded on first load
        const sectionContents = document.querySelectorAll('.rvk-section-content');
        sectionContents.forEach(function(content) {
            content.style.display = 'block';
        });

        // Validate Publisher ID format
        const publisherIdInput = document.querySelector('input[name="rvk_adsense_settings[publisher_id]"]');
        if (publisherIdInput) {
            publisherIdInput.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value && !value.startsWith('ca-pub-')) {
                    alert('Publisher ID mora počinjati sa "ca-pub-"');
                    this.focus();
                }
            });
        }

        // A/B Testing weight slider visual feedback
        const weightInputs = document.querySelectorAll('input[name*="[variant_a_weight]"]');
        weightInputs.forEach(function(input) {
            input.addEventListener('input', handleWeightInput);
            // Initialize weight displays
            handleWeightInput.call(input);
        });

        // Lazy load toggle explanation
        const lazyLoadInputs = document.querySelectorAll('input[name*="[lazy_load]"]');
        lazyLoadInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                const isChecked = this.checked;
                const message = isChecked
                    ? 'Lenjivo učitavanje omogućeno: Reklame će se učitati kada su vidljive, poboljšavajući brzinu stranice.'
                    : 'Lenjivo učitavanje onemogućeno: Reklame će se učitati odmah pri učitavanju stranice.';
                // console.log(message);
            });
        });

        // Form validation before submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }

        // Helper: Toggle responsive size inputs based on checkbox
        const responsiveCheckboxes = document.querySelectorAll('input[type="checkbox"][name*="[responsive]["][name*="][enabled]"]');
        responsiveCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', handleResponsiveToggle);
            // Trigger initial state
            handleResponsiveToggle.call(checkbox);
        });
    }

    function handleWeightInput() {
        const weight = this.value;
        const variantB = 100 - weight;

        // Remove existing display
        const existingDisplay = this.parentNode.querySelector('.weight-display');
        if (existingDisplay) {
            existingDisplay.remove();
        }

        // Add new display
        const display = document.createElement('span');
        display.className = 'weight-display';
        display.style.marginLeft = '10px';
        display.style.color = '#666';
        display.textContent = 'Varijanta A: ' + weight + '%, Varijanta B: ' + variantB + '%';
        this.parentNode.insertBefore(display, this.nextSibling);
    }

    function handleFormSubmit(e) {
        const publisherIdInput = document.querySelector('input[name="rvk_adsense_settings[publisher_id]"]');
        const adsenseEnabledInput = document.querySelector('input[name="rvk_adsense_settings[enable_adsense]"]');

        const publisherId = publisherIdInput ? publisherIdInput.value.trim() : '';
        const adsenseEnabled = adsenseEnabledInput ? adsenseEnabledInput.checked : false;

        if (adsenseEnabled && !publisherId) {
            alert('Molimo unesite vaš AdSense Publisher ID prije omogućavanja AdSense-a.');
            e.preventDefault();
            return false;
        }

        if (publisherId && !publisherId.startsWith('ca-pub-')) {
            alert('Publisher ID mora počinjati sa "ca-pub-"');
            e.preventDefault();
            return false;
        }

        // Check if at least one position has ad slot ID if enabled
        let hasValidPosition = false;
        ['position_a', 'position_b', 'position_c'].forEach(function(position) {
            const enabledInput = document.querySelector('input[name="rvk_adsense_settings[' + position + '][enabled]"]');
            const slotIdInput = document.querySelector('input[name="rvk_adsense_settings[' + position + '][ad_slot_id]"]');

            const enabled = enabledInput ? enabledInput.checked : false;
            const slotId = slotIdInput ? slotIdInput.value.trim() : '';

            if (enabled && slotId) {
                hasValidPosition = true;
            } else if (enabled && !slotId) {
                alert('Pozicija ' + position.replace('position_', '').toUpperCase() + ' je omogućena ali nema ID Reklamnog Slota. Molimo unesite ID Reklamnog Slota ili onemogućite ovu poziciju.');
                e.preventDefault();
                return false;
            }
        });

        if (adsenseEnabled && !hasValidPosition) {
            const confirmSave = confirm('AdSense je omogućen ali nijedna pozicija nema konfigurisane ID-ove Reklamnih Slotova. Želite li ipak sačuvati?');
            if (!confirmSave) {
                e.preventDefault();
                return false;
            }
        }

        return true;
    }

    function handleResponsiveToggle() {
        const isChecked = this.checked;
        const container = this.closest('label').parentNode;
        const inputs = container.querySelectorAll('input[type="number"]');

        inputs.forEach(function(input) {
            if (isChecked) {
                input.disabled = false;
                input.style.opacity = '1';
            } else {
                input.disabled = true;
                input.style.opacity = '0.5';
            }
        });
    }

})();
