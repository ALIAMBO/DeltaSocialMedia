# Image Cropping Implementation Guide

## Overview
The social media application now includes comprehensive image cropping functionality for all photo upload scenarios:
- **Posts**: Square format (1:1 aspect ratio)
- **Stories**: Portrait format (9:16 aspect ratio)  
- **Profile Avatar**: Square format (1:1 aspect ratio)
- **Profile Cover Photo**: Landscape format (3:1 aspect ratio)

## User Flows

### 1. Post Image Cropping
**Flow**: Click photo → Select image → Preview with crop UI → Click post → Save

**Steps**:
1. User clicks the "Photo" button in the post creation form
2. File picker opens
3. User selects an image (max 5MB)
4. Image preview appears with cropper overlay
5. User can:
   - Drag to move the crop area
   - Resize crop box to adjust the frame
   - Cancel to remove the image
6. User clicks "Post" to publish with the cropped image

**Technical Details**:
- Aspect ratio locked to 1:1 (square)
- Canvas output: 1080 x 1080px
- Handled by: `resources/js/cropper/post-cropper.js`
- Form integration: `resources/views/feed/index.blade.php`

### 2. Story Image Cropping
**Flow**: Add Story → Select image → Preview with crop UI → Click upload → Save

**Steps**:
1. User clicks "Add Story" button (+ circle in stories bar)
2. Modal opens with image upload area
3. User selects an image (max 5MB)
4. Image preview appears with cropper overlay (9:16 format)
5. User can:
   - Add optional caption
   - Crop/reposition the image
   - Click "Remove Image" to start over
6. User clicks "Upload" to publish story

**Technical Details**:
- Aspect ratio locked to 9:16 (portrait/vertical)
- Canvas output: 1080 x 1920px
- Handled by: `resources/js/cropper/story-cropper.js`
- Form integration: `resources/views/feed/index.blade.php`
- Alpine.js integration for reactive UI

### 3. Profile Avatar Cropping
**Flow**: Click avatar → Select image → Crop modal → Click apply crop → Click save

**Steps**:
1. User goes to Profile Settings
2. Clicks on the avatar image
3. File picker opens
4. User selects an image (max 5MB)
5. **Crop Modal appears** with:
   - Image preview
   - Crop overlay (1:1 format)
   - Cancel & Apply buttons
6. User adjusts crop area as needed
7. User clicks "Apply Crop" to confirm
8. Modal closes and avatar preview updates
9. User clicks "Save Changes" at bottom to finalize

**Technical Details**:
- Aspect ratio locked to 1:1 (square)
- Canvas output: 500 x 500px
- Handled by: `resources/js/cropper/profile-cropper.js`
- Modal-based interaction (crops before form submission)
- Form integration: `resources/views/profile/edit.blade.php`

### 4. Profile Cover Photo Cropping
**Flow**: Click cover → Select image → Crop modal → Click apply crop → Click save

**Steps**:
1. User goes to Profile Settings
2. Clicks on the cover photo area
3. File picker opens
4. User selects an image (max 5MB)
5. **Crop Modal appears** with:
   - Image preview
   - Crop overlay (3:1 format)
   - Cancel & Apply buttons
6. User adjusts crop area as needed
7. User clicks "Apply Crop" to confirm
8. Modal closes and cover preview updates
9. User clicks "Save Changes" at bottom to finalize

**Technical Details**:
- Aspect ratio locked to 3:1 (landscape/wide)
- Canvas output: 1500 x 500px
- Handled by: `resources/js/cropper/profile-cropper.js`
- Modal-based interaction (crops before form submission)
- Form integration: `resources/views/profile/edit.blade.php`

## File Structure

### JavaScript Cropper Modules
```
resources/js/
├── cropper/                          # Cropper functionality
│   ├── post-cropper.js               # Posts cropping (1:1)
│   ├── story-cropper.js              # Stories cropping (9:16)
│   └── profile-cropper.js            # Profile cropping (1:1 & 3:1)
├── pages/
│   ├── feed-index.js                 # Feed page integration
│   └── profile-edit.js               # Profile settings page integration
```

### Blade Templates
- `resources/views/feed/index.blade.php` - Posts & Stories creation
- `resources/views/profile/edit.blade.php` - Profile avatar & cover
- `resources/views/profile/show.blade.php` - Profile display

### Libraries
- **Cropper.js**: CDN loaded in `resources/views/layouts/app.blade.php`
  - CSS: `https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css`
  - JS: `https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/Cropper.min.js`

## Features

### Image Validation
- Maximum file size: **5MB** per image
- Supported formats: JPG, JPEG, PNG, GIF, WebP
- Error messages displayed inline

### Cropping Controls
- **Move**: Drag to reposition the image
- **Resize**: Drag crop box edges to adjust frame size
- **Guides**: Grid overlay to help with alignment
- **Auto crop**: Automatically fills available space

### UI/UX
- Real-time preview during cropping
- Visual feedback on hover states
- Disabled state for buttons during processing
- Modal overlay for profile photos
- Dark mode support with custom cropper styling

### Output Quality
- JPEG compression: 0.9 quality (90%)
- Optimized for web display
- Files automatically named (post.jpg, story.jpg, avatar.jpg, cover.jpg)

## Code Organization

Following the project's JavaScript organization guidelines:

1. **Separation of Concerns**: Cropper logic in dedicated modules
2. **Blade Clean**: HTML remains focused on structure
3. **Reusable Components**: Each cropper module is self-contained
4. **Module Exports**: Functions exported for use in different contexts
5. **Global Functions**: Key functions attached to `window` for HTML handlers

## Development Notes

### Adding Cropping to New Features

To add image cropping to a new feature:

1. Create a cropper module in `resources/js/cropper/[feature]-cropper.js`
2. Export setup function and cropper control functions
3. Import in the appropriate page file (`resources/js/pages/[page].js`)
4. Update the form/template to use the new cropper

### Customizing Aspect Ratios

Edit the `ASPECT_RATIOS` and `CANVAS_SIZES` objects in the respective cropper files:

```javascript
// Example: Add 16:9 aspect ratio for new feature
const ASPECT_RATIOS = {
    feature: 16 / 9,
    // ...
};
```

### Styling the Cropper

Customize the cropper appearance in the `@push('styles')` sections:

```css
.cropper-bg { background-color: #your-color !important; }
.cropper-view-box { outline-color: #your-color !important; }
.cropper-line, .cropper-point { background-color: #your-color !important; }
```

## Troubleshooting

### Cropper Not Working
1. Check that Cropper.js is loaded: Open DevTools → Network tab
2. Verify CSS is imported in layout
3. Check browser console for JavaScript errors
4. Ensure file input and preview elements have correct IDs

### Image Not Showing in Preview
1. Check file size is under 5MB
2. Verify image format is supported (JPG, PNG, GIF, WebP)
3. Check browser console for FileReader errors

### Modal Not Appearing
1. Verify modal HTML element exists and has correct ID
2. Check that `openCropModal()` function is being called
3. Ensure `document.body.style.overflow` is being set

## Browser Support
- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- Mobile browsers: ✅ Touch support enabled
