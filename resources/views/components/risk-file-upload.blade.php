@props(['riskId', 'documentType', 'title' => 'Документы', 'existingFiles' => [], 'recordId' => null])

<div class="upload-block" data-upload-group="{{ $documentType }}" data-risk-id="{{ $riskId }}" data-record-id="{{ $recordId }}">
    <div class="upload-title">{{ $title }}</div>
    <div class="upload-toolbar">
        <label class="upload-btn">
            <span class="fas fa-camera"></span>
            <span>Выбрать файл</span>
            <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
        </label>
    </div>
    <div class="upload-grid">
        @foreach($existingFiles as $file)
        @php
            $fileName = $file['name'] ?? $file['filename'] ?? '';
            $fileType = $file['type'] ?? $file['mime_type'] ?? 'file';
            
            // Если тип файла не определен, определяем по расширению
            if ($fileType === 'file' && $fileName) {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'])) {
                    $fileType = 'image/' . $extension;
                }
            }
            
            $isImage = str_starts_with($fileType, 'image/');
        @endphp
        <div class="upload-item" 
             data-file-id="{{ $file['id'] ?? '' }}" 
             data-file-url="{{ $file['url'] }}" 
             data-file-name="{{ $fileName }}" 
             data-file-type="{{ $fileType }}"
             title="Кликните для просмотра/скачивания">
            @if($isImage)
                @php
                    $imageUrl = $file['url'];
                    // Корректируем URL для превью
                    if (str_contains($imageUrl, 'localhost/storage/')) {
                        $imageUrl = str_replace('http://localhost/storage/', '/storage/', $imageUrl);
                    } elseif (!str_starts_with($imageUrl, '/storage/')) {
                        $imageUrl = '/storage/' . ltrim($imageUrl, '/');
                    }
                @endphp
                <img class="upload-thumb" src="{{ $imageUrl }}" alt="{{ $fileName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="upload-icon" style="display: none;">🖼️</div>
            @else
                <div class="upload-icon">📄</div>
            @endif
            <div class="upload-meta">{{ $fileName }}</div>
            <div class="upload-remove">&times;</div>
        </div>
        @endforeach
    </div>
</div>

<style>
.upload-block {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    background: #f9fafb;
    transition: all 0.3s ease;
}

.upload-block:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.upload-title {
    font-weight: 600;
    font-size: 16px;
    color: #374151;
    margin-bottom: 15px;
    text-align: center;
}

.upload-toolbar {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #3b82f6;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
    font-weight: 500;
}

.text-center {
    width: 100%;
}
.upload-btn:hover {
    background: #2563eb;
}

.upload-btn input[type="file"] {
    display: none;
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    min-height: 60px;
}

.upload-item {
    position: relative;
    width: 120px;
    height: 120px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}

.upload-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.upload-thumb {
    width: 100%;
    height: 80px;
    object-fit: cover;
    cursor: pointer;
}

.upload-icon {
    font-size: 32px;
    margin-bottom: 8px;
    cursor: pointer;
}

.upload-thumb + .upload-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: #6b7280;
}

.upload-meta {
    font-size: 11px;
    color: #6b7280;
    text-align: center;
    padding: 0 4px;
    word-break: break-all;
    line-height: 1.2;
    max-height: 24px;
    overflow: hidden;
}

.upload-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
}

.upload-item:hover .upload-remove {
    opacity: 1;
}

.upload-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    padding: 8px;
    border-radius: 4px;
}

