<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelController extends Controller
{
    /**
     * Создать Excel файл
     */
    public function createExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовки
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Название услуги');
        $sheet->setCellValue('C1', 'Группа');
        $sheet->setCellValue('D1', 'Шаблон');
        
        // Стили для заголовков
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Данные (пример)
        $data = [
            [1, 'Услуга 1', 'Группа A', 'Шаблон сообщения 1'],
            [2, 'Услуга 2', 'Группа B', 'Шаблон сообщения 2'],
            [3, 'Услуга 3', 'Группа A', 'Шаблон сообщения 3'],
        ];
        
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item[0]);
            $sheet->setCellValue('B' . $row, $item[1]);
            $sheet->setCellValue('C' . $row, $item[2]);
            $sheet->setCellValue('D' . $row, $item[3]);
            $row++;
        }
        
        // Автоматическая ширина колонок
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Границы
        $sheet->getStyle('A1:D' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        
        // Создаем файл
        $writer = new Xlsx($spreadsheet);
        $filename = 'messages_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        
        $writer->save($filepath);
        
        return response()->download($filepath)->deleteFileAfterSend();
    }
    
    /**
     * Читать Excel файл
     */
    public function readExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        
        $data = [];
        $highestRow = $sheet->getHighestRow();
        
        // Читаем данные начиная со второй строки (пропускаем заголовки)
        for ($row = 2; $row <= $highestRow; $row++) {
            $data[] = [
                'id' => $sheet->getCell('A' . $row)->getValue(),
                'service' => $sheet->getCell('B' . $row)->getValue(),
                'group' => $sheet->getCell('C' . $row)->getValue(),
                'template' => $sheet->getCell('D' . $row)->getValue(),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'total_rows' => count($data),
        ]);
    }
    
    /**
     * Экспорт сообщений в Excel
     */
    public function exportMessages()
    {
        $messages = \App\Models\TemplateTlgXlsx::orderBy('Service')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовки
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Название услуги');
        $sheet->setCellValue('C1', 'Группа');
        $sheet->setCellValue('D1', 'Шаблон');
        
        // Стили для заголовков
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Данные из базы
        $row = 2;
        foreach ($messages as $message) {
            $sheet->setCellValue('A' . $row, $message->id);
            $sheet->setCellValue('B' . $row, $message->Service);
            $sheet->setCellValue('C' . $row, $message->Group);
            $sheet->setCellValue('D' . $row, $message->Template);
            $row++;
        }
        
        // Автоматическая ширина колонок
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Границы
        $sheet->getStyle('A1:D' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        
        // Создаем файл
        $writer = new Xlsx($spreadsheet);
        $filename = 'messages_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        
        $writer->save($filepath);
        
        return response()->download($filepath)->deleteFileAfterSend();
    }
} 