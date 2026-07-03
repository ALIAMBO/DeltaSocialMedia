@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<!-- Define fileValidation function BEFORE Alpine tries to use it -->
<script>
window.fileValidation = function() {
    return {
        showModal: false,
        modalMessage: '',

        openModal(message) {
            this.modalMessage = message;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = 'auto';
        },
        handleSubmit(e) {},

        init() {
            if (this._initialized) return;
            this._initialized = true;

            // Avatar wrapper click
            var avatarWrapper = document.getElementById('avatar-wrapper');
            if (avatarWrapper) {
                avatarWrapper.addEventListener('click', function() {
                    document.getElementById('avatar-input').click();
                });
            }

            // Cover wrapper click
            var coverWrapper = document.getElementById('cover-wrapper');
            if (coverWrapper) {
                coverWrapper.addEventListener('click', function() {
                    document.getElementById('cover-input').click();
                });
            }

            // Avatar file change → open crop modal
            var avatarInput = document.getElementById('avatar-input');
            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    if (!file) return;
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image must be less than 5MB.');
                        e.target.value = '';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        openCropModal('avatar', ev.target.result, 1, 'Crop Profile Picture');
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Cover file change → open crop modal
            var coverInput = document.getElementById('cover-input');
            if (coverInput) {
                coverInput.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    if (!file) return;
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image must be less than 5MB.');
                        e.target.value = '';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        openCropModal('cover', ev.target.result, 3, 'Crop Cover Photo');
                    };
                    reader.readAsDataURL(file);
                });
            }

            this.checkForValidationErrors();
        },

        checkForValidationErrors() {
            var container = document.getElementById('validation-errors');
            if (!container) return;
            try {
                var errorsText = container.textContent.trim();
                if (!errorsText) return;
                var errors = JSON.parse(errorsText);
                if (Object.keys(errors).length === 0) return;
                var errorMessage = '';
                for (var field in errors) {
                    errorMessage += errors[field].join('\n') + '\n';
                }
                this.openModal(errorMessage.trim());
            } catch (e) {}
        }
    };
};

// Shared crop modal state
var _cropTarget = null; // 'avatar' or 'cover'
var _cropInstance = null;

function openCropModal(target, src, aspectRatio, title) {
    _cropTarget = target;
    var modal = document.getElementById('cropper-modal');
    var img = document.getElementById('cropper-img');
    var titleEl = document.getElementById('cropper-title');
    if (titleEl) titleEl.textContent = title;
    img.src = src;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (_cropInstance) { _cropInstance.destroy(); _cropInstance = null; }
    setTimeout(function() {
        _cropInstance = new Cropper(img, {
            aspectRatio: aspectRatio,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false
        });
    }, 50);
}

