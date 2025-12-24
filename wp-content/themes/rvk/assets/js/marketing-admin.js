/**
 * Marketing Admin JavaScript
 * Handles image uploads and drag-and-drop for small banners
 */

(function ($) {
    'use strict';

    let draggedElement = null;

    // Initialize on document ready
    $(document).ready(function () {
        initImageUploaders();
        initSmallBannerDragDrop();
        initAddSmallBanner();
    });

    /**
     * Initialize WordPress Media Uploader for images
     */
    function initImageUploaders() {
        // Top Banner image uploads
        $('.rvk-upload-image').on('click', function (e) {
            e.preventDefault();

            const button = $(this);
            const targetId = button.data('target');
            const targetInput = $('#' + targetId);
            const previewContainer = $('#' + targetId + '_preview');
            const removeButton = button.siblings('.rvk-remove-image');

            const mediaUploader = wp.media({
                title: 'Odaberi sliku',
                button: {
                    text: 'Koristi ovu sliku'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                targetInput.val(attachment.id);
                previewContainer.html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');
                removeButton.show();
            });

            mediaUploader.open();
        });

        // Remove image buttons
        $('.rvk-remove-image').on('click', function (e) {
            e.preventDefault();

            const button = $(this);
            const targetId = button.data('target');
            const targetInput = $('#' + targetId);
            const previewContainer = $('#' + targetId + '_preview');

            targetInput.val('');
            previewContainer.html('');
            button.hide();
        });

        // Small banner image uploads (delegated for dynamic elements)
        $(document).on('click', '.rvk-upload-small-banner', function (e) {
            e.preventDefault();

            const button = $(this);
            const container = button.closest('.small-banner-item');
            const imageInput = container.find('.small-banner-image-id');
            const previewContainer = container.find('.small-banner-preview');

            const mediaUploader = wp.media({
                title: 'Odaberi sliku',
                button: {
                    text: 'Koristi ovu sliku'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                imageInput.val(attachment.id);
                previewContainer.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');
            });

            mediaUploader.open();
        });
    }

    /**
     * Initialize drag and drop for small banners
     */
    function initSmallBannerDragDrop() {
        const container = document.getElementById('small-banners-container');

        if (!container) return;

        // Delegated event listeners for dynamic elements
        container.addEventListener('dragstart', function (e) {
            if (e.target.classList.contains('small-banner-item')) {
                draggedElement = e.target;
                e.target.style.opacity = '0.5';
            }
        });

        container.addEventListener('dragend', function (e) {
            if (e.target.classList.contains('small-banner-item')) {
                e.target.style.opacity = '1';
                updateSmallBannerIndices();
            }
        });

        container.addEventListener('dragover', function (e) {
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
        const draggableElements = [...container.querySelectorAll('.small-banner-item:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
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

        items.forEach((item, index) => {
            const imageInput = item.querySelector('.small-banner-image-id');
            const linkInput = item.querySelector('input[type="url"]');
            const altTextInput = item.querySelector('input[type="text"]');

            if (imageInput) {
                imageInput.name = `rvk_marketing_banners[small_banners][${index}][image]`;
            }
            if (linkInput) {
                linkInput.name = `rvk_marketing_banners[small_banners][${index}][link]`;
            }
            if (altTextInput) {
                altTextInput.name = `rvk_marketing_banners[small_banners][${index}][alt_text]`;
            }
        });
    }

    /**
     * Initialize add small banner button
     */
    function initAddSmallBanner() {
        $('#add-small-banner').on('click', function () {
            const container = $('#small-banners-container');
            const index = container.find('.small-banner-item').length;

            const newBanner = `
                <div class="small-banner-item" draggable="true">
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
                </div>
            `;

            container.append(newBanner);
        });

        // Remove small banner (delegated)
        $(document).on('click', '.rvk-remove-small-banner', function () {
            $(this).closest('.small-banner-item').remove();
            updateSmallBannerIndices();
        });
    }

})(jQuery);
