/**
 * Analytics Settings Admin - Vanilla JavaScript (No jQuery)
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
    // Integration type switcher (GA4 vs GTM)
    const integrationTypeRadios = document.querySelectorAll('input[name="integration_type"]');
    integrationTypeRadios.forEach(function(radio) {
      radio.addEventListener('change', handleIntegrationTypeChange);
    });

    // Trigger initial state
    const checkedRadio = document.querySelector('input[name="integration_type"]:checked');
    if (checkedRadio) {
      handleIntegrationTypeChange.call(checkedRadio);
    }

    // Async/Defer mutual exclusivity for all integrations
    setupAsyncDeferToggles('ga4');
    setupAsyncDeferToggles('gtm');
    setupAsyncDeferToggles('facebook_pixel');

    // Lazy load toggles
    setupLazyLoadToggle('ga4');
    setupLazyLoadToggle('gtm');
    setupLazyLoadToggle('facebook_pixel');

    // Cookie consent toggles
    setupCookieConsentToggle('ga4');
    setupCookieConsentToggle('gtm');
    setupCookieConsentToggle('facebook_pixel');

    // ID validation on blur
    setupIdValidation();

    // Form validation
    const form = document.getElementById('analytics-settings-form');
    if (form) {
      form.addEventListener('submit', handleFormSubmit);
    }
  }

  function handleIntegrationTypeChange() {
    const type = this.value;
    const ga4Section = document.getElementById('ga4-section');
    const gtmSection = document.getElementById('gtm-section');
    const ga4Checkbox = document.querySelector('input[name="rvk_analytics_settings[ga4][enabled]"]');
    const gtmCheckbox = document.querySelector('input[name="rvk_analytics_settings[gtm][enabled]"]');

    if (type === 'ga4') {
      ga4Section.classList.add('active');
      ga4Section.style.display = 'block';
      gtmSection.classList.remove('active');
      gtmSection.style.display = 'none';
      if (ga4Checkbox) ga4Checkbox.checked = true;
      if (gtmCheckbox) gtmCheckbox.checked = false;
    } else if (type === 'gtm') {
      gtmSection.classList.add('active');
      gtmSection.style.display = 'block';
      ga4Section.classList.remove('active');
      ga4Section.style.display = 'none';
      if (gtmCheckbox) gtmCheckbox.checked = true;
      if (ga4Checkbox) ga4Checkbox.checked = false;
    } else if (type === 'none') {
      ga4Section.classList.remove('active');
      ga4Section.style.display = 'none';
      gtmSection.classList.remove('active');
      gtmSection.style.display = 'none';
      if (ga4Checkbox) ga4Checkbox.checked = false;
      if (gtmCheckbox) gtmCheckbox.checked = false;
    }
  }

  function setupAsyncDeferToggles(integration) {
    const asyncCheckbox = document.querySelector('input[name="rvk_analytics_settings[' + integration + '][async_loading]"]');
    const deferCheckbox = document.querySelector('input[name="rvk_analytics_settings[' + integration + '][defer_loading]"]');

    if (!asyncCheckbox || !deferCheckbox) return;

    asyncCheckbox.addEventListener('change', function() {
      if (this.checked && deferCheckbox.checked) {
        deferCheckbox.checked = false;
      }
    });

    deferCheckbox.addEventListener('change', function() {
      if (this.checked && asyncCheckbox.checked) {
        asyncCheckbox.checked = false;
      }
    });
  }

  function setupLazyLoadToggle(integration) {
    const lazyLoadCheckbox = document.querySelector('input[name="rvk_analytics_settings[' + integration + '][lazy_load]"]');
    const delayField = document.querySelector('.lazy-delay-' + integration.replace('_', '-'));

    if (!lazyLoadCheckbox || !delayField) return;

    lazyLoadCheckbox.addEventListener('change', function() {
      delayField.style.display = this.checked ? 'block' : 'none';
    });

    // Trigger initial state
    delayField.style.display = lazyLoadCheckbox.checked ? 'block' : 'none';
  }

  function setupCookieConsentToggle(integration) {
    const consentCheckbox = document.querySelector('input[name="rvk_analytics_settings[' + integration + '][cookie_consent_required]"]');
    const consentNote = document.querySelector('.consent-note-' + integration.replace('_', '-'));

    if (!consentCheckbox || !consentNote) return;

    consentCheckbox.addEventListener('change', function() {
      consentNote.style.display = this.checked ? 'block' : 'none';
    });

    // Trigger initial state
    consentNote.style.display = consentCheckbox.checked ? 'block' : 'none';
  }

  function setupIdValidation() {
    // GA4 Measurement ID validation
    const ga4Input = document.querySelector('input[name="rvk_analytics_settings[ga4][measurement_id]"]');
    if (ga4Input) {
      ga4Input.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !value.match(/^G-[A-Z0-9]+$/)) {
          this.classList.add('error');
          showInlineError(this, 'Invalid format. Must be G-XXXXXXXXXX');
        } else {
          this.classList.remove('error');
          hideInlineError(this);
        }
      });
    }

    // GTM Container ID validation
    const gtmInput = document.querySelector('input[name="rvk_analytics_settings[gtm][container_id]"]');
    if (gtmInput) {
      gtmInput.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !value.match(/^GTM-[A-Z0-9]+$/)) {
          this.classList.add('error');
          showInlineError(this, 'Invalid format. Must be GTM-XXXXXX');
        } else {
          this.classList.remove('error');
          hideInlineError(this);
        }
      });
    }

    // Facebook Pixel ID validation
    const fbInput = document.querySelector('input[name="rvk_analytics_settings[facebook_pixel][pixel_id]"]');
    if (fbInput) {
      fbInput.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !value.match(/^[0-9]+$/)) {
          this.classList.add('error');
          showInlineError(this, 'Invalid format. Must be numeric only');
        } else {
          this.classList.remove('error');
          hideInlineError(this);
        }
      });
    }
  }

  function showInlineError(input, message) {
    hideInlineError(input); // Remove existing error first
    const errorDiv = document.createElement('span');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
  }

  function hideInlineError(input) {
    const existingError = input.parentNode.querySelector('.error-msg');
    if (existingError) {
      existingError.remove();
    }
  }

  function handleFormSubmit(e) {
    let isValid = true;
    const errorMessages = [];

    // Get selected integration type
    const selectedType = document.querySelector('input[name="integration_type"]:checked');
    if (!selectedType) {
      return true; // Allow submission if none selected (will disable all)
    }

    // Validate based on selected type
    if (selectedType.value === 'ga4') {
      const measurementId = document.querySelector('input[name="rvk_analytics_settings[ga4][measurement_id]"]');
      if (measurementId && !measurementId.value.trim()) {
        errorMessages.push('GA4 Measurement ID is required when GA4 is enabled.');
        measurementId.classList.add('error');
        isValid = false;
      }
    } else if (selectedType.value === 'gtm') {
      const containerId = document.querySelector('input[name="rvk_analytics_settings[gtm][container_id]"]');
      if (containerId && !containerId.value.trim()) {
        errorMessages.push('GTM Container ID is required when GTM is enabled.');
        containerId.classList.add('error');
        isValid = false;
      }
    }

    // Validate Facebook Pixel if enabled
    const fbEnabled = document.querySelector('input[name="rvk_analytics_settings[facebook_pixel][enabled]"]');
    if (fbEnabled && fbEnabled.checked) {
      const pixelId = document.querySelector('input[name="rvk_analytics_settings[facebook_pixel][pixel_id]"]');
      if (pixelId && !pixelId.value.trim()) {
        errorMessages.push('Facebook Pixel ID is required when Facebook Pixel is enabled.');
        pixelId.classList.add('error');
        isValid = false;
      }
    }

    if (!isValid) {
      e.preventDefault();
      alert('Validation Errors:\n\n' + errorMessages.join('\n'));
      return false;
    }

    return true;
  }

})();
