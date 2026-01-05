(function($) {
  'use strict';

  $(document).ready(function() {

    // GA4 vs GTM mutual exclusivity
    $('input[name="integration_type"]').on('change', function() {
      const type = $(this).val();

      if (type === 'ga4') {
        $('#ga4-section').addClass('active').show();
        $('#gtm-section').removeClass('active').hide();
        $('input[name="rvk_analytics_settings[ga4][enabled]"]').prop('checked', true);
        $('input[name="rvk_analytics_settings[gtm][enabled]"]').prop('checked', false);
      } else if (type === 'gtm') {
        $('#gtm-section').addClass('active').show();
        $('#ga4-section').removeClass('active').hide();
        $('input[name="rvk_analytics_settings[gtm][enabled]"]').prop('checked', true);
        $('input[name="rvk_analytics_settings[ga4][enabled]"]').prop('checked', false);
      }
    });

    // Trigger change on page load to show correct section
    $('input[name="integration_type"]:checked').trigger('change');

    // GA4 Measurement ID validation
    $('#ga4_measurement_id').on('blur', function() {
      const value = $(this).val().trim();
      const $field = $(this);

      // Remove previous error
      $field.removeClass('error');
      $field.next('.error-msg').remove();

      if (value && !value.match(/^G-[A-Z0-9]+$/)) {
        $field.addClass('error');
        $field.after('<span class="error-msg">Invalid format. Must start with G- (e.g., G-ABC123XYZ)</span>');
      }
    });

    // GTM Container ID validation
    $('#gtm_container_id').on('blur', function() {
      const value = $(this).val().trim();
      const $field = $(this);

      // Remove previous error
      $field.removeClass('error');
      $field.next('.error-msg').remove();

      if (value && !value.match(/^GTM-[A-Z0-9]+$/)) {
        $field.addClass('error');
        $field.after('<span class="error-msg">Invalid format. Must start with GTM- (e.g., GTM-ABC123)</span>');
      }
    });

    // Facebook Pixel ID validation
    $('#facebook_pixel_id').on('blur', function() {
      const value = $(this).val().trim();
      const $field = $(this);

      // Remove previous error
      $field.removeClass('error');
      $field.next('.error-msg').remove();

      if (value && !value.match(/^[0-9]+$/)) {
        $field.addClass('error');
        $field.after('<span class="error-msg">Invalid format. Must be numeric only (e.g., 123456789012345)</span>');
      }
    });

    // Async/Defer mutual exclusivity
    $('.async-defer-toggle').each(function() {
      const $section = $(this);
      const $async = $section.find('.async-checkbox');
      const $defer = $section.find('.defer-checkbox');

      $async.on('change', function() {
        if ($(this).is(':checked')) {
          $defer.prop('checked', false);
        }
      });

      $defer.on('change', function() {
        if ($(this).is(':checked')) {
          $async.prop('checked', false);
        }
      });
    });

    // Lazy load delay field toggle
    $('.lazy-load-checkbox').on('change', function() {
      const $checkbox = $(this);
      const $delayField = $checkbox.closest('tbody').find('.lazy-delay-field');

      if ($checkbox.is(':checked')) {
        $delayField.show();
      } else {
        $delayField.hide();
      }
    }).trigger('change');

    // Consent mode toggle
    $('input[name="rvk_analytics_settings[performance][consent_mode]"]').on('change', function() {
      const value = $(this).val();
      const $manualOptions = $('.consent-manual-options');

      if (value === 'manual') {
        $manualOptions.show();
      } else {
        $manualOptions.hide();
      }
    }).trigger('change');

    // Form validation before submit
    $('form').on('submit', function(e) {
      let isValid = true;
      let errorMessages = [];

      // Check for validation errors
      if ($('.error').length > 0) {
        errorMessages.push('Please fix validation errors before saving.');
        isValid = false;
      }

      // Check GA4 settings
      const ga4Enabled = $('input[name="rvk_analytics_settings[ga4][enabled]"]').is(':checked');
      const ga4MeasurementId = $('#ga4_measurement_id').val().trim();
      if (ga4Enabled && !ga4MeasurementId) {
        errorMessages.push('GA4 is enabled but Measurement ID is empty.');
        $('#ga4_measurement_id').addClass('error');
        isValid = false;
      }

      // Check GTM settings
      const gtmEnabled = $('input[name="rvk_analytics_settings[gtm][enabled]"]').is(':checked');
      const gtmContainerId = $('#gtm_container_id').val().trim();
      if (gtmEnabled && !gtmContainerId) {
        errorMessages.push('GTM is enabled but Container ID is empty.');
        $('#gtm_container_id').addClass('error');
        isValid = false;
      }

      // Check Facebook Pixel settings
      const fbEnabled = $('input[name="rvk_analytics_settings[facebook_pixel][enabled]"]').is(':checked');
      const fbPixelId = $('#facebook_pixel_id').val().trim();
      if (fbEnabled && !fbPixelId) {
        errorMessages.push('Facebook Pixel is enabled but Pixel ID is empty.');
        $('#facebook_pixel_id').addClass('error');
        isValid = false;
      }

      // Warn if both GA4 and GTM enabled (shouldn't happen with radio buttons, but just in case)
      if (ga4Enabled && gtmEnabled) {
        errorMessages.push('GA4 and GTM cannot both be enabled. Please choose one.');
        isValid = false;
      }

      // Display errors
      if (!isValid) {
        e.preventDefault();
        alert('Validation Errors:\n\n' + errorMessages.join('\n'));
        return false;
      }

      return true;
    });

    // Clear error styling on input
    $('input[type="text"]').on('input', function() {
      $(this).removeClass('error');
      $(this).next('.error-msg').remove();
    });

  });

})(jQuery);