function cancelCrop() {
    if (_cropInstance) { _cropInstance.destroy(); _cropInstance = null; }
    // Reset the file input for whichever target was being cropped
    if (_cropTarget === 'avatar') {
        document.getElementById('avatar-input').value = '';
    } else if (_cropTarget === 'cover') {
        document.getElementById('cover-input').value = '';
    }
    _cropTarget = null;
    document.getElementById('cropper-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function applyCrop() {
    if (!_cropInstance || !_cropTarget) return;
    var canvas = _cropInstance.getCroppedCanvas({ width: _cropTarget === 'cover' ? 1500 : 500, height: _cropTarget === 'cover' ? 500 : 500 });
    if (!canvas) return;
    canvas.toBlob(function(blob) {
        var filename = _cropTarget === 'cover' ? 'cover.jpg' : 'avatar.jpg';
        var file = new File([blob], filename, { type: 'image/jpeg' });
        var dt = new DataTransfer();
        dt.items.add(file);

        if (_cropTarget === 'avatar') {
            document.getElementById('avatar-input').files = dt.files;
            document.getElementById('avatar-preview').src = canvas.toDataURL('image/jpeg', 0.9);
        } else {
            document.getElementById('cover-input').files = dt.files;
            var coverImg = document.getElementById('cover-img');
            coverImg.src = canvas.toDataURL('image/jpeg', 0.9);
            coverImg.classList.remove('hidden');
        }

        if (_cropInstance) { _cropInstance.destroy(); _cropInstance = null; }
        _cropTarget = null;
        document.getElementById('cropper-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 'image/jpeg', 0.9);
}
</script>

<!-- Hidden container for backend validation errors (read by profile-edit.js) -->
<div id="validation-errors" class="hidden">{{ json_encode($errors->messages()) }}</div>

<div class="max-w-2xl mx-auto" x-data="fileValidation()" x-init="init()">
    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-5">Profile Settings</h1>

    <!-- File Size Warning Modal -->
    <template x-if="showModal">
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.away="closeModal()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-sm w-full mx-4 overflow-hidden transition-colors" @click.stop>
                <!-- Header -->
                <div class="bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-red-900 dark:text-red-200">File Size Too Large</h2>
                    </div>
                    <button @click="closeModal()" class="text-red-400 dark:text-red-300 hover:text-red-600 dark:hover:text-red-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300" x-text="modalMessage"></p>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex justify-end">
                    <button @click="closeModal()"
                            class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 transition">
                        Got It
                    </button>
                </div>
            </div>
        </div>
    </template>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors" @submit="handleSubmit">
        @csrf @method('PUT')

        {{-- File inputs live here, outside any clickable div --}}
        <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden">
        <input type="file" id="cover-input" name="cover_photo" accept="image/*" class="hidden">

        <!-- Cover Photo -->
        <div class="relative">
            {{-- Cover strip --}}
            <div id="cover-wrapper"
                 class="h-36 bg-gradient-to-r from-green-400 to-green-500 rounded-t-2xl overflow-hidden cursor-pointer group">
                <img id="cover-img"
                     src="{{ $user->profile?->cover_photo ? $user->profile->cover_url : '' }}"
                     alt="Cover"
                     class="w-full h-full object-cover {{ $user->profile?->cover_photo ? '' : 'hidden' }}">
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30
                            flex items-center justify-center transition pointer-events-none">
                    <span class="text-white text-sm font-medium opacity-0 group-hover:opacity-100">
                        Change Cover Photo
                    </span>
                </div>
            </div>

            {{-- Avatar: bottom of cover, shifted down by half its own height (40px = half of 80px / w-20) --}}
            <div id="avatar-wrapper"
                 class="absolute left-6 cursor-pointer group/av z-10" style="bottom: 3px;">
                <img id="avatar-preview"
                     src="{{ $user->profile?->avatar_url ?? asset('images/default-avatar.png') }}"
                     class="w-20 h-20 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow"
                     alt="avatar">
                <div class="absolute inset-0 rounded-full bg-black bg-opacity-0
                            group-hover/av:bg-opacity-40 flex items-center justify-center transition">
                    <svg class="w-5 h-5 text-white opacity-0 group-hover/av:opacity-100"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- pt-12 = half avatar (40px) + 8px gap --}}
        <div class="px-6 pb-6 pt-14">
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">Click the avatar or cover area to change photos</p>

            <!-- Form Fields -->
            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Display Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Bio</label>
                    <textarea name="bio" rows="3"
                              placeholder="Tell people a little about yourself..."
                              class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl px-4 py-2.5 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 resize-none transition-colors">{{ old('bio', $user->profile?->bio) }}</textarea>
                    @error('bio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Location & Website -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Location</label>
                        <input type="text" name="location"
                               value="{{ old('location', $user->profile?->location) }}"
                               placeholder="City, Country"
                               class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                        @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Website</label>
                        <input type="text" name="website"
                               value="{{ old('website', $user->profile?->website) }}"
                               placeholder="https://yoursite.com"
                               class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                        @error('website') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Birth Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Birth Date</label>
                    <input type="date" name="birth_date"
                           value="{{ old('birth_date', $user->profile?->birth_date?->format('Y-m-d')) }}"
                           class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500 transition-colors">
                    @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end mt-6">
                <button type="submit" id="submit-btn"
                        class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-semibold
                               px-8 py-2.5 rounded-full transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Cropper Modal -->
    <div id="cropper-modal" class="fixed inset-0 bg-black bg-opacity-75 flex flex-col items-center justify-center z-50 hidden p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full transition-colors flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100" id="cropper-title">Crop Image</h3>
                <button type="button" onclick="cancelCrop()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Crop Container -->
            <div class="p-4 flex-1 bg-gray-50 dark:bg-gray-900/50" style="min-height:0;">
                <div id="cropper-img-wrap" style="width:100%;height:400px;max-height:50vh;position:relative;">
                    <img id="cropper-img" src="" style="display:block;max-width:100%;max-height:100%;">
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                <button type="button" onclick="cancelCrop()" class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="button" onclick="applyCrop()" class="px-5 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 transition">
                    Apply Crop
                </button>
            </div>
        </div>
    </div>
</div>


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
.cropper-bg { background-image: none !important; background-color: #09090b !important; }
.cropper-view-box { outline: 2px solid #16a34a !important; outline-color: #16a34a !important; }
.cropper-line, .cropper-point { background-color: #16a34a !important; }
</style>
@endpush

@endsection
