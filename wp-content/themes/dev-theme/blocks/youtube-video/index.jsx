import { registerBlockType } from '@wordpress/blocks';
import { TextControl, PanelBody } from '@wordpress/components';
import { InspectorControls, BlockControls, BlockAlignmentToolbar, useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import metadata from './block.json';

console.log('YouTube Video Block Editor Script Loaded v1.0.0');

// Parse YouTube URL to extract video ID
function parseYouTubeUrl(url) {
    if (!url) return null;

    // Standard watch URL: youtube.com/watch?v=VIDEO_ID
    const watchPattern = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
    // Shorts: youtube.com/shorts/VIDEO_ID
    const shortsPattern = /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/;
    // Embed: youtube.com/embed/VIDEO_ID
    const embedPattern = /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/;

    let match = url.match(watchPattern) ||
        url.match(shortsPattern) ||
        url.match(embedPattern);

    return match ? match[1] : null;
}

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { videoUrl, videoId, alignment, caption } = attributes;
        const [error, setError] = useState('');

        const blockProps = useBlockProps({
            className: 'youtube-video-block-editor'
        });

        useEffect(() => {
            if (videoUrl) {
                const extractedId = parseYouTubeUrl(videoUrl);
                if (extractedId) {
                    setAttributes({ videoId: extractedId });
                    setError('');
                } else {
                    setError('Nevažeći YouTube link.');
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
                    <PanelBody title="Postavke">
                        <TextControl
                            label="Opis"
                            value={caption}
                            onChange={(val) => setAttributes({ caption: val })}
                            placeholder="Opis (opcionalno)"
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="wp-block" style={{ padding: '20px', background: '#f7f7f7', border: '1px solid #ddd', borderRadius: '8px' }}>
                    <h4 style={{ margin: '0 0 16px', fontSize: '16px', fontWeight: '600' }}>YouTube Video</h4>

                    <TextControl
                        label="Video link"
                        help="Zalijepite YouTube link videa (youtube.com/watch?v=... ili youtu.be/...)"
                        value={videoUrl}
                        onChange={(val) => setAttributes({ videoUrl: val })}
                        placeholder="https://www.youtube.com/watch?v=..."
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
                                width: '100%',
                                background: '#000',
                                borderRadius: '4px',
                                overflow: 'hidden'
                            }}>
                                <img
                                    src={`https://i.ytimg.com/vi/${videoId}/hqdefault.jpg`}
                                    alt="YouTube video thumbnail"
                                    style={{
                                        display: 'block',
                                        width: '100%',
                                        height: 'auto',
                                        aspectRatio: '16 / 9'
                                    }}
                                />
                                <div style={{
                                    position: 'absolute',
                                    top: '50%',
                                    left: '50%',
                                    transform: 'translate(-50%, -50%)',
                                    pointerEvents: 'none'
                                }}>
                                    <svg width="68" height="48" viewBox="0 0 68 48">
                                        <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
                                        <path d="M 45,24 27,14 27,34" fill="#fff"></path>
                                    </svg>
                                </div>
                            </div>
                            <p style={{ marginTop: '12px', fontSize: '13px', color: '#666' }}>
                                Preview: Click play button on frontend to load video
                            </p>
                        </div>
                    )}
                </div>
            </div>
        );
    },
    save: () => null
});
