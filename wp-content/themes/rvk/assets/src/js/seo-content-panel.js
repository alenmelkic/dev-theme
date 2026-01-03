/**
 * SEO & AEO Content Panel - Gutenberg Sidebar
 * AI-powered SEO optimization panel
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
    const { createElement: el, Fragment, useState, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { TextControl, TextareaControl, Button, PanelRow, CheckboxControl, Notice, Spinner } = wp.components;

    /**
     * SEO Content Panel Component
     */
    const SEOContentPanel = () => {
        const { editPost } = useDispatch('core/editor');
        const { savePost } = useDispatch('core/editor');

        // Get post data
        const { postId, postContent, postTitle, postExcerpt, postMeta } = useSelect((select) => {
            const editor = select('core/editor');
            return {
                postId: editor.getCurrentPostId(),
                postContent: editor.getEditedPostContent(),
                postTitle: editor.getEditedPostAttribute('title'),
                postExcerpt: editor.getEditedPostAttribute('excerpt'),
                postMeta: editor.getEditedPostAttribute('meta') || {}
            };
        });

        // State
        const [seoTitle, setSeoTitle] = useState(postMeta._seo_title || '');
        const [seoDescription, setSeoDescription] = useState(postMeta._seo_description || '');
        const [seoKeywords, setSeoKeywords] = useState(postMeta._seo_keywords || '');
        const [seoCanonical, setSeoCanonical] = useState(postMeta._seo_canonical || '');
        const [seoNoindex, setSeoNoindex] = useState(postMeta._seo_noindex === '1');
        const [seoNofollow, setSeoNofollow] = useState(postMeta._seo_nofollow === '1');
        const [seoScore, setSeoScore] = useState(0);
        const [loading, setLoading] = useState(false);
        const [message, setMessage] = useState(null);
        const [showAdvanced, setShowAdvanced] = useState(false);

        // Update state when post meta changes
        useEffect(() => {
            if (postMeta._seo_title !== undefined) setSeoTitle(postMeta._seo_title);
            if (postMeta._seo_description !== undefined) setSeoDescription(postMeta._seo_description);
            if (postMeta._seo_keywords !== undefined) setSeoKeywords(postMeta._seo_keywords);
            if (postMeta._seo_canonical !== undefined) setSeoCanonical(postMeta._seo_canonical);
            if (postMeta._seo_noindex !== undefined) setSeoNoindex(postMeta._seo_noindex === '1');
            if (postMeta._seo_nofollow !== undefined) setSeoNofollow(postMeta._seo_nofollow === '1');
        }, [postMeta]);

        // Auto-save meta when changed
        useEffect(() => {
            const meta = {
                _seo_title: seoTitle,
                _seo_description: seoDescription,
                _seo_keywords: seoKeywords,
                _seo_canonical: seoCanonical,
                _seo_noindex: seoNoindex ? '1' : '0',
                _seo_nofollow: seoNofollow ? '1' : '0'
            };
            editPost({ meta });
        }, [seoTitle, seoDescription, seoKeywords, seoCanonical, seoNoindex, seoNofollow]);

        /**
         * Generate all SEO meta with AI
         */
        const generateAllWithAI = async () => {
            if (!postContent || postContent.trim() === '') {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije generisanja SEO meta podataka.' });
                return;
            }

            setLoading(true);
            setMessage(null);

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/generate-meta',
                    method: 'POST',
                    data: { content: postContent, title: postTitle }
                });

                if (response.success) {
                    if (response.title) setSeoTitle(response.title);
                    if (response.description) setSeoDescription(response.description);
                    if (response.keywords && response.keywords.length > 0) {
                        setSeoKeywords(response.keywords.join(', '));
                    }

                    setMessage({ type: 'success', text: '✓ SEO meta podaci uspješno generisani!' });

                    // Analyze content after generation
                    analyzeContent();
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoading(false);
            }
        };

        /**
         * Generate SEO title with AI
         */
        const generateTitle = async () => {
            if (!postContent || postContent.trim() === '') {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije generisanja naslova.' });
                return;
            }

            setLoading(true);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-title',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.title) {
                    setSeoTitle(response.title);
                    setMessage({ type: 'success', text: `✓ SEO naslov generisan! (${response.length} karaktera)` });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoading(false);
            }
        };

        /**
         * Generate meta description with AI
         */
        const generateDescription = async () => {
            if (!postContent || postContent.trim() === '') {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije generisanja opisa.' });
                return;
            }

            setLoading(true);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-excerpt',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.excerpt) {
                    setSeoDescription(response.excerpt);
                    setMessage({ type: 'success', text: `✓ Meta opis generisan! (${response.length} karaktera)` });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoading(false);
            }
        };

        /**
         * Extract keywords with AI
         */
        const extractKeywords = async () => {
            if (!postContent || postContent.trim() === '') {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije ekstrakcije ključnih riječi.' });
                return;
            }

            setLoading(true);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/extract-keywords',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.keywords) {
                    setSeoKeywords(response.keywords.join(', '));
                    setMessage({ type: 'success', text: '✓ Ključne riječi ekstraktovane!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoading(false);
            }
        };

        /**
         * Analyze content and calculate SEO score
         */
        const analyzeContent = async () => {
            if (!postId) return;

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/analyze',
                    method: 'POST',
                    data: { post_id: postId, content: postContent }
                });

                if (response.success && response.analysis) {
                    setSeoScore(response.analysis.seo_score || 0);
                }
            } catch (error) {
                console.error('Analysis error:', error);
            }
        };

        // Analyze on mount and content change
        useEffect(() => {
            if (postContent && postId) {
                const timer = setTimeout(() => {
                    analyzeContent();
                }, 2000); // Debounce

                return () => clearTimeout(timer);
            }
        }, [postContent, postId]);

        // Character counts
        const titleLength = seoTitle.length;
        const descLength = seoDescription.length;

        // SEO score color
        const getScoreColor = (score) => {
            if (score >= 70) return '#46b450';
            if (score >= 50) return '#ffb900';
            return '#dc3232';
        };

        return el(
            Fragment,
            null,
            el(
                PluginDocumentSettingPanel,
                {
                    name: 'seo-optimization-panel',
                    title: 'SEO & AEO Optimization',
                    className: 'seo-optimization-panel'
                },
                // Message notice
                message && el(Notice, {
                    status: message.type === 'success' ? 'success' : 'error',
                    isDismissible: true,
                    onRemove: () => setMessage(null)
                }, message.text),

                // SEO Score
                el(PanelRow, {},
                    el('div', { style: { width: '100%', marginBottom: '15px' } },
                        el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                            el('strong', {}, 'SEO Score:'),
                            el('span', {
                                style: {
                                    fontSize: '18px',
                                    fontWeight: 'bold',
                                    color: getScoreColor(seoScore)
                                }
                            }, seoScore + '/100')
                        ),
                        el('div', {
                            style: {
                                height: '8px',
                                background: '#e0e0e0',
                                borderRadius: '4px',
                                marginTop: '8px',
                                overflow: 'hidden'
                            }
                        },
                            el('div', {
                                style: {
                                    width: seoScore + '%',
                                    height: '100%',
                                    background: getScoreColor(seoScore),
                                    transition: 'width 0.3s ease'
                                }
                            })
                        )
                    )
                ),

                // SEO Title
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } },
                        'SEO Title'
                    ),
                    el(TextControl, {
                        value: seoTitle,
                        onChange: setSeoTitle,
                        placeholder: postTitle || 'Unesite SEO naslov',
                        help: el('span', {
                            style: { color: titleLength > 60 ? '#dc3232' : (titleLength >= 50 ? '#46b450' : '#666') }
                        }, `${titleLength}/60 karaktera`)
                    }),
                    el(Button, {
                        variant: 'secondary',
                        onClick: generateTitle,
                        disabled: loading,
                        style: { marginTop: '5px' }
                    }, loading ? el(Spinner) : '🤖 Generiši sa AI')
                ),

                // Meta Description
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } },
                        'Meta Description'
                    ),
                    el(TextareaControl, {
                        value: seoDescription,
                        onChange: setSeoDescription,
                        placeholder: 'Unesite meta opis',
                        rows: 3,
                        help: el('span', {
                            style: { color: descLength > 160 ? '#dc3232' : (descLength >= 150 ? '#46b450' : '#666') }
                        }, `${descLength}/160 karaktera`)
                    }),
                    el(Button, {
                        variant: 'secondary',
                        onClick: generateDescription,
                        disabled: loading,
                        style: { marginTop: '5px' }
                    }, loading ? el(Spinner) : '🤖 Generiši sa AI')
                ),

                // Focus Keywords
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } },
                        'Focus Keywords'
                    ),
                    el(TextControl, {
                        value: seoKeywords,
                        onChange: setSeoKeywords,
                        placeholder: 'npr. WordPress, SEO, optimizacija',
                        help: 'Odvojeno zarezom'
                    }),
                    el(Button, {
                        variant: 'secondary',
                        onClick: extractKeywords,
                        disabled: loading,
                        style: { marginTop: '5px' }
                    }, loading ? el(Spinner) : '🤖 Ekstraktuj sa AI')
                ),

                // Bulk AI Generation
                el(PanelRow, {},
                    el(Button, {
                        variant: 'primary',
                        onClick: generateAllWithAI,
                        disabled: loading,
                        style: { width: '100%', justifyContent: 'center', marginTop: '10px' }
                    }, loading ? el(Spinner) : '🤖 Optimiziraj Sve sa AI')
                ),

                // Advanced Settings Toggle
                el(PanelRow, { style: { marginTop: '15px' } },
                    el(Button, {
                        variant: 'link',
                        onClick: () => setShowAdvanced(!showAdvanced)
                    }, showAdvanced ? '▼ Sakrij Napredne Postavke' : '▶ Napredne Postavke')
                ),

                // Advanced Settings
                showAdvanced && el(Fragment, null,
                    el('div', { style: { marginTop: '15px', paddingTop: '15px', borderTop: '1px solid #ddd' } },
                        el(TextControl, {
                            label: 'Canonical URL',
                            value: seoCanonical,
                            onChange: setSeoCanonical,
                            placeholder: 'Ostavi prazno za default',
                            type: 'url'
                        }),
                        el(CheckboxControl, {
                            label: 'No Index (sprečava indeksiranje)',
                            checked: seoNoindex,
                            onChange: setSeoNoindex
                        }),
                        el(CheckboxControl, {
                            label: 'No Follow (sprečava praćenje linkova)',
                            checked: seoNofollow,
                            onChange: setSeoNofollow
                        })
                    )
                )
            )
        );
    };

    // Register the plugin
    registerPlugin('seo-content-panel', {
        render: SEOContentPanel,
        icon: 'search'
    });

})();
