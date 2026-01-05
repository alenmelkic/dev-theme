/**
 * Load More Pagination Logic
 */

export const initLoadMore = () => {
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (!loadMoreBtn) return;

    loadMoreBtn.addEventListener('click', async (e) => {
        e.preventDefault();

        const btn = e.currentTarget;
        const text = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.spinner-border');
        const containerSelector = btn.getAttribute('data-container');
        const container = document.querySelector(containerSelector);
        let nextPageUrl = btn.getAttribute('data-next-page');

        if (!nextPageUrl || btn.disabled) return;

        // Start loading
        btn.disabled = true;
        text.classList.add('d-none');
        spinner.classList.remove('d-none');

        try {
            const response = await fetch(nextPageUrl);
            if (!response.ok) throw new Error('Network response was not ok');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Get new items
            const newItems = doc.querySelectorAll(`${containerSelector} > *`);
            const fragment = document.createDocumentFragment();

            newItems.forEach(item => {
                fragment.appendChild(item.cloneNode(true));
            });

            // Append items
            container.appendChild(fragment);

            // Update URL for SEO/AEO
            history.pushState(null, '', nextPageUrl);

            // Update button or hide it
            const newLoadMoreBtn = doc.getElementById('load-more-btn');
            if (newLoadMoreBtn) {
                const newUrl = newLoadMoreBtn.getAttribute('data-next-page');
                btn.setAttribute('data-next-page', newUrl);
                btn.disabled = false;
                text.classList.remove('d-none');
                spinner.classList.add('d-none');
            } else {
                btn.parentElement.remove();
            }

            // Trigger re-init of components
            document.dispatchEvent(new CustomEvent('load-more:loaded', {
                detail: { container: containerSelector }
            }));

        } catch (error) {
            console.error('Error loading more posts:', error);
            btn.disabled = false;
            text.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    });
};
