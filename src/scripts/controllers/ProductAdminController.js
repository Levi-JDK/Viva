import { AdminService } from '../services/AdminService.js';

export class ProductAdminController {
    init() {
        this.isTransitioning = false;
        
        // Expose globals for inline calls
        window.showSection = this.showSection.bind(this);
        window.toggleSidebarSeller = this.toggleSidebarSeller.bind(this);
        window.editarProducto = this.editarProducto.bind(this);
        window.eliminarProducto = this.eliminarProducto.bind(this);
        window.previewImage = this.previewImage.bind(this);
        window.previewBackground = this.previewBackground.bind(this);

        this.selectedImages = [];
        this.MAX_IMAGES = 4;
        
        // Expose functions required by inline onclick in renderPreviews
        window.removeImage = (index) => {
            this.selectedImages.splice(index, 1);
            const uploadForm = document.getElementById('product-upload-form');
            if (uploadForm) this.renderPreviews(uploadForm);
        };

        window.removeExistingImage = (index) => {
            if (window.existingImages) {
                window.existingImages.splice(index, 1);
            }
            const uploadForm = document.getElementById('product-upload-form');
            if (uploadForm) this.renderPreviews(uploadForm);
        };

        const uploadForm = document.getElementById('product-upload-form');
        this.gridContainer = document.getElementById('image-preview-grid');
        
        if (uploadForm && uploadForm.getAttribute('data-mode') === 'edit' && window.existingImages) {
            this.renderPreviews(uploadForm);
        }
    }

