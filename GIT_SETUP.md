# Настройка Git для проекта eFlight

## ✅ Git настроен!

### Расположение файла конфигурации
**Файл:** `~/.gitconfig` (домашняя директория пользователя)
**Полный путь:** `/Users/alwxanderalexander/.gitconfig`

### Текущая конфигурация
```ini
[user]
        name = Alex
        email = aleksandr.mai@mail.ru
```

## Проблема с PATH

### ❌ Проблема
XAMPP переопределяет команды в PATH, что приводит к конфликту с Git.

### ✅ Решение
Используйте полный путь к Git или временно измените PATH:

```bash
# Способ 1: Использование полного пути
/usr/bin/git config --global --list

# Способ 2: Временное изменение PATH
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git config --global --list

# Способ 3: Создание алиаса
alias git='/usr/bin/git'
```

## Основные команды Git

### Настройка пользователя
```bash
# Установка имени пользователя
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git config --global user.name "Your Name"

# Установка email
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git config --global user.email "your.email@example.com"

# Проверка настроек
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git config --global --list
```

### Работа с репозиторием
```bash
# Инициализация репозитория
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git init

# Добавление файлов
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git add .

# Создание коммита
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git commit -m "Initial commit"

# Проверка статуса
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git status

# Просмотр истории
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git log --oneline
```

### Работа с ветками
```bash
# Создание новой ветки
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git branch feature-name

# Переключение на ветку
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git checkout feature-name

# Создание и переключение на новую ветку
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git checkout -b feature-name

# Просмотр всех веток
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git branch -a
```

### Работа с удаленным репозиторием
```bash
# Добавление удаленного репозитория
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git remote add origin https://github.com/username/repository.git

# Отправка изменений
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git push origin main

# Получение изменений
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git pull origin main

# Клонирование репозитория
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git clone https://github.com/username/repository.git
```

## Полезные алиасы

### Добавление в ~/.zshrc
```bash
# Откройте файл конфигурации
nano ~/.zshrc

# Добавьте эти строки
alias git='/usr/bin/git'
alias gst='git status'
alias gco='git checkout'
alias gbr='git branch'
alias gadd='git add'
alias gcom='git commit -m'
alias glog='git log --oneline'
alias gpull='git pull'
alias gpush='git push'

# Сохраните и перезагрузите
source ~/.zshrc
```

## Проверка работы Git

### Проверка версии
```bash
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git --version
```

### Проверка конфигурации
```bash
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git config --global --list
```

### Проверка статуса репозитория
```bash
export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git status
```

## Структура файлов Git

### Глобальная конфигурация
- **Файл:** `~/.gitconfig`
- **Содержимое:** Настройки пользователя (имя, email)

### Локальная конфигурация проекта
- **Файл:** `.git/config` (в корне проекта)
- **Содержимое:** Настройки конкретного репозитория

### Системная конфигурация
- **Файл:** `/etc/gitconfig` (обычно не существует)
- **Содержимое:** Системные настройки Git

## Рекомендации

### 1. Используйте алиасы
Создайте алиасы для упрощения работы с Git.

### 2. Настройте .gitignore
Создайте файл `.gitignore` для исключения ненужных файлов:
```
node_modules/
vendor/
.env
.DS_Store
*.log
```

### 3. Используйте понятные сообщения коммитов
```bash
git commit -m "feat: add user authentication"
git commit -m "fix: resolve Bootstrap import issue"
git commit -m "docs: update README with setup instructions"
```

### 4. Регулярно делайте коммиты
```bash
# Частые коммиты с понятными сообщениями
git add .
git commit -m "feat: implement flight management system"
```

## Следующие шаги

1. **Инициализируйте репозиторий:**
   ```bash
   export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git init
   ```

2. **Добавьте файлы:**
   ```bash
   export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git add .
   ```

3. **Создайте первый коммит:**
   ```bash
   export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git commit -m "Initial commit: eFlight project setup"
   ```

4. **Создайте репозиторий на GitHub/GitLab и свяжите с локальным:**
   ```bash
   export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git remote add origin https://github.com/username/eflight.git
   export PATH="/usr/bin:/bin:/usr/sbin:/sbin" && git push -u origin main
   ```

**Git готов к использованию! 🚀** 