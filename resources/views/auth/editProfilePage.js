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

                    let croppedData = canvas.toDataURL('image/png');
                    if (this.cropImageType === 'image/gif') {
                        croppedData = croppedData.replace('image/png', 'image/gif');
                    } else if (this.cropImageType === 'image/jpeg' || this.cropImageType === 'image/jpg') {
                        croppedData = canvas.toDataURL('image/jpeg', 0.9);
                    }
                    this.croppedAvatar = croppedData;
                    this.avatarPreview = this.croppedAvatar;
                    this.closeCrop();
                    this.showMessage('Photo cropped. Save changes to keep it.', 'success');
                },

                useOriginal() {
                    this.croppedAvatar = this.cropImageSrc;
                    this.avatarPreview = this.cropImageSrc;
                    this.closeCrop();
                    this.showMessage('Original GIF selected. Save changes to keep it.', 'success');
                },

                showMessage(text, type) {
                    window.sitesphereSwal.toast({
                        icon: type === 'error' ? 'error' : 'success',
                        title: text
                    });
                },

                isSubmitting: false,

                async submitForm(formElement) {
                    if (this.isSubmitting) return;

                    const result = await window.sitesphereSwal.confirm({
                        title: 'Save Changes?',
                        text: 'Are you sure you want to apply these profile settings?'
                    });

                    if (result.isConfirmed) {
                        this.isSubmitting = true;
                        formElement.submit();
                    }
                }
            };
        }
    </script>

    @if (session('success'))