    showSection(sectionId) {
        if (this.isTransitioning) return;

        const currentSection = document.querySelector('.content-section.active');
        const targetSection = document.getElementById(sectionId);

        if (currentSection && currentSection.id === sectionId) return;

        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active-item');
            if (item.getAttribute('onclick')?.includes(`'${sectionId}'`)) {
                item.classList.add('active-item');
                const menuText = item.querySelector('span').innerText;
                const pageTitle = document.getElementById('pageTitle');
                if (pageTitle) pageTitle.innerText = menuText;
            }
        });

        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (window.innerWidth < 1024 && sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }

        if (currentSection) {
            this.isTransitioning = true;
            currentSection.classList.add('closing');
            currentSection.classList.remove('active');

            setTimeout(() => {
                currentSection.classList.remove('closing');
                currentSection.style.display = 'none';

                if (targetSection) {
                    targetSection.style.display = 'block';
                    requestAnimationFrame(() => {
                        targetSection.classList.add('active');
                        this.isTransitioning = false;
                    });
                } else {
                    this.isTransitioning = false;
                }
            }, 300);
        } else {
            if (targetSection) {
                targetSection.style.display = 'block';
                requestAnimationFrame(() => {
                    targetSection.classList.add('active');
                });
            }
        }
    }

    toggleSidebarSeller() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        if (!sidebar || !sidebarOverlay) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            sidebarOverlay.classList.remove('hidden');
        } else {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }
    }

    handleNumericKeydown(e) {
        if (['-', '+', 'e', 'E'].includes(e.key)) {
            e.preventDefault();
        }
    }

    handleNumericInput(e, input) {
        if (input.value !== '' && parseFloat(input.value) < 0) {
            input.value = Math.abs(parseFloat(input.value));
        }
    }

    handleMiscKeypress(e) {
        if (e.key === '-' || e.key === '.' || e.key === ',') {
            e.preventDefault();
        }
    }

    handleImageSelection(e, inputElement) {
        const uploadForm = document.getElementById('product-upload-form');
        this.gridContainer = document.getElementById('image-preview-grid');

        const files = Array.from(e.target.files);

        if (this.selectedImages.length + files.length > this.MAX_IMAGES) {
            if (typeof showToast !== 'undefined') showToast(`Máximo ${this.MAX_IMAGES} imágenes permitidas.`, 'error');
            e.target.value = ''; 
            return;
        }

        files.forEach(file => {
            const allowedExtensions = ['jpg', 'jpeg', 'webp'];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(fileExtension)) {
                if (typeof showToast !== 'undefined') showToast(`Formato no permitido: ${file.name}`, 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                if (typeof showToast !== 'undefined') showToast(`Archivo muy pesado: ${file.name}`, 'error');
                return;
            }

            this.selectedImages.push(file);
        });

        e.target.value = '';
        if (uploadForm) this.renderPreviews(uploadForm);
    }

    async submitProduct(e, uploadForm) {
        e.preventDefault();

        const isEditMode = uploadForm.getAttribute('data-mode') === 'edit';

        if (!isEditMode && this.selectedImages.length === 0) {
            if (typeof showToast !== 'undefined') showToast('Debes agregar al menos una imagen principal.', 'error');
            return;
        }

        if (isEditMode && this.selectedImages.length === 0 && (!window.existingImages || window.existingImages.length === 0)) {
            if (typeof showToast !== 'undefined') showToast('Debes mantener o subir al menos una imagen principal.', 'error');
            return;
        }

        const formData = new FormData(uploadForm);
        formData.delete('imagen_producto[]');

        this.selectedImages.forEach((file) => {
            formData.append('imagen_producto[]', file);
        });

        if (isEditMode && window.existingImages) {
            formData.append('imagenes_existentes', JSON.stringify(window.existingImages));
        }

        if (typeof showToast !== 'undefined') showToast(isEditMode ? 'Guardando cambios...' : 'Publicando producto...', 'info');

        try {
            const data = await AdminService.saveProduct(formData, isEditMode);
            if (data.success) {
                if (typeof showToast !== 'undefined') showToast(isEditMode ? 'Cambios guardados exitosamente.' : 'Producto publicado exitosamente.', 'success');
                if (!isEditMode) {
                    this.selectedImages = [];
                    this.renderPreviews(uploadForm);
                    uploadForm.reset();
                } else {
                    setTimeout(() => { window.location.href = '?view=inventory'; }, 1500);
                }
            } else {
                if (typeof showToast !== 'undefined') showToast(data.message || 'Error al procesar la solicitud.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de conexión.', 'error');
        }
    }

    async submitStand(e, standForm) {
        e.preventDefault();
        const formData = new FormData(standForm);
        if (typeof showToast !== 'undefined') showToast('Guardando cambios...', 'info');

        try {
            const data = await AdminService.updateStand(formData);
            if (data.success) {
                if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            } else {
                if (typeof showToast !== 'undefined') showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
        }
    }

    renderPreviews(uploadForm) {
        if (!this.gridContainer) return;
        this.gridContainer.innerHTML = '';

        const isEditMode = uploadForm.getAttribute('data-mode') === 'edit';
        if (isEditMode && this.selectedImages.length === 0 && window.existingImages && window.existingImages.length > 0) {
            const alertInfo = document.createElement('div');
            alertInfo.className = 'col-span-full mb-2 p-3 bg-blue-50 text-blue-700 text-sm rounded-lg flex items-center gap-2';
            alertInfo.innerHTML = '<i class="fas fa-info-circle"></i> Sube nuevas imágenes para reemplazar todas las actuales.';
            this.gridContainer.parentNode.insertBefore(alertInfo, this.gridContainer);

            window.existingImages.forEach((imgObj, index) => {
                const slot = document.createElement('div');
                slot.className = 'bg-gray-100 rounded-lg aspect-square flex items-center justify-center relative overflow-hidden group border border-gray-200';
                slot.innerHTML = `
                    <img src="${(typeof BASE_URL !== 'undefined' ? BASE_URL : '') + imgObj.url}" class="w-full h-full object-cover">
                    <button type="button" class="absolute top-1 right-1 bg-white text-red-500 rounded-full p-1 shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50 z-10" onclick="removeExistingImage(${index})">
                        <i class="fas fa-times text-xs w-4 h-4 flex items-center justify-center"></i>
                    </button>
                    ${index === 0 ? '<span class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[10px] text-center py-1">Principal (Actual)</span>' : ''}
                `;
                this.gridContainer.appendChild(slot);
            });
        }

        this.selectedImages.forEach((file, index) => {
            const reader = new FileReader();
            const slot = document.createElement('div');
            slot.className = 'bg-gray-100 rounded-lg aspect-square flex items-center justify-center relative overflow-hidden group border border-gray-200';
            slot.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-400"></i>';
            this.gridContainer.appendChild(slot);

            reader.onload = function (e) {
                slot.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" class="absolute top-1 right-1 bg-white text-red-500 rounded-full p-1 shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50 z-10" onclick="removeImage(${index})">
                        <i class="fas fa-times text-xs w-4 h-4 flex items-center justify-center"></i>
                    </button>
                    ${index === 0 ? '<span class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[10px] text-center py-1">Principal</span>' : ''}
                `;
            }
            reader.readAsDataURL(file);
        });

        const totalImages = (window.existingImages ? window.existingImages.length : 0) + this.selectedImages.length;
        if (totalImages < this.MAX_IMAGES) {
            const addBtn = document.createElement('div');
            addBtn.onclick = () => document.getElementById('product-images-input').click();
            addBtn.className = 'border-2 border-dashed border-naranja-artesanal/30 rounded-lg aspect-square flex flex-col items-center justify-center text-center hover:bg-orange-50 transition-colors cursor-pointer bg-orange-50/30 relative overflow-hidden group';
            addBtn.innerHTML = `
                <i class="fas fa-plus text-2xl text-naranja-artesanal mb-2 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs text-naranja-artesanal font-medium">Agregar</span>
            `;
            this.gridContainer.appendChild(addBtn);
        }

        const itemsVisibles = totalImages + (totalImages < this.MAX_IMAGES ? 1 : 0);
        const slotsRestantes = this.MAX_IMAGES - itemsVisibles;

        for (let i = 0; i < slotsRestantes; i++) {
            const placeholder = document.createElement('div');
            placeholder.className = 'border-2 border-dashed border-gray-200 rounded-lg aspect-square flex items-center justify-center bg-gray-50 opacity-50';
            placeholder.innerHTML = '<i class="fas fa-image text-gray-300"></i>';
            this.gridContainer.appendChild(placeholder);
        }
    }

    editarProducto(id_producto) {
        window.location.href = `?view=add_product&id=${id_producto}`;
    }

    eliminarProducto(id_producto) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡El producto será eliminado de tu catálogo!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    container: 'z-[9999]',
                    confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded mr-2',
                    cancelButton: 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded'
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const data = await AdminService.deleteProduct(id_producto);
                        if (data.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: 'Tu producto ha sido eliminado.',
                                icon: 'success',
                                buttonsStyling: false,
                                customClass: {
                                    container: 'z-[9999]',
                                    confirmButton: 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded'
                                }
                            }).then(() => { window.location.reload(); });
                        } else {
                            if (typeof showToast !== 'undefined') showToast(data.message || 'Error al eliminar.', 'error');
                        }
                    } catch (err) {
                        console.error('Error:', err);
                        if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
                    }
                }
            });
        }
    }



    previewImage(input, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById(imgId);
                if (img) img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    previewBackground(input, elementId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const element = document.getElementById(elementId);
                if (element) {
                    element.style.backgroundImage = `url('${e.target.result}')`;
                    element.style.backgroundSize = 'cover';
                    element.style.backgroundPosition = 'center';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    preventEmptyStand(e) {
        if (e) e.preventDefault();
        if (typeof showToast !== 'undefined') showToast('Guarda tu stand primero', 'info');
    }

    triggerPortadaUpload() {
        document.getElementById('portada-upload')?.click();
    }

    triggerLogoUpload() {
        document.getElementById('logo-upload')?.click();
    }

    triggerImageUpload() {
        document.getElementById('product-images-input')?.click();
    }
}
export const productAdminController = new ProductAdminController();