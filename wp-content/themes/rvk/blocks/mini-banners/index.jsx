import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('dev-theme/mini-banners', {
	edit: ({ attributes, setAttributes }) => {
		const blockProps = useBlockProps();
		const { title, subtitle } = attributes;

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Carousel Settings', 'dev-theme')}>
						<TextControl
							label={__('Title (H2)', 'dev-theme')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
							placeholder="Vaš brend, naša priča!"
							help={__('Required field', 'dev-theme')}
						/>
						<TextControl
							label={__('Subtitle (H3)', 'dev-theme')}
							value={subtitle}
							onChange={(value) => setAttributes({ subtitle: value })}
							placeholder="Enter subtitle..."
							help={__('Required field', 'dev-theme')}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div style={{
						padding: '2rem',
						border: '2px dashed #ccc',
						borderRadius: '8px',
						textAlign: 'center',
						backgroundColor: '#f9f9f9'
					}}>
						<h2 style={{ margin: '0 0 0.5rem 0', fontSize: '1.5rem' }}>
							{title || 'Vaš brend, naša priča!'}
						</h2>
						{subtitle && (
							<h3 style={{ margin: '0 0 1rem 0', fontSize: '1.2rem', color: '#666' }}>
								{subtitle}
							</h3>
						)}
						<p style={{ margin: '1rem 0 0 0', color: '#999', fontSize: '0.9rem' }}>
							Mini Banners Carousel<br/>
							<small>Configure banners in Settings → Marketing</small>
						</p>
					</div>
				</div>
			</>
		);
	},

	save: () => null
});
