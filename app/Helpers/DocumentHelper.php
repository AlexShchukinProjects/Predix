<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentHelper
{
    /**
     * Сохранить документ сотрудника
     */
    public static function storeCrewDocument(UploadedFile $file, int $crewId, string $documentType, string $requirementName = null): array
    {
        // Определяем подпапку по типу документа
        $subfolder = self::getDocumentSubfolder($documentType);
        
        // Создаем путь: Planning/crew_documents/{crew_id}/{subfolder}/
        $path = "Planning/crew_documents/{$crewId}/{$subfolder}";
        
        // Генерируем уникальное имя файла
        $filename = self::generateUniqueFilename($file, $requirementName);
        
        // Сохраняем файл
        $fullPath = $file->storeAs($path, $filename, 'public');
        
        // Генерируем уникальный ID для файла
        $fileId = uniqid('file_', true);
        
        return [
            'id' => $fileId,
            'path' => $fullPath,
            'url' => Storage::disk('public')->url($fullPath),
            'filename' => $filename,
            'name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toISOString()
        ];
    }
    
    /**
     * Загрузить файл для сотрудника (AJAX)
     */
    public static function uploadCrewDocument(UploadedFile $file, int $crewId, string $documentType): array
    {
        $documentInfo = self::storeCrewDocument($file, $crewId, $documentType);
        
        return [
            'success' => true,
            'file_id' => $documentInfo['id'],
            'file' => $documentInfo,
            'message' => 'Файл загружен успешно'
        ];
    }
    
    /**
     * Удалить документ
     */
    public static function deleteCrewDocument(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
    
    /**
     * Получить подпапку по типу документа
     */
    private static function getDocumentSubfolder(string $documentType): string
    {
        // Если передан тип папки напрямую, возвращаем его
        $directTypes = ['flight_docs', 'permits', 'checks', 'training', 'passports', 'other'];
        if (in_array($documentType, $directTypes)) {
            return $documentType;
        }
        
        // Маппинг названий документов на папки
        $typeMapping = [
            'Паспорт РФ' => 'passports',
            'Паспорт' => 'passports',
            'Удостоверение личности' => 'passports',
            'Летная книжка' => 'flight_docs',
            'Сертификат' => 'flight_docs',
            'Лицензия' => 'flight_docs',
            'Допуск' => 'permits',
            'Разрешение' => 'permits',
            'Проверка' => 'checks',
            'Медосмотр' => 'checks',
            'Подготовка' => 'training',
            'Обучение' => 'training',
            'Курс' => 'training'
        ];
        
        foreach ($typeMapping as $key => $folder) {
            if (str_contains($documentType, $key)) {
                return $folder;
            }
        }
        
        return 'other';
    }
    
    /**
     * Генерировать имя папки на основе требования и типа ВС
     */
    private static function generateFolderName(string $requirementName = null, string $aircraftType = null, int $recordId = null): string
    {
        $parts = [];
        
        // Добавляем название требования
        if ($requirementName) {
            $cleanRequirement = preg_replace('/[^a-zA-Z0-9а-яА-Я\s_-]/u', '', $requirementName);
            $cleanRequirement = trim($cleanRequirement);
            if ($cleanRequirement) {
                $parts[] = $cleanRequirement;
            }
        }
        
        // Добавляем тип ВС
        if ($aircraftType) {
            $cleanAircraftType = preg_replace('/[^a-zA-Z0-9а-яА-Я\s_-]/u', '', $aircraftType);
            $cleanAircraftType = trim($cleanAircraftType);
            if ($cleanAircraftType) {
                $parts[] = $cleanAircraftType;
            }
        }
        
        // Если нет названия требования и типа ВС, используем ID записи
        if (empty($parts)) {
            $parts[] = "record_{$recordId}";
        }
        
        return implode('_', $parts);
    }
    
    /**
     * Генерировать уникальное имя файла
     */
    private static function generateUniqueFilename(UploadedFile $file, string $requirementName = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = substr(md5(uniqid()), 0, 8);
        
        // Если есть название требования, используем его в имени файла
        if ($requirementName) {
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $requirementName);
            $cleanName = substr($cleanName, 0, 50); // Ограничиваем длину
            return "{$cleanName}_{$timestamp}_{$random}.{$extension}";
        }
        
        return "document_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Получить все документы сотрудника
     */
    public static function getCrewDocuments(int $crewId): array
    {
        $documents = [];
        $basePath = "Planning/crew_documents/{$crewId}";
        
        if (Storage::disk('public')->exists($basePath)) {
            $directories = Storage::disk('public')->directories($basePath);
            
            foreach ($directories as $directory) {
                $files = Storage::disk('public')->files($directory);
                foreach ($files as $file) {
                    $documents[] = [
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'filename' => basename($file),
                        'category' => basename($directory),
                        'size' => Storage::disk('public')->size($file),
                        'modified_at' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }
        }
        
        return $documents;
    }
    
    /**
     * Получить документы сотрудника по типу
     */
    public static function getCrewDocumentsByType(int $crewId, string $documentType): array
    {
        $documents = [];
        $subfolder = self::getDocumentSubfolder($documentType);
        $basePath = "Planning/crew_documents/{$crewId}/{$subfolder}";
        
        if (Storage::disk('public')->exists($basePath)) {
            $files = Storage::disk('public')->files($basePath);
            foreach ($files as $file) {
                $documents[] = [
                    'path' => $file,
                    'url' => Storage::disk('public')->url($file),
                    'filename' => basename($file),
                    'name' => basename($file),
                    'category' => $subfolder,
                    'size' => Storage::disk('public')->size($file),
                    'type' => Storage::disk('public')->mimeType($file),
                    'mime_type' => Storage::disk('public')->mimeType($file),
                    'modified_at' => Storage::disk('public')->lastModified($file)
                ];
            }
        }
        
        return $documents;
    }
    
    /**
     * Получить документы для конкретной записи
     */
    public static function getRecordDocuments(int $crewId, string $documentType, int $recordId, string $requirementName = null, string $aircraftType = null, $model = null): array
    {
        $documents = [];
        
        // Сначала пытаемся получить документы из поля Document модели (если модель передана)
        if ($model && $model->Document) {
            $documentData = json_decode($model->Document, true);
            if (is_array($documentData)) {
                foreach ($documentData as $file) {
                    // Преобразуем оптимизированные данные обратно в полный формат
                    // Передаем параметры для восстановления пути
                    $fullFile = self::expandOptimizedFileData($file, $crewId, $documentType, $recordId, $requirementName, $aircraftType);
                    if ($fullFile) {
                        $documents[] = $fullFile;
                    }
                }
            }
        }
        
        // Также получаем файлы из файловой системы (для обратной совместимости)
        $subfolder = self::getDocumentSubfolder($documentType);
        
        // Сначала пытаемся найти папку с новым форматом
        $folderName = self::generateFolderName($requirementName, $aircraftType, $recordId);
        $basePath = "Planning/crew_documents/{$crewId}/{$subfolder}/{$folderName}";
        
        // Если папка с новым форматом не существует, ищем старую папку record_{id}
        if (!Storage::disk('public')->exists($basePath)) {
            $basePath = "Planning/crew_documents/{$crewId}/{$subfolder}/record_{$recordId}";
        }
        
        if (Storage::disk('public')->exists($basePath)) {
            $files = Storage::disk('public')->files($basePath);
            foreach ($files as $file) {
                // Проверяем, не добавлен ли уже этот файл из поля Document
                $filePath = $file;
                $alreadyAdded = false;
                foreach ($documents as $doc) {
                    if (($doc['path'] ?? '') === $filePath || ($doc['p'] ?? '') === $filePath) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                
                if (!$alreadyAdded) {
                    $documents[] = [
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'filename' => basename($file),
                        'name' => basename($file),
                        'category' => $subfolder,
                        'size' => Storage::disk('public')->size($file),
                        'type' => Storage::disk('public')->mimeType($file),
                        'mime_type' => Storage::disk('public')->mimeType($file),
                        'modified_at' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }
        }
        
        return $documents;
    }
    
    /**
     * Преобразовать оптимизированные данные файла обратно в полный формат
     */
    private static function expandOptimizedFileData(array $file, int $crewId = null, string $documentType = null, int $recordId = null, string $requirementName = null, string $aircraftType = null): ?array
    {
        // Если данные уже в полном формате, возвращаем как есть
        if (isset($file['path']) && isset($file['url'])) {
            return $file;
        }
        
        // Преобразуем из оптимизированного формата
        $fileId = $file['i'] ?? $file['id'] ?? uniqid('file_', true);
        $fileName = $file['n'] ?? $file['name'] ?? null;
        
        // Если есть полный путь, используем его
        $path = $file['p'] ?? $file['path'] ?? null;
        
        // Если пути нет, но есть имя файла и параметры для восстановления пути
        if (!$path && $fileName && $crewId && $documentType && $recordId) {
            // Восстанавливаем путь на основе известной структуры
            $subfolder = self::getDocumentSubfolder($documentType);
            $folderName = self::generateFolderName($requirementName, $aircraftType, $recordId);
            
            // Пробуем найти файл в возможных папках
            $possiblePaths = [
                "Planning/crew_documents/{$crewId}/{$subfolder}/{$folderName}/{$fileName}",
                "Planning/crew_documents/{$crewId}/{$subfolder}/record_{$recordId}/{$fileName}"
            ];
            
            foreach ($possiblePaths as $possiblePath) {
                if (Storage::disk('public')->exists($possiblePath)) {
                    $path = $possiblePath;
                    break;
                }
            }
        }
        
        if (!$path) {
            return null;
        }
        
        return [
            'id' => $fileId,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => basename($path),
            'name' => $fileName ?? basename($path),
            'size' => $file['s'] ?? $file['size'] ?? (Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 0),
            'type' => $file['type'] ?? (Storage::disk('public')->exists($path) ? Storage::disk('public')->mimeType($path) : 'application/octet-stream'),
            'mime_type' => $file['mime_type'] ?? $file['type'] ?? (Storage::disk('public')->exists($path) ? Storage::disk('public')->mimeType($path) : 'application/octet-stream'),
            'uploaded_at' => $file['uploaded_at'] ?? now()->toISOString()
        ];
    }
    
    /**
     * Сохранить документ для конкретной записи
     */
    public static function storeRecordDocument(UploadedFile $file, int $crewId, string $documentType, int $recordId, string $requirementName = null, string $aircraftType = null): array
    {
        // Определяем подпапку по типу документа
        $subfolder = self::getDocumentSubfolder($documentType);
        
        // Создаем имя папки на основе требования и типа ВС
        $folderName = self::generateFolderName($requirementName, $aircraftType, $recordId);
        
        // Создаем путь: Planning/crew_documents/{crew_id}/{subfolder}/{folder_name}/
        $path = "Planning/crew_documents/{$crewId}/{$subfolder}/{$folderName}";
        
        // Убеждаемся, что папка существует
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path, 0755, true);
        }
        
        // Генерируем уникальное имя файла
        $filename = self::generateUniqueFilename($file);
        
        // Сохраняем файл
        $fullPath = $file->storeAs($path, $filename, 'public');
        
        // Генерируем уникальный ID для файла
        $fileId = uniqid('file_', true);
        
        return [
            'id' => $fileId,
            'path' => $fullPath,
            'url' => Storage::disk('public')->url($fullPath),
            'filename' => $filename,
            'name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toISOString()
        ];
    }
}
