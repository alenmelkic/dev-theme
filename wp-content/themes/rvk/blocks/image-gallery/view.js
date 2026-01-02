import PhotoSwipeLightbox from 'photoswipe/lightbox';

/**
 * Initialize PhotoSwipe Gallery
 */
const initPhotoSwipe = () => {
    const galleries = document.querySelectorAll('.pswp-gallery');

    if (galleries.length === 0) return;

    galleries.forEach((gallery) => {
        const lightbox = new PhotoSwipeLightbox({
            gallery: gallery,
            children: 'a.gallery-item',
            pswpModule: () => import('photoswipe'),
        });

        // Filter to handle responsive image sizes in the lightbox
        lightbox.addFilter('itemData', (itemData) => {
            const mobileSrc = itemData.element.getAttribute('data-mobile-src');
            if (mobileSrc && window.innerWidth < 768) {
                itemData.src = mobileSrc;
                itemData.w = parseInt(itemData.element.getAttribute('data-mobile-width'), 10);
                itemData.h = parseInt(itemData.element.getAttribute('data-mobile-height'), 10);
            }
            return itemData;
        });

        lightbox.init();
    });
};

// Start on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhotoSwipe);
} else {
    initPhotoSwipe();
}

// Support for Swup (if active in the theme)
document.addEventListener('swup:content:replace', initPhotoSwipe);
document.addEventListener('swup:page:view', initPhotoSwipe);
