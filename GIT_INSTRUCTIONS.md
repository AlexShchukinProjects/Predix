# Инструкция по работе с Git для eFlight

## Текущее состояние
- **Репозиторий:** https://github.com/AlexShchukinProjects/FlightManager.git
- **Ветка:** master
- **Последний коммит:** 988be33 29072025-2
- **Статус:** Синхронизирован с удаленным репозиторием

## Основные команды Git

### Проверка статуса
```bash
git status                    # Статус репозитория
git log --oneline | head -5   # Последние 5 коммитов
git branch                    # Список веток
```

### Скачивание обновлений
```bash
git fetch Origine             # Получить обновления с удаленного репозитория
git pull Origine master       # Скачать и применить обновления
```

### Создание резервной копии
```bash
git branch backup-local-changes    # Создать резервную ветку
git stash push -m "Описание"       # Сохранить изменения во временное хранилище
```

### Восстановление из резервной копии
```bash
git checkout backup-local-changes  # Переключиться на резервную ветку
git stash pop                      # Восстановить изменения из stash
```

### Сброс к последней версии удаленного репозитория
```bash
git fetch Origine
git reset --hard Origine/master
```

## Рабочий процесс

### 1. Перед началом работы
```bash
git fetch Origine             # Получить последние обновления
git status                    # Проверить статус
```

### 2. Создание новой ветки для работы
```bash
git checkout -b feature/название-функции
```

### 3. Сохранение изменений
```bash
git add .                     # Добавить все изменения
git commit -m "Описание изменений"
```

### 4. Отправка изменений
```bash
git push Origine feature/название-функции
```

### 5. Слияние с основной веткой
```bash
git checkout master
git merge feature/название-функции
git push Origine master
```

## Полезные команды

### Просмотр истории
```bash
git log --oneline            # Краткая история
git log --graph --oneline    # История с графиком веток
git show <commit-hash>       # Детали коммита
```

### Отмена изменений
```bash
git checkout -- <file>       # Отменить изменения в файле
git reset --hard HEAD        # Отменить все изменения
git revert <commit-hash>     # Создать коммит, отменяющий изменения
```

### Работа с ветками
```bash
git branch -a                # Все ветки (локальные и удаленные)
git checkout <branch-name>   # Переключиться на ветку
git branch -d <branch-name>  # Удалить ветку
```

## Настройка после скачивания кода

После скачивания нового кода из репозитория:

1. **Установить PHP зависимости:**
```bash
php composer.phar install
```

2. **Установить npm зависимости:**
```bash
npm install
```

3. **Запустить серверы:**
```bash
# Laravel сервер (в одном терминале)
php artisan serve

# Vite сервер (в другом терминале)
npm run dev
```

## Проверка работы
- **Laravel:** http://localhost:8000
- **Vite:** http://localhost:5173

## Резервные ветки
- `backup-local-changes` - резервная копия локальных изменений 