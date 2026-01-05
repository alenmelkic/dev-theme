/**
 * Marketing Admin JavaScript - Vanilla JavaScript (No jQuery)
 * Handles image uploads and drag-and-drop for small banners
 */

(function() {
    'use strict';

    let draggedElement = null;

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initImageUploaders();
        initSmallBannerDragDrop();
        initAddSmallBanner();
    }

    /**
     * Initialize WordPress Media Uploader for images
     */
    function initImageUploaders() {
        // Top Banner image uploads
        const uploadButtons = document.querySelectorAll('.rvk-upload-image');
        uploadButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const previewContainer = document.getElementById(targetId + '_preview');
                const removeButton = this.parentNode.querySelector('.rvk-remove-image');

                if (!window.wp || !window.wp.media) {
                    alert('WordPress media uploader not available');
                    return;
                }

                const mediaUploader = wp.media({
                    title: 'Odaberi sliku',
                    button: {
                        text: 'Koristi ovu sliku'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    targetInput.value = attachment.id;
                    previewContainer.innerHTML = '<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">';
                    if (removeButton) {
                        removeButton.style.display = 'inline-block';
                    }
                });

                mediaUploader.open();
            });
        });

        // Remove image buttons
        const removeButtons = document.querySelectorAll('.rvk-remove-image');
        removeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const previewContainer = document.getElementById(targetId + '_preview');

                targetInput.value = '';
                previewContainer.innerHTML = '';
                this.style.display = 'none';
            });
        });

        // Small banner image uploads (delegated for dynamic elements)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('rvk-upload-small-banner')) {
                e.preventDefault();

                const button = e.target;
                const container = button.closest('.small-banner-item');
                const imageInput = container.querySelector('.small-banner-image-id');
                const previewContainer = container.querySelector('.small-banner-preview');

                if (!window.wp || !window.wp.media) {
                    alert('WordPress media uploader not available');
                    return;
                }

                const mediaUploader = wp.media({
                    title: 'Odaberi sliku',
                    button: {
                        text: 'Koristi ovu sliku'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    imageInput.value = attachment.id;
                    previewContainer.innerHTML = '<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">';
                });

                mediaUploader.open();
            }
        });
    }

    /**
     * Initialize drag and drop for small banners
     */
    function initSmallBannerDragDrop() {
        const container = document.getElementById('small-banners-container');

        if (!container) return;

        // Delegated event listeners for dynamic elements
        container.addEventListener('dragstart', function(e) {
            if (e.target.classList.contains('small-banner-item')) {
                draggedElement = e.target;
                e.target.style.opacity = '0.5';
            }
        });

        container.addEventListener('dragend', function(e) {
            if (e.target.classList.contains('small-banner-item')) {
                e.target.style.opacity = '1';
                updateSmallBannerIndices();
            }
        });

        container.addEventListener('dragover', function(e) {
            e.preventDefault();

            const afterElement = getDragAfterElement(container, e.clientY);
            const draggable = draggedElement;

            if (afterElement == null) {
                container.appendChild(draggable);
            } else {
                container.insertBefore(draggable, afterElement);
            }
        });
    }

    /**
     * Get element after which to insert dragged element
     */
    function getDragAfterElement(container, y) {
        const draggableElements = Array.from(container.querySelectorAll('.small-banner-item:not(.dragging)'));

        return draggableElements.reduce(function(closest, child) {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    /**
     * Update indices of small banner inputs after reordering
     */
    function updateSmallBannerIndices() {
        const items = document.querySelectorAll('.small-banner-item');

        items.forEach(function(item, index) {
            const imageInput = item.querySelector('.small-banner-image-id');
            const linkInput = item.querySelector('input[type="url"]');
            const altTextInput = item.querySelector('input[type="text"]');

            if (imageInput) {
                imageInput.name = 'rvk_marketing_banners[small_banners][' + index + '][image]';
            }
            if (linkInput) {
                linkInput.name = 'rvk_marketing_banners[small_banners][' + index + '][link]';
            }
            if (altTextInput) {
                altTextInput.name = 'rvk_marketing_banners[small_banners][' + index + '][alt_text]';
            }
        });
    }

    /**
     * Initialize add small banner button
     */
    function initAddSmallBanner() {
        const addButton = document.getElementById('add-small-banner');
        if (!addButton) return;

        addButton.addEventListener('click', function() {
            const container = document.getElementById('small-banners-container');
            const index = container.querySelectorAll('.small-banner-item').length;

            const newBanner = document.createElement('div');
            newBanner.className = 'small-banner-item';
            newBanner.draggable = true;
            newBanner.innerHTML = `
                <div class="small-banner-drag-handle">☰</div>
                <div class="small-banner-content">
                    <div class="rvk-image-upload">
                        <input type="hidden"
                               name="rvk_marketing_banners[small_banners][${index}][image]"
                               class="small-banner-image-id"
                               value="">
                        <button type="button" class="button rvk-upload-small-banner">
                            Odaberi sliku
                        </button>
                        <div class="rvk-image-preview small-banner-preview"></div>
                    </div>
                    <div class="small-banner-link-field">
                        <label>Link:</label>
                        <input type="url"
                               name="rvk_marketing_banners[small_banners][${index}][link]"
                               value=""
                               class="regular-text"
                               placeholder="https://example.com">
                    </div>
                    <div class="small-banner-link-field">
                        <label>Alt Tekst:</label>
                        <input type="text"
                               name="rvk_marketing_banners[small_banners][${index}][alt_text]"
                               value=""
                               class="regular-text"
                               placeholder="Opis bannera">
                    </div>
                    <button type="button" class="button button-link-delete rvk-remove-small-banner">Ukloni</button>
                </div>
            `;

            container.appendChild(newBanner);
        });

        // Remove small banner (delegated)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('rvk-remove-small-banner')) {
                const item = e.target.closest('.small-banner-item');
                if (item) {
                    item.remove();
                    updateSmallBannerIndices();
                }
            }
        });
    }

})();
