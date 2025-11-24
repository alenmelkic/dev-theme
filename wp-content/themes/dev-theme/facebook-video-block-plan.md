# Facebook Video Block Implementation Plan

## Goal
Create a custom WordPress Gutenberg block for embedding Facebook videos with optimal performance using a facade pattern (lazy loading).

## User Requirements
- Just video playback (no social features needed)
- Support videos from Radio's Facebook page
- Option to embed videos from other Facebook pages
- **Critical**: No impact on page loading speed

## Proposed Solution: Facade Pattern with Lazy Loading

### Architecture
```
blocks/facebook-video/
├── index.jsx              // Block registration
├── edit.jsx               // Editor component
├── save.jsx               // Save component (outputs data attributes)
├── facebook-video.php     // Server-side rendering & enqueue
├── facebook-video.scss    // Block styles
└── facebook-video.js      // Frontend lazy loader
```

## Implementation Details

### Phase 1: Block Setup
- [x] Create block directory structure
- [ ] Register block in WordPress
- [ ] Set up build process (wp-scripts)

### Phase 2: Editor Interface
- [ ] Create URL input field
- [ ] Add video preview in editor
- [ ] Extract video ID from Facebook URL
- [ ] Validate Facebook video URLs
- [ ] Add block controls (alignment, caption)

### Phase 3: Frontend Facade
- [ ] Create lightweight placeholder component
- [ ] Extract video thumbnail from Facebook
- [ ] Add play button overlay
- [ ] Style to match theme aesthetic

### Phase 4: Lazy Loading System
- [ ] Implement click-to-load functionality
- [ ] Load Facebook SDK on first video click
- [ ] Initialize Facebook embed player
- [ ] Handle multiple videos on same page
- [ ] Add loading state/spinner

### Phase 5: Performance Optimization
- [ ] Implement Intersection Observer (load when near viewport)
- [ ] Ensure Facebook SDK loads only once
- [ ] Add proper iframe lazy loading attributes
- [ ] Optimize thumbnail loading

### Phase 6: Integration & Polish
- [ ] Match SoundCloud block design patterns
- [ ] Add responsive design
- [ ] Test with Swup page transitions
- [ ] Ensure no conflicts with existing players
- [ ] Add error handling for invalid URLs

## Technical Specifications

### Facebook Video URL Formats Supported
```
https://www.facebook.com/watch/?v=VIDEO_ID
https://www.facebook.com/PAGE_NAME/videos/VIDEO_ID
https://fb.watch/SHORT_CODE
```

### Facade Pattern Flow
1. **Initial State**: Show thumbnail + play button (2KB)
2. **User Clicks**: Load Facebook SDK if not loaded (~50KB, once)
3. **SDK Ready**: Replace facade with Facebook embed
4. **Video Plays**: Full Facebook player functionality

### Performance Metrics Target
- **Initial Load**: 0ms delay (no external resources)
- **Time to Interactive**: No impact
- **Largest Contentful Paint**: No impact
- **First Click to Play**: < 500ms (SDK load time)

## Facebook SDK Integration

### Loading Strategy
```javascript
// Load SDK only once, globally
if (!window.FB) {
  loadFacebookSDK();
}
// Initialize player after SDK ready
FB.XFBML.parse();
```

### Privacy Considerations
- Facebook SDK will track users who click play
- No tracking before user interaction
- Consider adding privacy notice in block description

## Block Attributes Schema
```javascript
{
  videoUrl: {
    type: 'string',
    default: ''
  },
  videoId: {
    type: 'string',
    default: ''
  },
  alignment: {
    type: 'string',
    default: 'center'
  },
  caption: {
    type: 'string',
    default: ''
  }
}
```

## Styling Approach
- Match existing SoundCloud block aesthetic
- Responsive 16:9 aspect ratio
- Smooth transition from facade to player
- Loading spinner during SDK initialization
- Mobile-optimized controls

## Testing Checklist
- [ ] Test with various Facebook video URL formats
- [ ] Test multiple videos on same page
- [ ] Test with Swup page transitions
- [ ] Test on mobile devices
- [ ] Test loading performance (Lighthouse)
- [ ] Test with slow network (3G)
- [ ] Test error states (invalid URL, deleted video)

## Verification Plan

### Performance Testing
- Run Lighthouse before/after adding block
- Verify no impact on page load metrics
- Test with 5+ videos on one page

### Functionality Testing
- Embed video from Radio's Facebook page
- Embed video from other Facebook pages
- Test in WordPress editor
- Test on frontend
- Test with radio player (ensure no conflicts)

## Estimated Timeline
- **Phase 1-2**: Block setup & editor (1-2 hours)
- **Phase 3-4**: Facade & lazy loading (2-3 hours)
- **Phase 5-6**: Optimization & polish (1-2 hours)
- **Total**: 4-7 hours of development

## Success Criteria
✅ Videos embed correctly from any Facebook page
✅ Zero impact on initial page load speed
✅ Smooth user experience (click to play)
✅ Works with existing Swup navigation
✅ No conflicts with radio/SoundCloud players
✅ Responsive on all devices
