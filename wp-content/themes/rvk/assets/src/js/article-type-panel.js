/**
 * Article Type Gutenberg Panel
 * Tip Članka - Panel za odabir tipa članka
 */

(function(wp) {
    const { registerPlugin } = wp.plugins;
    // Use wp.editor instead of deprecated wp.editPost (WP 6.6+)
    const { PluginDocumentSettingPanel, PluginPrePublishPanel } = wp.editor || wp.editPost;
    const { createElement: el, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { RadioControl, Notice } = wp.components;

    function ArticleTypePanel() {
        // Get current post ID and article type terms
        const { postId, articleType } = useSelect((select) => {
            const editor = select('core/editor');
            const post = editor.getCurrentPost();

            return {
                postId: post.id,
                articleType: editor.getEditedPostAttribute('article-type') || []
            };
        });

        const { editPost, lockPostSaving, unlockPostSaving } = useDispatch('core/editor');

        // Article type options with icons - Bosnian labels
        const options = [
            { label: '📄 Standardni', value: 'standard' },
            { label: '🎥 Video', value: 'video' },
            { label: '🎧 Audio', value: 'audio' },
            { label: '🖼️ Galerija', value: 'galerija' }
        ];

        // Get current selection - default to null (no selection)
        const currentValue = Array.isArray(articleType) && articleType.length > 0
            ? articleType[0]
            : null;

        // Lock/unlock publishing based on article type selection
        useEffect(() => {
            if (!currentValue) {
                lockPostSaving('article-type-required');
            } else {
                unlockPostSaving('article-type-required');
            }
        }, [currentValue, lockPostSaving, unlockPostSaving]);

        // Handle change
        const handleChange = (value) => {
            editPost({
                'article-type': value ? [value] : []
            });
        };

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'article-type-panel',
                title: 'Tip Članka *',
                className: 'article-type-panel',
                priority: 1 // Higher priority = appears first
            },
            el('div', { style: { padding: '16px' } },
                el('p', {
                    style: {
                        marginTop: 0,
                        marginBottom: '12px',
                        fontSize: '13px',
                        color: '#1e1e1e'
                    }
                },
                    el('strong', {}, 'Odaberite tip članka:'),
                    el('span', { style: { color: '#d63638', marginLeft: '4px' } }, '*')
                ),
                !currentValue && el(Notice, {
                    status: 'warning',
                    isDismissible: false,
                    style: { marginBottom: '12px' }
                }, 'Morate odabrati tip članka prije objave.'),
                el(RadioControl, {
                    selected: currentValue,
                    options: options,
                    onChange: handleChange
                })
            )
        );
    }

    // Pre-publish check panel
    function ArticleTypePrePublishPanel() {
        const { articleType } = useSelect((select) => {
            const editor = select('core/editor');
            return {
                articleType: editor.getEditedPostAttribute('article-type') || []
            };
        });

        const currentValue = Array.isArray(articleType) && articleType.length > 0
            ? articleType[0]
            : null;

        if (currentValue) {
            return null; // Don't show if article type is selected
        }

        return el(
            PluginPrePublishPanel,
            {
                className: 'article-type-pre-publish-panel',
                title: 'Tip Članka',
                initialOpen: true
            },
            el(Notice, {
                status: 'error',
                isDismissible: false
            }, '⚠️ Morate odabrati tip članka prije objave.')
        );
    }

    // Register the plugin
    registerPlugin('article-type-panel', {
        render: ArticleTypePanel,
        icon: null
    });

    // Register pre-publish check
    registerPlugin('article-type-pre-publish', {
        render: ArticleTypePrePublishPanel,
        icon: null
    });

})(window.wp);
