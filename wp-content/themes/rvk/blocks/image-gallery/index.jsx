import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, MediaPlaceholder, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { images } = attributes;

        const onSelectImages = (newImages) => {
            const formattedImages = newImages.map(img => ({
                id: img.id,
                url: img.url,
                alt: img.alt,
                caption: img.caption
            }));
            setAttributes({ images: formattedImages });
        };

        const removeImage = (index) => {
            const newImages = [...images];
            newImages.splice(index, 1);
            setAttributes({ images: newImages });
        };

        const blockProps = useBlockProps({
            className: 'rvk-gallery-editor'
        });

        return (
            <div {...blockProps}>
                {images.length === 0 ? (
                    <MediaPlaceholder
                        onSelect={onSelectImages}
                        allowedTypes={['image']}
                        multiple
                        labels={{ title: 'Galerija slika' }}
                        icon="images-alt2"
                    />
                ) : (
                    <div className="rvk-gallery-grid-editor">
                        <div className="gallery-items">
                            {images.map((img, index) => (
                                <div key={img.id || index} className="gallery-item-preview">
                                    <img src={img.url} alt={img.alt} />
                                    <Button
                                        isDestructive
                                        icon="no-alt"
                                        onClick={() => removeImage(index)}
                                        className="remove-img"
                                    />
                                </div>
                            ))}
                        </div>
                        <div className="gallery-actions">
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={(newMedia) => {
                                        const formatted = newMedia.map(item => ({
                                            id: item.id,
                                            url: item.url,
                                            alt: item.alt,
                                            caption: item.caption
                                        }));
                                        setAttributes({ images: [...images, ...formatted] });
                                    }}
                                    allowedTypes={['image']}
                                    multiple
                                    render={({ open }) => (
                                        <Button variant="primary" onClick={open} icon="plus">
                                            Dodaj još slika
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                            <Button variant="secondary" onClick={() => setAttributes({ images: [] })}>
                                Isprazni galeriju
                            </Button>
                        </div>
                    </div>
                )}
                <style>{`
                    .rvk-gallery-grid-editor .gallery-items {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                        gap: 15px;
                        margin-bottom: 20px;
                    }
                    .gallery-item-preview {
                        position: relative;
                        aspect-ratio: 1;
                        background: #f0f0f0;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .gallery-item-preview img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }
                    .gallery-item-preview .remove-img {
                        position: absolute;
                        top: 5px;
                        right: 5px;
                        background: white;
                        border-radius: 50%;
                        padding: 0;
                        width: 24px;
                        height: 24px;
                        min-width: 24px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .gallery-actions {
                        display: flex;
                        gap: 10px;
                    }
                `}</style>
            </div>
        );
    },
    save: () => null
});
