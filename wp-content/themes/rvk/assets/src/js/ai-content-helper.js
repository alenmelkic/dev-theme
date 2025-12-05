/**
 * AI Content Helper - WordPress Sidebar Panel
 * Adds AI generation panel to the WordPress editor sidebar
 * Tailored by Alen Melkić
 */

(function () {
    'use strict';

    // Wait for WordPress to be ready
    if (typeof wp === 'undefined' || !wp.data || !wp.plugins || !wp.editPost) {
        console.error('WordPress editor not available');
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { createElement: el, Fragment } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { Button } = wp.components;

    /**
     * AI Content Helper Component
     */
    const AIContentHelper = () => {
        const { editPost } = useDispatch('core/editor');
        const { getEditedPostContent, getEditedPostAttribute } = useSelect('core/editor');

        /**
         * Generate title with AI
         */
        const generateTitle = async () => {
            const content = getEditedPostContent();

            if (!content || content.trim() === '') {
                alert('Molimo dodajte sadržaj prije generisanja naslova.');
                return;
            }

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-title',
                    method: 'POST',
                    data: { content }
                });

                if (response.success && response.title) {
                    editPost({ title: response.title });
                    wp.data.dispatch('core/notices').createNotice(
                        'success',
                        `✓ Naslov generisan! (${response.length} karaktera)`,
                        { isDismissible: true, type: 'snackbar' }
                    );
                }
            } catch (error) {
                wp.data.dispatch('core/notices').createNotice(
                    'error',
                    'Greška: ' + error.message,
                    { isDismissible: true, type: 'snackbar' }
                );
            }
        };

        /**
         * Generate excerpt with AI
         */
        const generateExcerpt = async () => {
            const content = getEditedPostContent();

            if (!content || content.trim() === '') {
                alert('Molimo dodajte sadržaj prije generisanja sažetka.');
                return;
            }

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-excerpt',
                    method: 'POST',
                    data: { content }
                });

                if (response.success && response.excerpt) {
                    editPost({ excerpt: response.excerpt });
                    wp.data.dispatch('core/notices').createNotice(
                        'success',
                        `✓ Sažetak generisan! (${response.length} karaktera)`,
                        { isDismissible: true, type: 'snackbar' }
                    );
                }
            } catch (error) {
                wp.data.dispatch('core/notices').createNotice(
                    'error',
                    'Greška: ' + error.message,
                    { isDismissible: true, type: 'snackbar' }
                );
            }
        };

        /**
         * Optimize title with AI
         */
        const optimizeTitle = async () => {
            const title = getEditedPostAttribute('title');
            const content = getEditedPostContent();

            if (!title || title.trim() === '') {
                alert('Molimo unesite naslov prije optimizacije.');
                return;
            }

            if (!content || content.trim() === '') {
                alert('Molimo dodajte sadržaj.');
                return;
            }

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/optimize-title',
                    method: 'POST',
                    data: { title, content }
                });

                if (response.success && response.title) {
                    editPost({ title: response.title });
                    wp.data.dispatch('core/notices').createNotice(
                        'success',
                        `✓ Naslov optimizovan! (${response.length} karaktera)`,
                        { isDismissible: true, type: 'snackbar' }
                    );
                }
            } catch (error) {
                wp.data.dispatch('core/notices').createNotice(
                    'error',
                    'Greška: ' + error.message,
                    { isDismissible: true, type: 'snackbar' }
                );
            }
        };

        /**
         * Optimize excerpt with AI
         */
        const optimizeExcerpt = async () => {
            const excerpt = getEditedPostAttribute('excerpt');
            const content = getEditedPostContent();

            if (!excerpt || excerpt.trim() === '') {
                alert('Molimo unesite sažetak prije optimizacije.');
                return;
            }

            if (!content || content.trim() === '') {
                alert('Molimo dodajte sadržaj.');
                return;
            }

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/optimize-excerpt',
                    method: 'POST',
                    data: { excerpt, content }
                });

                if (response.success && response.excerpt) {
                    editPost({ excerpt: response.excerpt });
                    wp.data.dispatch('core/notices').createNotice(
                        'success',
                        `✓ Sažetak optimizovan! (${response.length} karaktera)`,
                        { isDismissible: true, type: 'snackbar' }
                    );
                }
            } catch (error) {
                wp.data.dispatch('core/notices').createNotice(
                    'error',
                    'Greška: ' + error.message,
                    { isDismissible: true, type: 'snackbar' }
                );
            }
        };

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'ai-content-panel',
                title: '✨ AI Sadržaj',
                className: 'ai-content-panel'
            },
            el(
                'div',
                { className: 'ai-content-buttons' },
                el(
                    'div',
                    { style: { marginBottom: '12px' } },
                    el(
                        'h4',
                        { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                        'Naslov'
                    ),
                    el(
                        Button,
                        {
                            variant: 'secondary',
                            onClick: generateTitle,
                            style: { width: '100%', marginBottom: '6px', justifyContent: 'center' }
                        },
                        '✨ Generiši Naslov'
                    ),
                    el(
                        Button,
                        {
                            variant: 'secondary',
                            onClick: optimizeTitle,
                            style: { width: '100%', justifyContent: 'center' }
                        },
                        '🔧 Optimiziraj Naslov'
                    )
                ),
                el(
                    'div',
                    { style: { marginTop: '16px' } },
                    el(
                        'h4',
                        { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                        'Sažetak'
                    ),
                    el(
                        Button,
                        {
                            variant: 'secondary',
                            onClick: generateExcerpt,
                            style: { width: '100%', marginBottom: '6px', justifyContent: 'center' }
                        },
                        '✨ Generiši Sažetak'
                    ),
                    el(
                        Button,
                        {
                            variant: 'secondary',
                            onClick: optimizeExcerpt,
                            style: { width: '100%', justifyContent: 'center' }
                        },
                        '🔧 Optimiziraj Sažetak'
                    )
                )
            )
        );
    };

    // Register the plugin
    registerPlugin('ai-content-helper', {
        render: AIContentHelper,
        icon: 'star-filled'
    });

})();
