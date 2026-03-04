import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
    ToggleControl,
    Spinner,
    Placeholder,
} from '@wordpress/components';
import metadata from './block.json';

console.log('Hero Slider Block: Script Loaded');
console.log('Hero Slider Block: Name from metadata', metadata.name);

registerBlockType('dev-theme/hero-slider', {
    edit: ({ attributes, setAttributes }) => {
        console.log('Hero Slider Block: Edit function called');
        const { categoryId, postsCount, autoplay, autoplayDelay } = attributes;

        const categories = useSelect((select) => {
            return select('core').getEntityRecords('taxonomy', 'category', {
                per_page: -1,
            });
        }, []);

        const blockProps = useBlockProps();

        const categoryOptions = [
            { label: 'Odaberi kategoriju', value: 0 },
            ...(categories || []).map((cat) => ({
                label: cat.name,
                value: cat.id,
            })),
        ];

        const selectedCategory = categories?.find(
            (c) => c.id === parseInt(categoryId)
        );

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Postavke slidera">
                        <SelectControl
                            label="Kategorija"
                            value={categoryId}
                            options={categoryOptions}
                            onChange={(val) =>
                                setAttributes({ categoryId: parseInt(val) })
                            }
                        />
                        <RangeControl
                            label="Broj članaka"
                            value={postsCount}
                            onChange={(val) =>
                                setAttributes({ postsCount: val })
                            }
                            min={4}
                            max={12}
                        />
                        <ToggleControl
                            label="Automatsko reproduciranje"
                            checked={autoplay}
                            onChange={(val) =>
                                setAttributes({ autoplay: val })
                            }
                        />
                        {autoplay && (
                            <RangeControl
                                label="Kašnjenje (ms)"
                                value={autoplayDelay}
                                onChange={(val) =>
                                    setAttributes({ autoplayDelay: val })
                                }
                                min={2000}
                                max={10000}
                                step={500}
                            />
                        )}
                    </PanelBody>
                </InspectorControls>

                <div className="hero-slider-placeholder">
                    {categories === null ? (
                        <Spinner />
                    ) : (
                        <Placeholder
                            icon="slides"
                            label="Hero Slider"
                            instructions="Odaberite kategoriju za prikaz hero banner slidera s člancima."
                        >
                            <div style={{ marginTop: '10px' }}>
                                <strong>Odabrana kategorija: </strong>
                                {selectedCategory
                                    ? selectedCategory.name
                                    : 'Nije odabrana'}
                            </div>
                        </Placeholder>
                    )}
                </div>
            </div>
        );
    },
    save: () => null,
});
