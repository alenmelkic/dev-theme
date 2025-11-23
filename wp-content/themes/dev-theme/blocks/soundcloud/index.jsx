import { registerBlockType } from '@wordpress/blocks';
import { TextControl, ToggleControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import metadata from './block.json';

console.log('SoundCloud Block Editor Script Loaded');

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { url, height, visual } = attributes;
        const [tracks, setTracks] = useState([]);
        const [loading, setLoading] = useState(false);
        const [error, setError] = useState(null);

        // Fetch tracks on load using WordPress apiFetch
        useEffect(() => {
            setLoading(true);
            setError(null);

            apiFetch({ path: '/dev-theme/v1/soundcloud-tracks' })
                .then(data => {
                    console.log('Tracks data:', data);
                    setTracks(Array.isArray(data) ? data : []);
                    setLoading(false);
                })
                .catch(err => {
                    console.error('Error fetching tracks:', err);
                    setError(err.message || 'Failed to load tracks');
                    setLoading(false);
                });
        }, []);

        return (
            <div className="soundcloud-block-editor">
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Player Height (px)"
                            value={height}
                            onChange={(val) => setAttributes({ height: val })}
                        />
                        <ToggleControl
                            label="Visual Mode"
                            checked={visual}
                            onChange={(val) => setAttributes({ visual: val })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="wp-block" style={{ padding: '20px', background: '#f7f7f7', border: '1px solid #ddd', borderRadius: '8px' }}>
                    {/* Track List Section */}
                    <div style={{ marginBottom: '20px', borderBottom: '2px solid #ddd', paddingBottom: '20px' }}>
                        <h4 style={{ margin: '0 0 12px', fontSize: '16px', fontWeight: '600' }}>Označi audio zapis</h4>
                        {loading && <p style={{ color: '#666' }}>Loading tracks...</p>}
                        {error && <p style={{ color: 'red' }}>Error: {error}</p>}
                        {!loading && !error && tracks.length === 0 && <p style={{ color: '#666' }}>Nije pronađen nijedan zapis.</p>}
                        {!loading && !error && tracks.length > 0 && (
                            <div style={{ maxHeight: '200px', overflowY: 'auto', background: '#fff', border: '1px solid #ddd', borderRadius: '6px' }}>
                                {tracks.map((track, index) => (
                                    <div
                                        key={index}
                                        onClick={() => setAttributes({ url: track.url })}
                                        style={{
                                            padding: '12px',
                                            borderBottom: index < tracks.length - 1 ? '1px solid #eee' : 'none',
                                            cursor: 'pointer',
                                            background: url === track.url ? '#e3f2fd' : 'transparent',
                                            transition: 'background 0.2s ease'
                                        }}
                                        onMouseEnter={(e) => {
                                            if (url !== track.url) e.currentTarget.style.background = '#f5f5f5';
                                        }}
                                        onMouseLeave={(e) => {
                                            if (url !== track.url) e.currentTarget.style.background = 'transparent';
                                        }}
                                    >
                                        <div style={{ fontWeight: url === track.url ? '600' : '500', fontSize: '14px', marginBottom: '4px', color: '#333' }}>
                                            {track.title}
                                        </div>
                                        <div style={{ fontSize: '12px', color: '#666' }}>{track.date}</div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* URL Input */}
                    <TextControl
                        label="SoundCloud Link"
                        help="Odaberi sa liste iznad ili zalijepi prilagođeni URL."
                        value={url}
                        onChange={(val) => setAttributes({ url: val })}
                        placeholder="https://soundcloud.com/..."
                        style={{ marginBottom: '20px' }}
                    />
                </div>
            </div>
        );
    },
    save: () => null // Dynamic block
});
