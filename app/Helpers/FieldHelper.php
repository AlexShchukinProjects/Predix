<?php

namespace App\Helpers;

class FieldHelper
{
    public static function getFieldTypeName($type)
    {
        $typeNames = [
            'text' => 'Текст',
            'textarea' => 'Большое текстовое поле',
            'number' => 'Число',
            'date' => 'Дата',
            'time' => 'Время',
            'datetime' => 'Дата и время',
            'email' => 'Email',
            'phone' => 'Телефон',
            'radio' => 'Радио-кнопка',
            'checkbox' => 'Чекбокс',
            'toggle' => 'Переключатель',
            'lookup' => 'Справочник',
            'file' => 'Файл',
            'dragdrop' => 'Перетаскивание файлов'
        ];
        
        return $typeNames[$type] ?? $type;
    }
}
