@extends('dashboard')

@section('title')
    Edit Profile
@endsection

@push('styles')
    @vite('resources/css/edit-profile.css')
@endpush

@section('content')
    @php
        $profileUser = $profileUser ?? Auth::user();
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        $avatarUrl = $profileUser->getAvatarUrl();
        $profileInitial = \Illuminate\Support\Str::of($profileUser->name)->trim()->substr(0, 1)->upper()->toString();
        $dateOfBirth = old(
            'user_dob',
            $profileUser->user_dob
                ? \Illuminate\Support\Carbon::parse($profileUser->user_dob)->format('Y-m-d')
                : ''
        );
        $bio = old('user_bio', $profileUser->user_bio ?? '');
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} edit-profile-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content edit-profile-content" aria-labelledby="editProfileTitle">
            <section
                class="edit-profile-shell"
                x-data="editProfilePage({
                    avatarUrl: @js($avatarUrl),
                    initial: @js($profileInitial),
                    name: @js($profileUser->name),
                    bio: @js($bio),
                })"
                data-edit-profile-page
            >
                <header class="edit-profile-header">
                    <h1 id="editProfileTitle">
                        <x-fas-user class="edit-profile-heading-icon" aria-hidden="true" />
                        <span>Profile Settings</span>
                    </h1>
                    <p>Manage your personal information and profile details.</p>
                </header>

                <section class="profile-card">
                    <form method="POST" action="{{ route('edit-profile.update') }}" id="profile-form" data-edit-profile-form @submit.prevent="submitForm($el)">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="cropped_avatar" x-model="croppedAvatar" data-cropped-avatar>

                        <div class="profile-card-grid">
                            <aside class="photo-section">
                                <label>Profile Picture</label>

                                <div class="avatar-container">
                                    <div class="avatar-frame">
                                        <template x-if="avatarPreview">
                                            <img :src="avatarPreview" alt="{{ $profileUser->name }}" class="avatar" id="avatar-preview">
                                        </template>
                                        <template x-if="! avatarPreview">
                                            <span class="avatar-fallback" x-text="initial"></span>
                                        </template>
                                    </div>

                                    <button type="button" class="camera-btn" id="camera-button" aria-label="Choose profile photo"
                                        @click="choosePhoto">
                                        <x-fas-camera class="size-4" aria-hidden="true" />
                                    </button>
                                </div>

                                <p class="file-info">JPG or PNG or Animated GIF up to 1MB.</p>
                                <input type="file" id="photo-input" x-ref="photoInput" accept="image/png,image/jpeg,image/gif"
                                    hidden @change="handlePhoto">
                                <button type="button" class="upload-btn" id="upload-button" @click="choosePhoto">
                                    Upload Photo
                                </button>
                            </aside>

                            <div class="form-section">

                                <div class="field-group">
                                    <label for="full-name">Name</label>
                                    <input type="text" id="full-name" name="name" value="{{ old('name', $profileUser->name) }}"
                                        autocomplete="name" required @class(['is-invalid' => $errors->has('name')])>
                                </div>

                                <div class="field-group">
                                    <label for="dob">Date of Birth</label>
                                    <input type="date" id="dob" name="user_dob" value="{{ $dateOfBirth }}"
                                        max="{{ now()->format('Y-m-d') }}" @class(['is-invalid' => $errors->has('user_dob')])>
                                </div>

                                <div class="field-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $profileUser->email) }}"
                                        autocomplete="email" required @class(['is-invalid' => $errors->has('email')]) >
                                </div>

                                <div class="field-group">
                                    <label for="phone">Phone Number</label>
                                    <div @class(['phone-input-wrapper', 'is-invalid' => $errors->has('user_phone')])>
                                        <span class="phone-prefix">+95</span>
                                        <input type="tel" id="phone" name="user_phone"
                                            value="{{ old('user_phone', $profileUser->user_phone) }}" autocomplete="tel"
                                            maxlength="13" placeholder="9 123 456 789"
                                            x-ref="phoneInput"
                                            @input="formatPhoneInput"
                                            @blur="formatPhoneInput">
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label for="bio">Bio</label>
                                    <textarea id="bio" name="user_bio" maxlength="260" rows="4"
                                        placeholder="Write something about yourself..." x-model="bio"
                                        @class(['is-invalid' => $errors->has('user_bio')])>{{ $bio }}</textarea>
                                    <small id="bio-counter" data-bio-counter x-text="`${bio.length} / 260`"></small>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="save-btn" data-edit-profile-save :class="{ 'is-loading': isSubmitting }" :disabled="isSubmitting">
                                        <span class="button-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="save-btn-icon" viewBox="0 0 16 16"
                                                fill="currentColor" aria-hidden="true">
                                                <path
                                                    d="M8.5 1.5A1.5 1.5 0 0 1 10 3v1.5A1.5 1.5 0 0 1 8.5 6h-3A1.5 1.5 0 0 1 4 4.5v-3h4.5Z" />
                                                <path
                                                    d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.061.44l3.914 3.913A1.5 1.5 0 0 1 16 5.414V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5h-.5A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0H2v1.5Zm3 9A1 1 0 0 0 4 11.5V16h8v-4.5a1 1 0 0 0-1-1H5Z" />
                                            </svg>
                                            <span>Save Changes</span>
                                        </span>
                                        <span class="button-loader" aria-hidden="true">
                                            <i></i><i></i><i></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <div class="crop-modal" id="crop-modal" x-show="cropOpen" x-cloak x-transition.opacity
                    role="dialog" aria-modal="true" aria-labelledby="crop-title" @keydown.escape.window="closeCrop">
                    <div class="crop-dialog" @click.outside="closeCrop">
                        <div class="crop-header">
                            <h2 id="crop-title">Crop Profile Photo</h2>
                            <button type="button" class="crop-close" aria-label="Close crop window" @click="closeCrop">
                                &times;
                            </button>
                        </div>
                        <div class="crop-body">
                            <div class="crop-stage" aria-label="Drag photo to reposition crop"
                                @pointerdown="startDrag"
                                @pointermove="dragCrop"
                                @pointerup="stopDrag"
                                @pointercancel="stopDrag">
                                <img src="" alt="Selected profile preview" class="crop-image" x-ref="cropImage"
                                    :src="cropImageSrc"
                                    :style="`width: ${cropImageWidth}px; height: ${cropImageHeight}px; transform: translate(calc(-50% + ${cropX}px), calc(-50% + ${cropY}px));`"
                                    @load="prepareCropImage">
                                <div class="crop-guide"></div>
                            </div>
                            <div class="crop-control">
                                <label for="crop-zoom">Zoom</label>
                                <input type="range" id="crop-zoom" min="1" max="3" step="0.01"
                                    x-model.number="cropZoom" @input="syncCropImage">
                            </div>
                        </div>
                        <div class="crop-actions">
                            <button type="button" class="crop-cancel" @click="closeCrop">Cancel</button>
                            <button type="button" class="crop-apply" @click="applyCrop">Apply Crop</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        function editProfilePage(config) {
            return {
                avatarPreview: config.avatarUrl || '',
                initial: config.initial || '?',
                croppedAvatar: '',
                bio: config.bio || '',
                formMessage: '',
                formMessageType: 'success',
                init() {
                    if (this.$refs.phoneInput) {
                        let value = this.$refs.phoneInput.value;
                        let digits = value.replace(/\D/g, "");

                        if (digits.startsWith("0095")) {
                            digits = digits.slice(4);
                        } else if (digits.startsWith("95")) {
                            digits = digits.slice(2);
                        } else if (digits.startsWith("0")) {
                            digits = digits.slice(1);
                        }

                        digits = digits.slice(0, 10);

                        if (digits) {
                            const groups = [digits.slice(0, 1), digits.slice(1, 4), digits.slice(4, 7), digits.slice(7, 10)]
                                .filter(Boolean);
                            this.$refs.phoneInput.value = groups.join(" ");
                        } else {
                            this.$refs.phoneInput.value = "";
                        }
                    }
                },
                formatPhoneInput(event) {
                    let value = event.target.value;
                    let digits = value.replace(/\D/g, "");

                    if (digits.startsWith("0095")) {
                        digits = digits.slice(4);
                    } else if (digits.startsWith("95")) {
                        digits = digits.slice(2);
                    } else if (digits.startsWith("0")) {
                        digits = digits.slice(1);
                    }

                    digits = digits.slice(0, 10);

                    if (!digits) {
                        event.target.value = "";
                        return;
                    }

                    const groups = [digits.slice(0, 1), digits.slice(1, 4), digits.slice(4, 7), digits.slice(7, 10)]
                        .filter(Boolean);

                    event.target.value = groups.join(" ");
                },
                cropOpen: false,
                cropImageSrc: '',
                cropImageType: '',
                cropZoom: 1,
                cropBaseScale: 1,
                cropX: 0,
                cropY: 0,
                cropImageWidth: 0,
                cropImageHeight: 0,
                dragging: false,
                dragStartX: 0,
                dragStartY: 0,
                dragBaseX: 0,
                dragBaseY: 0,

                choosePhoto() {
                    this.$refs.photoInput.click();
                },

                handlePhoto(event) {
                    const file = event.target.files[0];

                    if (! file) {
                        return;
                    }
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    const maxPhotoSize = 1 * 1024 * 1024; // 1MB

                    if (! allowedTypes.includes(file.type)) {
                        this.showMessage('Please choose a JPG, GIF, or PNG image.', 'error');
                        event.target.value = '';
                        return;
                    }

                    if (file.size > maxPhotoSize) {
                        this.showMessage('Please choose an image smaller than 1MB.', 'error');
                        event.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.addEventListener('load', (readerEvent) => {
                        if (file.type === 'image/gif') {
                            this.croppedAvatar = readerEvent.target.result;
                            this.avatarPreview = readerEvent.target.result;
                            this.showMessage('GIF selected. Save changes to keep it.', 'success');
                            event.target.value = '';
                            return;
                        }

                        this.openCrop(readerEvent.target.result, file.type);
                    });
                    reader.readAsDataURL(file);
                },

                openCrop(source, type) {
                    this.cropImageSrc = source;
                    this.cropImageType = type;
                    this.cropZoom = 1;
                    this.cropX = 0;
                    this.cropY = 0;
                    this.cropOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeCrop() {
                    this.cropOpen = false;
                    this.cropImageSrc = '';
                    this.cropImageType = '';
                    this.dragging = false;
                    document.body.style.overflow = '';

                    if (this.$refs.photoInput) {
                        this.$refs.photoInput.value = '';
                    }
                },

                prepareCropImage() {
                    const image = this.$refs.cropImage;
                    const stage = image.closest('.crop-stage');

                    if (! image.naturalWidth || ! stage) {
                        return;
                    }

                    const stageSize = stage.getBoundingClientRect().width;
                    const smallestSide = Math.min(image.naturalWidth, image.naturalHeight);

                    this.cropBaseScale = stageSize / smallestSide;
                    this.constrainCropPosition();
                    this.syncCropImage();
                },

                syncCropImage() {
                    const image = this.$refs.cropImage;

                    if (! image || ! image.naturalWidth) {
                        return;
                    }

                    this.constrainCropPosition();
                    this.cropImageWidth = image.naturalWidth * this.cropBaseScale * this.cropZoom;
                    this.cropImageHeight = image.naturalHeight * this.cropBaseScale * this.cropZoom;
                },

                constrainCropPosition() {
                    const image = this.$refs.cropImage;
                    const stage = image?.closest('.crop-stage');

                    if (! image || ! image.naturalWidth || ! stage) {
                        return;
                    }

                    const stageSize = stage.getBoundingClientRect().width;
                    const imageWidth = image.naturalWidth * this.cropBaseScale * this.cropZoom;
                    const imageHeight = image.naturalHeight * this.cropBaseScale * this.cropZoom;
                    const maxX = Math.max(0, (imageWidth - stageSize) / 2);
                    const maxY = Math.max(0, (imageHeight - stageSize) / 2);

                    this.cropX = Math.min(maxX, Math.max(-maxX, this.cropX));
                    this.cropY = Math.min(maxY, Math.max(-maxY, this.cropY));
                },

                startDrag(event) {
                    this.dragging = true;
                    this.dragStartX = event.clientX;
                    this.dragStartY = event.clientY;
                    this.dragBaseX = this.cropX;
                    this.dragBaseY = this.cropY;
                    event.currentTarget.setPointerCapture(event.pointerId);
                },

                dragCrop(event) {
                    if (! this.dragging) {
                        return;
                    }

                    this.cropX = this.dragBaseX + event.clientX - this.dragStartX;
                    this.cropY = this.dragBaseY + event.clientY - this.dragStartY;
                    this.syncCropImage();
                },

                stopDrag(event) {
                    this.dragging = false;

                    if (event.currentTarget.hasPointerCapture(event.pointerId)) {
                        event.currentTarget.releasePointerCapture(event.pointerId);
                    }
                },

                applyCrop() {
                    const image = this.$refs.cropImage;
                    const stage = image?.closest('.crop-stage');

                    if (! image || ! image.naturalWidth || ! stage) {
                        return;
                    }

                    const outputSize = 400;
                    const stageSize = stage.getBoundingClientRect().width;
                    const imageWidth = image.naturalWidth * this.cropBaseScale * this.cropZoom;
                    const imageHeight = image.naturalHeight * this.cropBaseScale * this.cropZoom;
                    const imageLeft = (stageSize - imageWidth) / 2 + this.cropX;
                    const imageTop = (stageSize - imageHeight) / 2 + this.cropY;
                    const sourceX = Math.max(0, -imageLeft / (this.cropBaseScale * this.cropZoom));
                    const sourceY = Math.max(0, -imageTop / (this.cropBaseScale * this.cropZoom));
                    const sourceSize = Math.min(
                        image.naturalWidth - sourceX,
                        image.naturalHeight - sourceY,
                        stageSize / (this.cropBaseScale * this.cropZoom)
                    );
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');

                    canvas.width = outputSize;
                    canvas.height = outputSize;
                    context.drawImage(image, sourceX, sourceY, sourceSize, sourceSize, 0, 0, outputSize, outputSize);

                    this.croppedAvatar = canvas.toDataURL('image/png');
                    this.avatarPreview = this.croppedAvatar;
                    this.closeCrop();
                    this.showMessage('Photo cropped. Save changes to keep it.', 'success');
                },

                showMessage(text, type) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1000,
                        timerProgressBar: true,
                        icon: type === 'error' ? 'error' : 'success',
                        title: text,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                },

                isSubmitting: false,

                async submitForm(formElement) {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch(formElement.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (response.ok) {
                            const currentName = formElement.querySelector('[name="name"]')?.value || '';
                            const initialName = config.name || '';
                            const photoChanged = !!this.croppedAvatar;
                            const nameChanged = currentName !== initialName;

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1000,
                                timerProgressBar: true,
                                icon: 'success',
                                title: data.message || 'Profile settings saved.',
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });

                            if (photoChanged || nameChanged) {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else {
                            let errorText = 'An error occurred.';
                            if (data.errors) {
                                errorText = Object.values(data.errors).flat().join(' ');
                            } else if (data.message) {
                                errorText = data.message;
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1000,
                                timerProgressBar: true,
                                icon: 'error',
                                title: errorText
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                            icon: 'error',
                            title: 'Could not save profile settings. Please try again.'
                        });
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                    icon: 'error',
                    title: "{{ implode(' ', $errors->all()) }}"
                });
            });
        </script>
    @endif
@endpush

