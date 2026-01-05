(function($) {
  'use strict';

  $(document).ready(function() {

    let scriptIndex = $('#scripts-container .script-item').length;

    // Initialize sortable
    $('#scripts-container').sortable({
      handle: '.script-drag-handle',
      placeholder: 'script-item-placeholder',
      update: function() {
        updateScriptOrder();
      }
    });

    // Update order values after drag
    function updateScriptOrder() {
      $('#scripts-container .script-item').each(function(index) {
        $(this).find('.script-order').val(index);
      });
    }

    // Add new script
    $('#add-script-btn').on('click', function() {
      const timestamp = Date.now();
      const scriptId = 'script_' + timestamp;

      // Get template
      const template = $('#script-item-template').html();

      // Replace placeholders
      const newScript = template
        .replace(/\{\{SCRIPT_ID\}\}/g, scriptId)
        .replace(/\{\{INDEX\}\}/g, scriptIndex);

      // Append to container
      $('#scripts-container').append(newScript);

      // Initialize the new item
      const $newItem = $('#scripts-container .script-item:last');
      initScriptItem($newItem);

      scriptIndex++;

      // Update order
      updateScriptOrder();

      // Scroll to new item
      $('html, body').animate({
        scrollTop: $newItem.offset().top - 100
      }, 500);
    });

    // Remove script
    $(document).on('click', '.remove-script-btn', function() {
      if (confirm('Are you sure you want to remove this script?')) {
        $(this).closest('.script-item').remove();
        updateScriptOrder();
      }
    });

    // Type switcher (inline/external)
    $(document).on('change', '.script-type-select', function() {
      const $item = $(this).closest('.script-item');
      const type = $(this).val();

      if (type === 'inline') {
        $item.find('.inline-code-row').show();
        $item.find('.external-url-row').hide();
        $item.find('.async-defer-row').hide();
      } else {
        $item.find('.inline-code-row').hide();
        $item.find('.external-url-row').show();
        $item.find('.async-defer-row').show();
      }
    });

    // Lazy load toggle
    $(document).on('change', '.lazy-load-checkbox', function() {
      const $item = $(this).closest('.script-item');
      const $delayLabel = $item.find('.lazy-delay-label');

      if ($(this).is(':checked')) {
        $delayLabel.show();
      } else {
        $delayLabel.hide();
      }
    });

    // Conditional loading toggle
    $(document).on('change', '.conditional-enabled-checkbox', function() {
      const $details = $(this).closest('details');

      if ($(this).is(':checked')) {
        $details.attr('open', true);
      } else {
        $details.removeAttr('open');
      }
    });

    // Load on selector
    $(document).on('change', '.load-on-select', function() {
      const $item = $(this).closest('.script-item');
      const value = $(this).val();
      const $postTypesCheckboxes = $item.find('.post-types-checkboxes');

      if (value === 'post_types') {
        $postTypesCheckboxes.show();
      } else {
        $postTypesCheckboxes.hide();
      }
    });

    // Initialize existing script items
    $('.script-item').each(function() {
      initScriptItem($(this));
    });

    // Initialize a script item
    function initScriptItem($item) {
      // Trigger type change to show/hide fields
      $item.find('.script-type-select').trigger('change');
      $item.find('.lazy-load-checkbox').trigger('change');
      $item.find('.load-on-select').trigger('change');
    }

    // Form validation
    $('#custom-scripts-form').on('submit', function(e) {
      let isValid = true;
      let errorMessages = [];

      // Check each enabled script
      $('#scripts-container .script-item').each(function() {
        const $item = $(this);
        const enabled = $item.find('.script-enabled-toggle input').is(':checked');

        if (!enabled) {
          return; // Skip disabled scripts
        }

        const name = $item.find('input[name*="[name]"]').val().trim();
        const type = $item.find('.script-type-select').val();
        let content = '';

        if (type === 'external') {
          content = $item.find('.external-url-input').val().trim();

          // Validate HTTPS
          if (content && !content.startsWith('https://')) {
            errorMessages.push('Script "' + (name || 'Unnamed') + '": URL must start with https://');
            $item.find('.external-url-input').addClass('error');
            isValid = false;
          }

          // Check for localhost
          if (content && (content.includes('localhost') || content.includes('127.0.0.1'))) {
            errorMessages.push('Script "' + (name || 'Unnamed') + '": Localhost URLs are not allowed');
            $item.find('.external-url-input').addClass('error');
            isValid = false;
          }
        } else {
          content = $item.find('.inline-code-textarea').val().trim();
        }

        // Check if content is empty
        if (!content) {
          errorMessages.push('Script "' + (name || 'Unnamed') + '": Content/URL is empty');
          isValid = false;
        }

        // Check if name is empty
        if (!name) {
          errorMessages.push('One or more scripts are missing a name');
          isValid = false;
        }
      });

      // Display errors
      if (!isValid) {
        e.preventDefault();
        alert('Validation Errors:\n\n' + errorMessages.join('\n'));
        return false;
      }

      return true;
    });

    // Clear error styling on input
    $(document).on('input', 'input[type="url"], input[type="text"], textarea', function() {
      $(this).removeClass('error');
    });

  });

})(jQuery);
