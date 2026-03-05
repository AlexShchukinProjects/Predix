# Двухшаговый UI загрузки Work Cards: сначала показ числа строк, затем загрузка с прогрессом

## 1. Backend (InspectionDataController.php)

### 1.1 Добавить use (после IReadFilter):
```php
use Symfony\Component\HttpFoundation\StreamedResponse;
```

### 1.2 В методе workCardsUpload после validate добавить:
```php
$file = $request->file('file');
if ($request->header('X-Action') === 'count') {
    $path = $file->getRealPath();
    $ext = strtolower($file->getClientOriginalExtension());
    return response()->json(['total' => $this->getWorkCardsImportTotal($path, $ext)]);
}
if ($request->header('X-Response-Stream') === 'progress') {
    return $this->workCardsUploadStream($file);
}
```
И заменить вызов на: `$count = $this->importWorkCardsChunked($file, null, null);`

### 1.3 Добавить метод getWorkCardsImportTotal (перед importWorkCardsChunked):
```php
private function getWorkCardsImportTotal(string $path, string $ext): int
{
    if ($ext === 'csv' || $ext === 'txt') {
        $fh = fopen($path, 'r');
        if ($fh === false) return 0;
        $lines = 0;
        $first = fgetcsv($fh, 0, ',');
        if ($first !== false && isset($first[0]) && str_starts_with(trim((string) $first[0]), 'sep=')) {
            $first = fgetcsv($fh, 0, ',');
        }
        while (fgetcsv($fh, 0, ',') !== false) {
            $lines++;
            if ($lines > 1000000) break;
        }
        fclose($fh);
        return $lines;
    }
    $reader = IOFactory::createReaderForFile($path);
    if (!method_exists($reader, 'listWorksheetInfo')) return 0;
    $info = $reader->listWorksheetInfo($path);
    $totalRows = (int) ($info[0]['totalRows'] ?? 0);
    return $totalRows > 0 ? $totalRows - 1 : 0;
}
```

### 1.4 Добавить метод workCardsUploadStream (после getWorkCardsImportTotal):
Возвращает StreamedResponse: отправляет {"total": N}, затем {"processed": M, "total": N}, в конце {"done": true, "count": N} или {"error": "..."}. Реализация как в предыдущем ответе (workCardsUploadStream с $send и importWorkCardsChunked с callback).

### 1.5 Сигнатуру importWorkCardsChunked изменить на:
```php
private function importWorkCardsChunked($file, ?callable $progressCallback = null, ?int $total = null): int
```
В замыкание $flush добавить в use: $progressCallback, $total. В конце $flush после $buffer = []; вызвать:
```php
if ($progressCallback !== null && $total !== null) {
    $progressCallback($count, $total);
}
```

## 2. View (work_cards.blade.php)

### 2.1 В modal-body после блока с work-cards-upload-filename (после </div> дропзоны) вставить:
```html
<div id="work-cards-count-block" class="mt-3 d-none">
    <p class="mb-1 small text-muted">Будет загружено строк: <strong id="work-cards-total-rows">—</strong></p>
    <p class="mb-0 small text-muted">Нажмите «Загрузить» для начала импорта.</p>
</div>
<div id="work-cards-progress-block" class="mt-3 d-none">
    <div class="progress mb-2" style="height: 1.25rem;">
        <div id="work-cards-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
    </div>
    <p class="mb-0 small">Загружено: <span id="work-cards-processed">0</span> из <span id="work-cards-total">—</span></p>
    <p id="work-cards-progress-error" class="mt-2 mb-0 small text-danger d-none"></p>
</div>
```

### 2.2 Кнопке «Отмена» добавить id="work-cards-upload-cancel".

### 2.3 В resetUploadModal добавить скрытие и сброс: work-cards-count-block, work-cards-progress-block, total-rows, total, processed, progress-bar, progress-error.

### 2.4 При выборе файла (в setFile или после fileInput.addEventListener('change')): вызвать fetch с FormData и заголовком X-Action: count. В ответе взять data.total и показать work-cards-count-block, выставить work-cards-total-rows и work-cards-total в N.

### 2.5 При submit формы: показать work-cards-progress-block, отключить кнопки, fetch с X-Response-Stream: progress, читать поток NDJSON и обновлять progress-bar и «Загружено M из N». При done — редирект с success. При error — показать в progress-error.