.upload-loading .spinner-border {
    width: 20px;
    height: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadBlocks = document.querySelectorAll('.upload-block');
    
    uploadBlocks.forEach(block => {
        const fileInput = block.querySelector('input[type="file"]');
        const grid = block.querySelector('.upload-grid');
        const riskId = block.dataset.riskId;
        const documentType = block.dataset.uploadGroup;
        const recordId = block.dataset.recordId;
        
        // Обработчик выбора файлов
        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                uploadFile(file, riskId, documentType, grid, recordId);
            });
            
            // Очищаем input
            e.target.value = '';
        });
        
        // Обработчик кликов на файлы
        grid.addEventListener('click', function(e) {
            e.preventDefault(); // Предотвращаем стандартное поведение
            
            if (e.target.classList.contains('upload-remove')) {
                const item = e.target.closest('.upload-item');
                const fileId = item.dataset.fileId;
                const filePath = item.dataset.fileUrl;
                const fileName = item.dataset.fileName;
                
                console.log('Delete button clicked:', {
                    fileId: fileId,
                    filePath: filePath,
                    fileName: fileName
                });
                
                // Показываем модальное окно подтверждения
                showDeleteConfirmation(item, fileId, filePath, fileName);
            } else if (e.target.closest('.upload-item') && !e.target.classList.contains('upload-remove')) {
                // Клик по файлу для просмотра/скачивания
                const item = e.target.closest('.upload-item');
                const fileUrl = item.dataset.fileUrl;
                const fileName = item.dataset.fileName;
                const fileType = item.dataset.fileType;
                
                if (fileUrl) {
                    handleFileClick(fileUrl, fileName, fileType);
                }
            }
        });
    });
    
    // Функция загрузки файла
    async function uploadFile(file, riskId, documentType, grid, recordId = null) {
        console.log('Uploading file:', {
            fileName: file.name,
            fileSize: file.size,
            riskId: riskId,
            documentType: documentType,
            recordId: recordId
        });

        const formData = new FormData();
        formData.append('file', file);
        formData.append('document_type', documentType);
        if (recordId) {
            formData.append('record_id', recordId);
        }
        
        // Создаем элемент загрузки
        const item = createUploadItem(file);
        grid.appendChild(item);
        
        try {
            const response = await fetch(`/modules/risk-management/risk/${riskId}/documents/upload`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error response:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const result = await response.json();
            console.log('Server response:', result);
            
            if (result.success) {
                // Обновляем элемент с данными с сервера
                item.dataset.fileId = result.file_id || result.file?.id || '';
                item.dataset.fileUrl = result.file?.url || result.file_url || '';
                item.dataset.fileName = result.file?.name || result.file_name || '';
                item.dataset.fileType = result.file?.type || result.file_type || '';
                item.title = 'Кликните для просмотра/скачивания';
                
                console.log('Updated item dataset:', {
                    fileId: item.dataset.fileId,
                    fileUrl: item.dataset.fileUrl,
                    fileName: item.dataset.fileName,
                    fileType: item.dataset.fileType
                });
                
                // Убираем индикатор загрузки
                const loading = item.querySelector('.upload-loading');
                if (loading) loading.remove();
                
                console.log('File uploaded successfully:', result);
            } else {
                throw new Error(result.message || 'Ошибка загрузки файла');
            }
        } catch (error) {
            console.error('Upload error:', error);
            item.remove();
            alert('Ошибка загрузки файла: ' + error.message);
        }
    }
    
    
    // Функция создания элемента загрузки
    function createUploadItem(file) {
        const item = document.createElement('div');
        item.className = 'upload-item';
        item.dataset.fileUrl = URL.createObjectURL(file);
        item.dataset.fileName = file.name;
        item.dataset.fileType = file.type;
        item.title = 'Кликните для просмотра/скачивания';
        
        // Индикатор загрузки
        const loading = document.createElement('div');
        loading.className = 'upload-loading';
        loading.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
        
        // Превью файла
        let preview;
        if (file.type.startsWith('image/')) {
            preview = document.createElement('img');
            preview.className = 'upload-thumb';
            preview.src = URL.createObjectURL(file);
        } else {
            preview = document.createElement('div');
            preview.className = 'upload-icon';
            preview.textContent = '📄';
        }
        
        // Метаданные
        const meta = document.createElement('div');
        meta.className = 'upload-meta';
        meta.textContent = file.name;
        
        // Кнопка удаления
        const remove = document.createElement('div');
        remove.className = 'upload-remove';
        remove.innerHTML = '&times;';
        
        item.appendChild(loading);
        item.appendChild(preview);
        item.appendChild(meta);
        item.appendChild(remove);
        
        return item;
    }
    
    // Функция обработки клика по файлу
    function handleFileClick(fileUrl, fileName, fileType) {
        // Определяем тип файла по расширению, если тип не указан
        const actualFileType = determineFileType(fileName, fileType);
        
        if (actualFileType && actualFileType.startsWith('image/')) {
            // Показываем изображение в модальном окне
            const correctedUrl = correctImageUrl(fileUrl);
            showImageModal(correctedUrl, fileName);
        } else {
            // Скачиваем файл
            downloadFile(fileUrl, fileName);
        }
    }
    
    // Функция определения типа файла
    function determineFileType(fileName, fileType) {
        // Если тип уже определен и это изображение, возвращаем его
        if (fileType && fileType.startsWith('image/')) {
            return fileType;
        }
        
        // Определяем по расширению файла
        if (fileName) {
            const extension = fileName.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
            if (imageExtensions.includes(extension)) {
                return 'image/' + extension;
            }
        }
        
        return fileType || 'file';
    }
    
    // Функция корректировки URL изображения
    function correctImageUrl(url) {
        // Если URL содержит localhost, заменяем на правильный путь
        if (url.includes('localhost/storage/')) {
            return url.replace('http://localhost/storage/', '/storage/');
        }
        // Если URL начинается с /storage/, оставляем как есть
        if (url.startsWith('/storage/')) {
            return url;
        }
        // Если URL не содержит /storage/, добавляем его
        if (!url.includes('/storage/')) {
            return '/storage/' + url;
        }
        return url;
    }
    
    // Функция показа изображения в модальном окне
    function showImageModal(imageUrl, fileName) {
        // Создаем модальное окно если его нет
        let modal = document.getElementById('imageModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'imageModal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${fileName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${imageUrl}" class="img-fluid" alt="${fileName}" style="max-height: 70vh;">
                        </div>
                        <div class="modal-footer">
                            <a href="${imageUrl}" download="${fileName}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i>Скачать
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            // Обновляем содержимое существующего модального окна
            modal.querySelector('.modal-title').textContent = fileName;
            modal.querySelector('.modal-body img').src = imageUrl;
            modal.querySelector('.modal-body img').alt = fileName;
            modal.querySelector('.modal-footer a').href = imageUrl;
            modal.querySelector('.modal-footer a').download = fileName;
        }
        
        // Показываем модальное окно
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    // Функция скачивания файла
    function downloadFile(fileUrl, fileName) {
        const correctedUrl = correctImageUrl(fileUrl);
        const link = document.createElement('a');
        link.href = correctedUrl;
        link.download = fileName;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Функция показа модального окна подтверждения удаления
    function showDeleteConfirmation(item, fileId, filePath, fileName) {
        console.log('showDeleteConfirmation called:', {
            item: item,
            fileId: fileId,
            filePath: filePath,
            fileName: fileName
        });
        // Создаем модальное окно если его нет
        let modal = document.getElementById('deleteConfirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'deleteConfirmModal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Подтверждение удаления</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Вы точно хотите удалить вложение?</p>
                            <p class="text-muted small">Файл: <strong id="deleteFileName"></strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn efds-btn efds-btn--danger" id="confirmDeleteBtn">Удалить</button>
                            <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // Обновляем информацию о файле
        modal.querySelector('#deleteFileName').textContent = fileName || 'Неизвестный файл';
        
        // Показываем модальное окно
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Обработчик подтверждения удаления
        const confirmBtn = modal.querySelector('#confirmDeleteBtn');
        confirmBtn.onclick = function() {
            console.log('Delete confirmed:', {
                fileId: fileId,
                filePath: filePath,
                hasFileId: fileId && fileId !== ''
            });
            
            bsModal.hide();
            if (fileId && fileId !== '') {
                // Удаляем существующий файл
                console.log('Calling deleteExistingFile');
                deleteExistingFile(fileId, filePath, item);
            } else if (filePath && filePath.includes('/storage/')) {
                // Файл загружен на сервер, но fileId не установлен
                console.log('File is on server but no fileId, trying to delete by path');
                deleteExistingFile('', filePath, item);
            } else {
                // Удаляем новый файл (еще не загруженный)
                console.log('Removing new file (not uploaded yet)');
                item.remove();
            }
        };
    }
    
    // Улучшенная функция удаления существующего файла
    async function deleteExistingFile(fileId, filePath, item) {
        try {
            const riskId = document.querySelector('[data-risk-id]').dataset.riskId;
            
            console.log('Deleting file:', {
                fileId: fileId,
                filePath: filePath,
                riskId: riskId
            });
            
            const response = await fetch(`/modules/risk-management/risk/${riskId}/documents/delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    file_id: fileId || '',
                    file_path: filePath
                })
            });
            
            console.log('Delete response status:', response.status);
            
            const result = await response.json();
            console.log('Delete response result:', result);
            
            if (result.success) {
                // Успешно удалено
                item.remove();
                console.log('Файл успешно удален:', result);
            } else {
                // Ошибка удаления
                alert('Ошибка удаления файла: ' + (result.message || 'Неизвестная ошибка'));
                console.error('Ошибка удаления файла:', result);
            }
        } catch (error) {
            console.error('Ошибка при удалении файла:', error);
            alert('Ошибка удаления файла: ' + error.message);
        }
    }
});
</script>
