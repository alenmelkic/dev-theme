import { registerBlockType } from '@wordpress/blocks';
import { TextControl, PanelBody, SelectControl, Button } from '@wordpress/components';
import { InspectorControls, BlockControls, BlockAlignmentToolbar, useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

console.log('Facebook Video Block Editor Script Loaded v1.0.2');

// Parse Facebook video URL to extract video ID
function parseFacebookVideoUrl(url) {
    if (!url) return null;

    const reelPattern = /facebook\.com\/reel\/(\d+)/;
    const watchPattern = /facebook\.com\/watch\/\?v=(\d+)/;
    const videosPattern = /facebook\.com\/[^/]+\/videos\/(\d+)/;
    const fbWatchPattern = /fb\.watch\/([a-zA-Z0-9_-]+)/;
    const sharePattern = /facebook\.com\/share\/[vr]\/([a-zA-Z0-9_-]+)/;

    let match = url.match(reelPattern) ||
        url.match(watchPattern) ||
        url.match(videosPattern) ||
        url.match(fbWatchPattern) ||
        url.match(sharePattern);

    return match ? match[1] : null;
}

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { videoUrl, videoId, alignment, caption, thumbnailUrl } = attributes;
        const [error, setError] = useState('');

        const blockProps = useBlockProps({
            className: 'facebook-video-block-editor'
        });

        useEffect(() => {
            if (videoUrl) {
                const extractedId = parseFacebookVideoUrl(videoUrl);
                if (extractedId) {
                    setAttributes({ videoId: extractedId });
                    setError('');
                } else {
                    setError('Nevažeći link videa na Facebooku.');
                    setAttributes({ videoId: '' });
                }
            } else {
                setAttributes({ videoId: '' });
                setError('');
            }
        }, [videoUrl]);

        return (
            <div {...blockProps}>
                <BlockControls>
                    <BlockAlignmentToolbar
                        value={alignment}
                        onChange={(val) => setAttributes({ alignment: val })}
                        controls={['left', 'center', 'right']}
                    />
                </BlockControls>

                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Opis"
                            value={caption}
                            onChange={(val) => setAttributes({ caption: val })}
                            placeholder="Opis (opcionalno)"
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="wp-block" style={{ padding: '20px', background: '#f7f7f7', border: '1px solid #ddd', borderRadius: '8px' }}>
                    <h4 style={{ margin: '0 0 16px', fontSize: '16px', fontWeight: '600' }}>Facebook Video</h4>

                    <TextControl
                        label="Video link"
                        help="Zalijepite Facebook link video zapisa. Link mora biti u formatu https://www.facebook.com/reel/..."
                        value={videoUrl}
                        onChange={(val) => setAttributes({ videoUrl: val })}
                        placeholder="https://www.facebook.com/reel/..."
                    />



                    {error && (
                        <div style={{
                            padding: '12px',
                            background: '#fee',
                            border: '1px solid #fcc',
                            borderRadius: '4px',
                            color: '#c33',
                            marginTop: '16px'
                        }}>
                            {error}
                        </div>
                    )}

                    {videoId && !error && (
                        <div style={{
                            background: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '6px',
                            padding: '20px',
                            textAlign: 'center',
                            marginTop: '16px'
                        }}>
                            <div style={{
                                position: 'relative',
                                paddingBottom: '56.25%',
                                height: 0,
                                overflow: 'hidden',
                                background: '#f0f0f0',
                                borderRadius: '4px'
                            }}>
                                <iframe
                                    src={`https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(videoUrl)}&show_text=false&width=560`}
                                    style={{
                                        position: 'absolute',
                                        top: 0,
                                        left: 0,
                                        width: '100%',
                                        height: '100%',
                                        border: 'none'
                                    }}
                                    scrolling="no"
                                    frameBorder="0"
                                    allowFullScreen={true}
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                />
                            </div>
                        </div>
                    )}
                </div>
            </div>
        );
    },
    save: () => null
});
