/**
 * AdSense Admin JavaScript
 * Handles admin interface interactions for AdSense settings
 */

jQuery(document).ready(function($) {
    'use strict';

    // Handle collapsible sections (accordion functionality)
    $('.rvk-section-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $(this).siblings('.rvk-section-content').slideToggle(300);
    });

    // Initialize all sections as expanded on first load
    $('.rvk-section-content').show();

    // Validate Publisher ID format
    $('input[name="rvk_adsense_settings[publisher_id]"]').on('blur', function() {
        const value = $(this).val().trim();
        if (value && !value.startsWith('ca-pub-')) {
            alert('Publisher ID mora počinjati sa "ca-pub-"');
            $(this).focus();
        }
    });

    // A/B Testing weight slider visual feedback (optional)
    $('input[name*="[variant_a_weight]"]').on('input', function() {
        const weight = $(this).val();
        const variantB = 100 - weight;
        $(this).next('.weight-display').remove();
        $(this).after('<span class="weight-display" style="margin-left: 10px; color: #666;">Varijanta A: ' + weight + '%, Varijanta B: ' + variantB + '%</span>');
    });

    // Initialize weight displays
    $('input[name*="[variant_a_weight]"]').trigger('input');

    // Lazy load toggle explanation
    $('input[name*="[lazy_load]"]').on('change', function() {
        const isChecked = $(this).is(':checked');
        const message = isChecked
            ? 'Lenjivo učitavanje omogućeno: Reklame će se učitati kada su vidljive, poboljšavajući brzinu stranice.'
            : 'Lenjivo učitavanje onemogućeno: Reklame će se učitati odmah pri učitavanju stranice.';

        // Show temporary message (optional)
        // console.log(message);
    });

    // Form validation before submit
    $('form').on('submit', function(e) {
        const publisherId = $('input[name="rvk_adsense_settings[publisher_id]"]').val().trim();
        const adsenseEnabled = $('input[name="rvk_adsense_settings[enable_adsense]"]').is(':checked');

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
            const enabled = $('input[name="rvk_adsense_settings[' + position + '][enabled]"]').is(':checked');
            const slotId = $('input[name="rvk_adsense_settings[' + position + '][ad_slot_id]"]').val().trim();

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
    });

    // Helper: Toggle responsive size inputs based on checkbox
    $('input[type="checkbox"][name*="[responsive]["][name*="][enabled]"]').on('change', function() {
        const isChecked = $(this).is(':checked');
        const container = $(this).closest('label').parent();
        const inputs = container.find('input[type="number"]');

        if (isChecked) {
            inputs.prop('disabled', false).css('opacity', '1');
        } else {
            inputs.prop('disabled', true).css('opacity', '0.5');
        }
    }).trigger('change');
});
