import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner, Placeholder } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

console.log('Category Articles Block: Script Loaded');
console.log('Category Articles Block: Metadata', metadata);

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { categoryId, blockTitle } = attributes;

        // Fetch categories
        const categories = useSelect((select) => {
            return select('core').getEntityRecords('taxonomy', 'category', { per_page: -1 });
        }, []);

        const blockProps = useBlockProps();

        // Prepare options for SelectControl
        const categoryOptions = [
            { label: 'Odaberi kategoriju', value: 0 },
            ...(categories || []).map((cat) => ({ label: cat.name, value: cat.id }))
        ];

        const selectedCategory = categories?.find(c => c.id === parseInt(categoryId));

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Postavke">
                        <SelectControl
                            label="Kategorija"
                            value={categoryId}
                            options={categoryOptions}
                            onChange={(val) => setAttributes({ categoryId: parseInt(val) })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="category-articles-placeholder">
                    {categories === null ? (
                        <Spinner />
                    ) : (
                        <Placeholder
                            icon="layout"
                            label="Istaknute vijesti"
                            instructions="Odaberite kategoriju za prikaz najnovijih članaka."
                        >
                            <div style={{ marginTop: '10px' }}>
                                <strong>Odabrana kategorija: </strong>
                                {selectedCategory ? selectedCategory.name : 'Nije odabrana'}
                            </div>
                        </Placeholder>
                    )}
                </div>
            </div>
        );
    },
    save: () => null
});
