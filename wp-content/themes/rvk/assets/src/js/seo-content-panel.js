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
        const [loadingAll, setLoadingAll] = useState(false);
        const [loadingTitle, setLoadingTitle] = useState(false);
        const [loadingDescription, setLoadingDescription] = useState(false);
        const [loadingKeywords, setLoadingKeywords] = useState(false);
        const [message, setMessage] = useState(null);
        const [showAdvanced, setShowAdvanced] = useState(false);
        const [hasUserInteracted, setHasUserInteracted] = useState(false);

        // Helper function to save meta to database
        const saveMeta = (updates = {}) => {
            const meta = {
                _seo_title: updates.title !== undefined ? updates.title : seoTitle,
                _seo_description: updates.description !== undefined ? updates.description : seoDescription,
                _seo_keywords: updates.keywords !== undefined ? updates.keywords : seoKeywords,
                _seo_canonical: updates.canonical !== undefined ? updates.canonical : seoCanonical,
                _seo_noindex: updates.noindex !== undefined ? (updates.noindex ? '1' : '0') : (seoNoindex ? '1' : '0'),
                _seo_nofollow: updates.nofollow !== undefined ? (updates.nofollow ? '1' : '0') : (seoNofollow ? '1' : '0')
            };
            editPost({ meta });
        };

        // Auto-save meta when manually changed (but not on initial mount or AI generation)
        useEffect(() => {
            // Skip saving on initial mount
            if (!hasUserInteracted) {
                setHasUserInteracted(true);
                return;
            }

            // Debounce manual changes
            const timer = setTimeout(() => {
                saveMeta();
            }, 500);

            return () => clearTimeout(timer);
        }, [seoTitle, seoDescription, seoKeywords, seoCanonical, seoNoindex, seoNofollow]);

        /**
         * Generate all SEO meta with AI
         */
        const generateAllWithAI = async () => {
            if (!postContent || postContent.trim() === '') {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije generisanja SEO meta podataka.' });
                return;
            }

            setLoadingAll(true);
            setMessage(null);

            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/generate-meta',
                    method: 'POST',
                    data: { content: postContent, title: postTitle }
                });

                if (response.success) {
                    const updates = {};
                    if (response.title) {
                        setSeoTitle(response.title);
                        updates.title = response.title;
                    }
                    if (response.description) {
                        setSeoDescription(response.description);
                        updates.description = response.description;
                    }
                    if (response.keywords && response.keywords.length > 0) {
                        const keywordString = response.keywords.join(', ');
                        setSeoKeywords(keywordString);
                        updates.keywords = keywordString;
                    }

                    // Save immediately with new values
                    saveMeta(updates);

                    setMessage({ type: 'success', text: '✓ SEO meta podaci uspješno generisani!' });

                    // Analyze content after generation
                    analyzeContent();
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoadingAll(false);
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

            setLoadingTitle(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-title',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.title) {
                    setSeoTitle(response.title);
                    saveMeta({ title: response.title });
                    setMessage({ type: 'success', text: `✓ SEO naslov generisan! (${response.length} karaktera)` });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoadingTitle(false);
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

            setLoadingDescription(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-excerpt',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.excerpt) {
                    setSeoDescription(response.excerpt);
                    saveMeta({ description: response.excerpt });
                    setMessage({ type: 'success', text: `✓ Meta opis generisan! (${response.length} karaktera)` });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoadingDescription(false);
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

            setLoadingKeywords(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/extract-keywords',
                    method: 'POST',
                    data: { content: postContent }
                });

                if (response.success && response.keywords) {
                    const keywordString = response.keywords.join(', ');
                    setSeoKeywords(keywordString);
                    saveMeta({ keywords: keywordString });
                    setMessage({ type: 'success', text: '✓ Ključne riječi ekstraktovane!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoadingKeywords(false);
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

        // Analyze on mount and content change (if auto-analysis enabled)
        useEffect(() => {
            // Check if auto-analysis is enabled
            const autoAnalysisEnabled = window.seoData?.autoAnalysisEnabled !== false;

            if (postContent && postId && autoAnalysisEnabled) {
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
                        disabled: loadingTitle || loadingAll,
                        style: { marginTop: '5px' }
                    }, loadingTitle ? el(Spinner) : '🤖 Generiši sa AI')
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
                        disabled: loadingDescription || loadingAll,
                        style: { marginTop: '5px' }
                    }, loadingDescription ? el(Spinner) : '🤖 Generiši sa AI')
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
                        disabled: loadingKeywords || loadingAll,
                        style: { marginTop: '5px' }
                    }, loadingKeywords ? el(Spinner) : '🤖 Ekstraktuj sa AI')
                ),

                // Bulk AI Generation
                el(PanelRow, {},
                    el(Button, {
                        variant: 'primary',
                        onClick: generateAllWithAI,
                        disabled: loadingAll || loadingTitle || loadingDescription || loadingKeywords,
                        style: { width: '100%', justifyContent: 'center', marginTop: '10px' }
                    }, loadingAll ? el(Spinner) : '🤖 Optimiziraj Sve sa AI')
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
