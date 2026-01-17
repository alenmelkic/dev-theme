import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
    CheckboxControl,
    Placeholder,
    Spinner
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

console.log('Post Listings Block: Script Loaded');

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const {
            categoryIds,
            tagIds,
            taxonomyFilters,
            articleType,
            layout,
            postsPerPage,
            orderBy,
            order
        } = attributes;

        // Fetch categories
        const categories = useSelect((select) => {
            return select('core').getEntityRecords('taxonomy', 'category', {
                per_page: -1,
                orderby: 'name',
                order: 'asc'
            });
        }, []);

        // Fetch tags
        const tags = useSelect((select) => {
            return select('core').getEntityRecords('taxonomy', 'post_tag', {
                per_page: -1,
                orderby: 'name',
                order: 'asc'
            });
        }, []);

        // Fetch all registered taxonomies
        const taxonomies = useSelect((select) => {
            const allTaxonomies = select('core').getTaxonomies({ per_page: -1 });
            // Filter to only public taxonomies, excluding category and post_tag (handled separately)
            return allTaxonomies?.filter(tax =>
                tax.visibility?.public &&
                !['category', 'post_tag', 'post_format', 'nav_menu', 'link_category'].includes(tax.slug)
            );
        }, []);

        // Fetch terms for each custom taxonomy dynamically
        const [customTaxonomyTerms, setCustomTaxonomyTerms] = useState({});

        useEffect(() => {
            if (!taxonomies) return;

            const fetchTermsForTaxonomies = async () => {
                const termsMap = {};

                for (const taxonomy of taxonomies) {
                    try {
                        const terms = await wp.data.select('core').getEntityRecords('taxonomy', taxonomy.slug, {
                            per_page: -1,
                            orderby: 'name',
                            order: 'asc'
                        });
                        termsMap[taxonomy.slug] = terms || [];
                    } catch (error) {
                        console.error(`Error fetching terms for ${taxonomy.slug}:`, error);
                        termsMap[taxonomy.slug] = [];
                    }
                }

                setCustomTaxonomyTerms(termsMap);
            };

            fetchTermsForTaxonomies();
        }, [taxonomies]);

        const blockProps = useBlockProps({
            className: 'post-listings-editor'
        });

        // Helper function to toggle category selection
        const toggleCategory = (categoryId) => {
            const newCategories = categoryIds.includes(categoryId)
                ? categoryIds.filter(id => id !== categoryId)
                : [...categoryIds, categoryId];
            setAttributes({ categoryIds: newCategories });
        };

        // Helper function to toggle tag selection
        const toggleTag = (tagId) => {
            const newTags = tagIds.includes(tagId)
                ? tagIds.filter(id => id !== tagId)
                : [...tagIds, tagId];
            setAttributes({ tagIds: newTags });
        };

        // Helper function to toggle taxonomy term selection
        const toggleTaxonomyTerm = (taxonomySlug, termId) => {
            const currentTerms = taxonomyFilters[taxonomySlug] || [];
            const newTerms = currentTerms.includes(termId)
                ? currentTerms.filter(id => id !== termId)
                : [...currentTerms, termId];

            setAttributes({
                taxonomyFilters: {
                    ...taxonomyFilters,
                    [taxonomySlug]: newTerms
                }
            });
        };

        // Count total filters applied
        const totalFilters = categoryIds.length + tagIds.length +
            Object.values(taxonomyFilters).reduce((sum, terms) => sum + (Array.isArray(terms) ? terms.length : 0), 0) +
            (articleType !== 'all' ? 1 : 0);

        return (
            <div {...blockProps}>
                <InspectorControls>
                    {/* Filter Settings Panel */}
                    <PanelBody title="Filter Settings" initialOpen={true}>

                        {/* Categories Multi-Select */}
                        <div className="components-base-control">
                            <label className="components-base-control__label">
                                Categories ({categoryIds.length} selected)
                            </label>
                            {categories === null ? (
                                <Spinner />
                            ) : (
                                <div className="post-listings-checkbox-group">
                                    {categories?.map((cat) => (
                                        <CheckboxControl
                                            key={cat.id}
                                            label={cat.name}
                                            checked={categoryIds.includes(cat.id)}
                                            onChange={() => toggleCategory(cat.id)}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Tags Multi-Select */}
                        <div className="components-base-control" style={{ marginTop: '16px' }}>
                            <label className="components-base-control__label">
                                Tags ({tagIds.length} selected)
                            </label>
                            {tags === null ? (
                                <Spinner />
                            ) : (
                                <div className="post-listings-checkbox-group">
                                    {tags?.map((tag) => (
                                        <CheckboxControl
                                            key={tag.id}
                                            label={tag.name}
                                            checked={tagIds.includes(tag.id)}
                                            onChange={() => toggleTag(tag.id)}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Custom Taxonomies */}
                        {taxonomies?.map((taxonomy) => {
                            const terms = customTaxonomyTerms[taxonomy.slug] || [];
                            const selectedCount = (taxonomyFilters[taxonomy.slug] || []).length;

                            return (
                                <div key={taxonomy.slug} className="components-base-control" style={{ marginTop: '16px' }}>
                                    <label className="components-base-control__label">
                                        {taxonomy.name} ({selectedCount} selected)
                                    </label>
                                    {terms.length === 0 ? (
                                        <Spinner />
                                    ) : (
                                        <div className="post-listings-checkbox-group">
                                            {terms.map((term) => (
                                                <CheckboxControl
                                                    key={term.id}
                                                    label={term.name}
                                                    checked={(taxonomyFilters[taxonomy.slug] || []).includes(term.id)}
                                                    onChange={() => toggleTaxonomyTerm(taxonomy.slug, term.id)}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}

                        {/* Article Type Selector */}
                        <SelectControl
                            label="Article Type (Tip Clanka)"
                            value={articleType}
                            options={[
                                { label: 'All Types', value: 'all' },
                                { label: 'Standard', value: 'standard' },
                                { label: 'Video', value: 'video' },
                                { label: 'Audio', value: 'audio' },
                                { label: 'Galerija', value: 'galerija' }
                            ]}
                            onChange={(val) => setAttributes({ articleType: val })}
                            help="Filter by article type"
                        />
                    </PanelBody>

                    {/* Layout Settings Panel */}
                    <PanelBody title="Layout Settings" initialOpen={true}>
                        <SelectControl
                            label="Layout Style"
                            value={layout}
                            options={[
                                { label: 'Cards Layout 1 (3-column grid)', value: 'cards-1' },
                                { label: 'Cards Layout 2 (Featured style)', value: 'cards-2' },
                                { label: 'List Layout (Horizontal rows)', value: 'list' }
                            ]}
                            onChange={(val) => setAttributes({ layout: val })}
                        />

                        <RangeControl
                            label="Number of Posts"
                            value={postsPerPage}
                            onChange={(val) => setAttributes({ postsPerPage: val })}
                            min={1}
                            max={24}
                            help={`Display ${postsPerPage} posts`}
                        />
                    </PanelBody>

                    {/* Sorting Settings Panel */}
                    <PanelBody title="Sorting" initialOpen={false}>
                        <SelectControl
                            label="Order By"
                            value={orderBy}
                            options={[
                                { label: 'Date Published', value: 'date' },
                                { label: 'Date Modified', value: 'modified' },
                                { label: 'Title', value: 'title' }
                            ]}
                            onChange={(val) => setAttributes({ orderBy: val })}
                        />

                        <SelectControl
                            label="Order"
                            value={order}
                            options={[
                                { label: 'Descending (Newest first)', value: 'DESC' },
                                { label: 'Ascending (Oldest first)', value: 'ASC' }
                            ]}
                            onChange={(val) => setAttributes({ order: val })}
                        />
                    </PanelBody>
                </InspectorControls>

                {/* Editor Preview */}
                <Placeholder
                    icon="grid-view"
                    label="Post Listings"
                    instructions="Configure post filters and layout in the sidebar."
                >
                    <div style={{ padding: '20px', textAlign: 'left' }}>
                        <p><strong>Layout:</strong> {
                            layout === 'cards-1' ? 'Cards Layout 1 (3-column)' :
                            layout === 'cards-2' ? 'Cards Layout 2 (Featured)' :
                            'List Layout'
                        }</p>
                        <p><strong>Posts:</strong> {postsPerPage}</p>
                        <p><strong>Filters Applied:</strong> {totalFilters}</p>
                        {categoryIds.length > 0 && <p>Categories: {categoryIds.length}</p>}
                        {tagIds.length > 0 && <p>Tags: {tagIds.length}</p>}
                        {articleType !== 'all' && <p>Article Type: {articleType}</p>}
                    </div>
                </Placeholder>
            </div>
        );
    },
    save: () => null
});
