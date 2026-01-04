/**
 * SEO & AEO Content Panel - Gutenberg Sidebar
 * Store-first implementation using useEntityProp for reliable persistence
 * Tailored by Alen Melkić
 */

(function () {
    'use strict';

    // Wait for WordPress to be ready
    if (typeof wp === 'undefined' || !wp.data || !wp.plugins || !wp.editor) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editor;
    const { createElement: el, Fragment, useState, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { useEntityProp } = wp.coreData;
    const { TextControl, TextareaControl, Button, PanelRow, CheckboxControl, Notice, Spinner } = wp.components;

    /**
     * SEO Content Panel Component
     */
    const SEOContentPanel = () => {
        // Get post type and ID
        const { postType, postId, postContent, postTitle } = useSelect((select) => {
            const editor = select('core/editor');
            return {
                postType: editor.getCurrentPostType(),
                postId: editor.getCurrentPostId(),
                postContent: editor.getEditedPostContent(),
                postTitle: editor.getEditedPostAttribute('title')
            };
        });

        // Get dispatch functions to trigger saves
        const { editPost } = useDispatch('core/editor');

        // Use useEntityProp for reliable meta handling
        // This is THE recommended way to handle meta in Gutenberg
        const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);

        // Local state for UI feedback (loading, messages)
        const [seoScore, setSeoScore] = useState(0);
        const [loadingAll, setLoadingAll] = useState(false);
        const [loadingTitle, setLoadingTitle] = useState(false);
        const [loadingDescription, setLoadingDescription] = useState(false);
        const [loadingKeywords, setLoadingKeywords] = useState(false);
        const [loadingTakeaways, setLoadingTakeaways] = useState(false);
        const [message, setMessage] = useState(null);
        const [showAdvanced, setShowAdvanced] = useState(false);

        // Values from meta object
        const seoTitle = meta?._seo_title || '';
        const seoDescription = meta?._seo_description || '';
        const seoKeywords = meta?._seo_keywords || '';
        const seoCanonical = meta?._seo_canonical || '';
        const seoNoindex = meta?._seo_noindex === '1';
        const seoNofollow = meta?._seo_nofollow === '1';
        const aeoKeyTakeaways = meta?._aeo_key_takeaways || '';

        /**
         * Update individual meta fields
         */
        const updateMeta = (key, value) => {
            const safeValue = value === null || value === undefined ? '' : value;
            console.log(`SEO [useEntityProp]: Updating ${key}:`, safeValue === '' ? '[EMPTY]' : safeValue);

            const newMeta = {
                ...meta,
                [key]: safeValue
            };

            setMeta(newMeta);

            // CRITICAL: Also update via editPost to mark post as dirty and trigger save
            editPost({ meta: newMeta });
        };

        /**
         * AI Generation Handlers
         */
        const generateAllWithAI = async () => {
            if (!postContent) {
                setMessage({ type: 'error', text: 'Molimo dodajte sadržaj prije optimizacije.' });
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
                    const newMeta = { ...meta };
                    if (response.title) newMeta._seo_title = response.title;
                    if (response.description) newMeta._seo_description = response.description;
                    if (response.keywords) newMeta._seo_keywords = response.keywords.join(', ');

                    setMeta(newMeta);
                    setMessage({ type: 'success', text: '✓ SEO meta podaci uspješno generisani!' });
                    analyzeContent();
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Greška: ' + error.message });
            } finally {
                setLoadingAll(false);
            }
        };

        const generateTitle = async () => {
            if (!postContent) return;
            setLoadingTitle(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-title',
                    method: 'POST',
                    data: { content: postContent }
                });
                if (response.success && response.title) {
                    updateMeta('_seo_title', response.title);
                    setMessage({ type: 'success', text: '✓ SEO naslov generisan!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: error.message });
            } finally {
                setLoadingTitle(false);
            }
        };

        const generateDescription = async () => {
            if (!postContent) return;
            setLoadingDescription(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/ai/generate-excerpt',
                    method: 'POST',
                    data: { content: postContent }
                });
                if (response.success && response.excerpt) {
                    updateMeta('_seo_description', response.excerpt);
                    setMessage({ type: 'success', text: '✓ Meta opis generisan!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: error.message });
            } finally {
                setLoadingDescription(false);
            }
        };

        const extractKeywords = async () => {
            if (!postContent) return;
            setLoadingKeywords(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/seo/extract-keywords',
                    method: 'POST',
                    data: { content: postContent }
                });
                if (response.success && response.keywords) {
                    updateMeta('_seo_keywords', response.keywords.join(', '));
                    setMessage({ type: 'success', text: '✓ SEO tagovi ekstraktovani!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: error.message });
            } finally {
                setLoadingKeywords(false);
            }
        };

        const generateTakeaways = async () => {
            if (!postId) return;
            setLoadingTakeaways(true);
            setMessage(null);
            try {
                const response = await wp.apiFetch({
                    path: '/dev-theme/v1/aeo/generate-takeaways',
                    method: 'POST',
                    data: { post_id: postId }
                });
                if (response.success && response.takeaways) {
                    updateMeta('_aeo_key_takeaways', response.takeaways);
                    setMessage({ type: 'success', text: '✓ Key Takeaways generisani!' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: error.message });
            } finally {
                setLoadingTakeaways(false);
            }
        };

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
            const autoAnalysisEnabled = window.seoData?.autoAnalysisEnabled !== false;
            if (postContent && postId && autoAnalysisEnabled) {
                const timer = setTimeout(analyzeContent, 2000);
                return () => clearTimeout(timer);
            }
        }, [postContent, postId]);

        // Helpers
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
                            el('span', { style: { fontSize: '18px', fontWeight: 'bold', color: getScoreColor(seoScore) } }, seoScore + '/100')
                        ),
                        el('div', { style: { height: '8px', background: '#e0e0e0', borderRadius: '4px', marginTop: '8px', overflow: 'hidden' } },
                            el('div', { style: { width: seoScore + '%', height: '100%', background: getScoreColor(seoScore), transition: 'width 0.3s ease' } })
                        )
                    )
                ),

                // SEO Title
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } }, 'SEO Naslov'),
                    el(TextControl, {
                        value: seoTitle,
                        onChange: (val) => updateMeta('_seo_title', val),
                        placeholder: postTitle || 'Unesite SEO naslov',
                        help: el('span', {
                            style: { color: (seoTitle || '').length > 60 ? '#dc3232' : ((seoTitle || '').length >= 50 ? '#46b450' : '#666') }
                        }, `${(seoTitle || '').length}/60 karaktera`)
                    }),
                    el(Button, { variant: 'secondary', onClick: generateTitle, disabled: loadingTitle || loadingAll, style: { marginTop: '5px' } },
                        loadingTitle ? el(Spinner) : '🤖 Generiši sa AI'
                    )
                ),

                // Meta Description
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } }, 'Meta Opis'),
                    el(TextareaControl, {
                        value: seoDescription,
                        onChange: (val) => updateMeta('_seo_description', val),
                        placeholder: 'Unesite meta opis',
                        rows: 3,
                        help: el('span', {
                            style: { color: (seoDescription || '').length > 160 ? '#dc3232' : ((seoDescription || '').length >= 150 ? '#46b450' : '#666') }
                        }, `${(seoDescription || '').length}/160 karaktera`)
                    }),
                    el(Button, { variant: 'secondary', onClick: generateDescription, disabled: loadingDescription || loadingAll, style: { marginTop: '5px' } },
                        loadingDescription ? el(Spinner) : '🤖 Generiši sa AI'
                    )
                ),

                // Focus Tags
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } }, 'SEO Tagovi'),
                    el(TextControl, {
                        value: seoKeywords,
                        onChange: (val) => updateMeta('_seo_keywords', val),
                        placeholder: 'npr. WordPress, SEO, optimizacija',
                        help: 'Odvojeno zarezom'
                    }),
                    el(Button, { variant: 'secondary', onClick: extractKeywords, disabled: loadingKeywords || loadingAll, style: { marginTop: '5px' } },
                        loadingKeywords ? el(Spinner) : '🤖 Ekstraktuj sa AI'
                    )
                ),

                // Key Takeaways (AEO)
                el('div', { style: { marginBottom: '15px' } },
                    el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: '600' } }, '🎯 Key Takeaways (AEO)'),
                    el(TextareaControl, {
                        value: aeoKeyTakeaways,
                        onChange: (val) => updateMeta('_aeo_key_takeaways', val),
                        placeholder: '• Prvi ključni point\n• Drugi ključni point\n• Treći ključni point',
                        rows: 5,
                        help: 'AI engines prioritize content with clear takeaways. Add 3-5 key points.'
                    }),
                    el(Button, { variant: 'secondary', onClick: generateTakeaways, disabled: loadingTakeaways || loadingAll, style: { marginTop: '5px' } },
                        loadingTakeaways ? el(Spinner) : '🤖 Generiši sa AI'
                    )
                ),

                // Bulk
                el(PanelRow, {},
                    el(Button, { variant: 'primary', onClick: generateAllWithAI, disabled: loadingAll || loadingTitle || loadingDescription || loadingKeywords, style: { width: '100%', justifyContent: 'center', marginTop: '10px' } },
                        loadingAll ? el(Spinner) : '🤖 Optimiziraj Sve sa AI'
                    )
                ),

                // Advanced
                el(PanelRow, { style: { marginTop: '15px' } },
                    el(Button, { variant: 'link', onClick: () => setShowAdvanced(!showAdvanced) },
                        showAdvanced ? '▼ Sakrij Napredne Postavke' : '▶ Napredne Postavke'
                    )
                ),

                showAdvanced && el(Fragment, null,
                    el('div', { style: { marginTop: '15px', paddingTop: '15px', borderTop: '1px solid #ddd' } },
                        el(TextControl, {
                            label: 'Canonical URL',
                            value: seoCanonical,
                            onChange: (val) => updateMeta('_seo_canonical', val),
                            placeholder: 'Ostavi prazno za default',
                            type: 'url'
                        }),
                        el(CheckboxControl, {
                            label: 'No Index',
                            checked: seoNoindex,
                            onChange: (val) => updateMeta('_seo_noindex', val ? '1' : '0')
                        }),
                        el(CheckboxControl, {
                            label: 'No Follow',
                            checked: seoNofollow,
                            onChange: (val) => updateMeta('_seo_nofollow', val ? '1' : '0')
                        })
                    )
                )
            )
        );
    };

    registerPlugin('seo-content-panel', {
        render: SEOContentPanel,
        icon: 'search'
    });

})();
