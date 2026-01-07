/**
 * Custom Scripts Settings Admin - Vanilla JavaScript with Sortable.js (No jQuery)
 */
import Sortable from 'sortablejs';

(function() {
  'use strict';

  let scriptIndex = 0;
  let sortableInstance = null;

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  function init() {
    const scriptsContainer = document.getElementById('scripts-container');
    if (!scriptsContainer) return;

    scriptIndex = scriptsContainer.querySelectorAll('.script-item').length;

    // Initialize Sortable.js
    sortableInstance = new Sortable(scriptsContainer, {
      handle: '.script-drag-handle',
      animation: 150,
      ghostClass: 'script-item-placeholder',
      onEnd: function() {
        updateScriptOrder();
      }
    });

    // Add new script button
    const addScriptBtn = document.getElementById('add-script-btn');
    if (addScriptBtn) {
      addScriptBtn.addEventListener('click', addNewScript);
    }

    // Remove script buttons (delegated)
    scriptsContainer.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-script-btn') || e.target.closest('.remove-script-btn')) {
        const btn = e.target.classList.contains('remove-script-btn') ? e.target : e.target.closest('.remove-script-btn');
        handleRemoveScript(btn);
      }
    });

    // Type switcher (delegated)
    scriptsContainer.addEventListener('change', function(e) {
      if (e.target.classList.contains('script-type-select')) {
        handleTypeSwitch(e.target);
      }
      if (e.target.classList.contains('lazy-load-checkbox')) {
        handleLazyLoadToggle(e.target);
      }
      if (e.target.classList.contains('conditional-enabled-checkbox')) {
        handleConditionalToggle(e.target);
      }
      if (e.target.classList.contains('load-on-select')) {
        handleLoadOnChange(e.target);
      }
    });

    // Form validation
    const form = document.getElementById('custom-scripts-form');
    if (form) {
      form.addEventListener('submit', handleFormSubmit);
    }

    // Clear error styling on input (delegated)
    scriptsContainer.addEventListener('input', function(e) {
      if (e.target.matches('input[type="url"], input[type="text"], textarea')) {
        e.target.classList.remove('error');
      }
    });

    // Initialize existing script items
    scriptsContainer.querySelectorAll('.script-item').forEach(function(item) {
      initScriptItem(item);
    });
  }

  function updateScriptOrder() {
    const scriptsContainer = document.getElementById('scripts-container');
    const scriptItems = scriptsContainer.querySelectorAll('.script-item');
    scriptItems.forEach(function(item, index) {
      const orderInput = item.querySelector('.script-order');
      if (orderInput) {
        orderInput.value = index;
      }
    });
  }

  function addNewScript() {
    const timestamp = Date.now();
    const scriptId = 'script_' + timestamp;

    // Get template
    const template = document.getElementById('script-item-template');
    if (!template) return;

    // Clone template content
    let newScript = template.innerHTML;

    // Replace placeholders
    newScript = newScript.replace(/\{\{SCRIPT_ID\}\}/g, scriptId);
    newScript = newScript.replace(/\{\{INDEX\}\}/g, scriptIndex);

    // Append to container
    const scriptsContainer = document.getElementById('scripts-container');
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = newScript;
    const newItem = tempDiv.firstElementChild;
    scriptsContainer.appendChild(newItem);

    // Initialize the new item
    initScriptItem(newItem);

    scriptIndex++;

    // Update order
    updateScriptOrder();

    // Scroll to new item
    newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function handleRemoveScript(btn) {
    if (confirm('Are you sure you want to remove this script?')) {
      const scriptItem = btn.closest('.script-item');
      if (scriptItem) {
        scriptItem.remove();
        updateScriptOrder();
      }
    }
  }

  function handleTypeSwitch(select) {
    const scriptItem = select.closest('.script-item');
    if (!scriptItem) return;

    const type = select.value;
    const inlineCodeRow = scriptItem.querySelector('.inline-code-row');
    const externalUrlRow = scriptItem.querySelector('.external-url-row');
    const asyncDeferRow = scriptItem.querySelector('.async-defer-row');

    if (type === 'inline') {
      inlineCodeRow.style.display = 'block';
      externalUrlRow.style.display = 'none';
      asyncDeferRow.style.display = 'none';
    } else {
      inlineCodeRow.style.display = 'none';
      externalUrlRow.style.display = 'block';
      asyncDeferRow.style.display = 'block';
    }
  }

  function handleLazyLoadToggle(checkbox) {
    const scriptItem = checkbox.closest('.script-item');
    if (!scriptItem) return;

    const delayLabel = scriptItem.querySelector('.lazy-delay-label');
    if (!delayLabel) return;

    delayLabel.style.display = checkbox.checked ? 'inline-block' : 'none';
  }

  function handleConditionalToggle(checkbox) {
    const details = checkbox.closest('details');
    if (!details) return;

    if (checkbox.checked) {
      details.setAttribute('open', 'true');
    } else {
      details.removeAttribute('open');
    }
  }

  function handleLoadOnChange(select) {
    const scriptItem = select.closest('.script-item');
    if (!scriptItem) return;

    const value = select.value;
    const postTypesCheckboxes = scriptItem.querySelector('.post-types-checkboxes');
    if (!postTypesCheckboxes) return;

    postTypesCheckboxes.style.display = (value === 'post_types') ? 'grid' : 'none';
  }

  function initScriptItem(item) {
    // Trigger type change to show/hide fields
    const typeSelect = item.querySelector('.script-type-select');
    if (typeSelect) {
      handleTypeSwitch(typeSelect);
    }

    const lazyLoadCheckbox = item.querySelector('.lazy-load-checkbox');
    if (lazyLoadCheckbox) {
      handleLazyLoadToggle(lazyLoadCheckbox);
    }

    const loadOnSelect = item.querySelector('.load-on-select');
    if (loadOnSelect) {
      handleLoadOnChange(loadOnSelect);
    }
  }

  function handleFormSubmit(e) {
    let isValid = true;
    let errorMessages = [];

    const scriptsContainer = document.getElementById('scripts-container');
    const scriptItems = scriptsContainer.querySelectorAll('.script-item');

    // Check each enabled script
    scriptItems.forEach(function(item) {
      const enabledCheckbox = item.querySelector('.script-enabled-toggle input');
      const enabled = enabledCheckbox ? enabledCheckbox.checked : false;

      if (!enabled) {
        return; // Skip disabled scripts
      }

      const nameInput = item.querySelector('input[name*="[name]"]');
      const name = nameInput ? nameInput.value.trim() : '';
      const typeSelect = item.querySelector('.script-type-select');
      const type = typeSelect ? typeSelect.value : '';
      let content = '';

      if (type === 'external') {
        const urlInput = item.querySelector('.external-url-input');
        content = urlInput ? urlInput.value.trim() : '';

        // Validate HTTPS
        if (content && !content.startsWith('https://')) {
          errorMessages.push('Script "' + (name || 'Unnamed') + '": URL must start with https://');
          if (urlInput) urlInput.classList.add('error');
          isValid = false;
        }

        // Check for localhost
        if (content && (content.includes('localhost') || content.includes('127.0.0.1'))) {
          errorMessages.push('Script "' + (name || 'Unnamed') + '": Localhost URLs are not allowed');
          if (urlInput) urlInput.classList.add('error');
          isValid = false;
        }
      } else {
        const textarea = item.querySelector('.inline-code-textarea');
        content = textarea ? textarea.value.trim() : '';
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
  }

})();
