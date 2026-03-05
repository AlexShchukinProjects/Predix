# Таблицы БД: какие оставить, какие можно удалить

Чтобы работали **модуль Надежность**, **Настройки** и **система авторизации** (плюс чат и уведомления), нужны только перечисленные ниже таблицы. Остальные можно удалить.

---

## Текущее состояние БД (обновлено 2026-03-06)

Миграция `2026_03_06_120000_drop_remaining_unused_tables` удалила 27 таблиц: обучение (course_modules, courses, tr_course_lesson_questions, tr_courses, tr_training_materials, trainings), экипажи/рейсы (crew_accessibilities, crew_requirements, crews_archive, flight_crews, flight_readiness_types, flightchecks, flightdocs, fleets, maintenance_aircraft, passengers), прочее (requirements2, risk_registries, spi_aircraft_types, sr_display_settings, sr_event_causes, sr_message_event_cause, permissions, country, owners, parkings), архив (aircrafts_types_archive).

Миграция `2026_03_06_130000_drop_rel_tables_and_create_stub` удалила 12 таблиц модуля Надежность; миграция `2026_03_06_140000_drop_rel_stub_table` удалила и таблицу `rel_stub`. Модели (ReliabilityFailure, RelFailureSystem, RelBufSetting и др.) работают через заглушки: **запросы к БД не выполняются** (кастомный `StubEloquentBuilder` возвращает пустые результаты), запись не сохраняется (трейт `StubRelModel`). Таблицу хранить не нужно.

**Таблицы, которые остались в БД (22 шт.):**

| # | Таблица | Группа |
|---|---------|--------|
| 1 | aircraft | Надежность / справочник |
| 2 | aircrafts_types | Надежность / справочник |
| 3 | auth_permissions | Авторизация |
| 4 | auth_roles_permissions | Авторизация |
| 5 | cache | Laravel |
| 6 | cache_locks | Laravel |
| 7 | chat_participants | Чат |
| 8 | chats | Чат |
| 9 | departments | Авторизация |
| 10 | email_logs | Уведомления |
| 11 | failed_jobs | Laravel (очереди) |
| 12 | job_batches | Laravel (очереди) |
| 13 | jobs | Laravel (очереди) |
| 14 | messages | Чат |
| 15 | migrations | Laravel |
| 16 | notification_templates | Уведомления |
| 17 | password_reset_tokens | Авторизация |
| 18 | role_user | Авторизация |
| 19 | roles | Авторизация |
| 20 | sessions | Laravel |
| 21 | system_settings | Надежность / настройки |
| 22 | users | Авторизация |

**Итого в БД: 22 таблицы.** Модуль Надежность работает в режиме заглушек без таблиц и без SQL.

---

## Таблицы, которые НУЖНО ОСТАВИТЬ

### Авторизация и пользователи
| Таблица | Назначение |
|--------|------------|
| `users` | Пользователи |
| `roles` | Роли |
| `auth_permissions` | Права доступа |
| `auth_roles_permissions` | Связь ролей и прав (pivot) |
| `role_user` | Связь пользователей и ролей (pivot) |
| `departments` | Подразделения (user.department_id) |
| `aircraft_type_user` | Типы ВС пользователя (pivot) |
| `password_reset_tokens` | Сброс пароля (Laravel) |
| `sessions` | Сессии (Laravel) |

### Модуль Надежность
| Таблица | Назначение |
|--------|------------|
| `rel_failures` | Отказы |
| `rel_failure_attachments` | Вложения к отказам |
| `rel_failure_systems` | Подсистемы/системы |
| `rel_failure_aggregates` | Агрегаты |
| `rel_failure_detection_stages` | Этапы выявления |
| `rel_failure_consequences` | Последствия |
| `rel_wo_statuses` | Статусы ЗНР |
| `rel_engine_types` | Типы двигателей |
| `rel_engine_numbers` | Номера двигателей |
| `rel_taken_measures` | Принятые меры |
| `rel_failure_form_settings` | Настройки формы отказа |
| `rel_buf_settings` | Настройки отчёта БУФ |
| `aircraft` | Воздушные суда |
| `aircrafts_types` | Типы ВС |
| `system_settings` | Системные настройки |
| `spi_flight_data` | Данные полётов по месяцам (график мониторинга) |
| `spi_flight_data_weekly` | Данные полётов по неделям (график мониторинга) |

### Настройки (справочники и т.п.)
| Таблица | Назначение |
|--------|------------|
| `system_settings` | В т.ч. модули дашборда, общие настройки |
| `aircraft` | Парк ВС (Fleet) |
| `aircrafts_types` | Типы ВС |
| `airports` | Аэропорты |
| `events` | Справочник мероприятий |
| `flight_statuses` | Статусы рейсов |
| `minimum_crew` | Минимальный состав экипажа |
| `positions` | Должности |
| `requirements` | Требования к экипажу |
| `requirement_types` | Типы требований |
| `crews` | Экипажи/сотрудники |
| `readiness_types` | Типы готовности (ReadinessType) |
| `flight_readiness_type` | Связь рейсов и типов готовности (pivot) |
| `flights` | Рейсы (для мероприятий экипажа в настройках) |
| `events_crew` | Мероприятия экипажа по рейсам |
| `crew_aircraft_types` | Типы ВС экипажа (pivot) |
| `templatetlgxlsx` | Шаблоны сообщений (Настройки → сообщения) |

### Чат
| Таблица | Назначение |
|--------|------------|
| `chats` | Чаты |
| `messages` | Сообщения чата |
| `chat_participants` | Участники чата |

### Уведомления
| Таблица | Назначение |
|--------|------------|
| `notification_templates` | Шаблоны уведомлений |
| `email_logs` | Логи отправки email |

---

## Таблицы, которые МОЖНО УДАЛИТЬ

Удаление выполняйте только после резервной копии БД. Порядок может быть важен из‑за внешних ключей (сначала дочерние, потом родительские).

### Инспекции (insp_*)
- `insp_inspections`
- `insp_departments`
- `insp_audit_subtypes`
- `insp_audit_types`
- `insp_report_approvals`
- `insp_remarks`
- `insp_nonconformity_types`
- `insp_inspection_answers`
- `insp_checklists`
- `insp_checklist_questions`

### Управление рисками (rm_*)
- `rm_risks`
- `rm_risk_documents`
- `rm_corrective_measures`
- `rm_risk_residual_assessment_history`
- `rm_risk_assessment_history`
- `rm_risk_changes`
- `rm_risk_notifications`
- `rm_identification_settings`
- `rm_assessment_settings`
- `rm_danger_characteristics`
- `rm_categories`
- `rm_programs`
- `rm_areas`
- `rm_department_codes`
- `rm_risk_areas` (если есть)

### Система сообщений / Safety Reporting (sr_*)
- `sr_messages`
- `sr_message_actions`
- `sr_event_description_message_notifications`
- `sr_message_changes`
- `sr_message_analysis`
- `sr_message_feedback`
- `sr_message_risk_assessments`
- `sr_message_data`
- `sr_message_type_sections`
- `sr_message_type_fields`
- `sr_message_event_descriptions`
- `sr_message_types`
- `sr_operation_stages`
- `sr_factors`
- `sr_aircraft_event_types`
- `sr_time_of_day`
- `sr_sources`
- `sr_hazardous_weather`
- `sr_hazard_factor_details`
- `sr_hazard_factors`
- `sr_asobp_codes`
- `sr_customers`
- `sr_activity_areas`
- `sr_message_field_definitions` (если есть)

### Документация (doc_*)
- `doc_documents`
- `doc_module_settings`
- `doc_document_approvals`
- `doc_document_approval_files`
- `doc_document_familiarizations`
- `doc_document_approval_sheet`
- `doc_document_approvers`
- `doc_subcategories`
- `doc_categories`
- `doc_sections`

### Планирование (pl_* и др.)
- `pl_flight_changes_history`
- `pl_flight_pax`
- `pl_mnt_resources`
- `pl_crew_performance_settings`
- `crewperformances`
- `crewperformancelistpersonells`
- `flight_pax`
- `flight_servises`
- `flight_readiness_type` — **не удалять**, используется в настройках (Events)
- `services` (если не используется в других оставшихся разделах)

### Обучение (tr_*)
- `tr_course_lessons`
- `tr_questions`
- `tr_lesson_progress`
- `tr_question_groups`
- `tr_question_answers`
- `tr_lesson_files`
- `tr_course_assignments`

### SPI (кроме данных для графика)
- `spi_indicator_levels`
- `spi_settings`

### Исполнительская дисциплина
- `executive_discipline_measures`
- `executive_discipline_measure_history`

### Прочие
- `special_situation_types`
- `maintenance_types` (маршрут настроек удалён)
- `risk_registry` (если есть и не используется)
- Таблицы, которые есть в БД, но не входят в список «оставить» выше (например старые или неиспользуемые).

---

## Важно

1. Сделайте полный бэкап БД перед удалением таблиц.
2. Проверьте внешние ключи: при необходимости удаляйте в порядке зависимостей (сначала таблицы, которые ссылаются на другие, затем основные).
3. После удаления таблиц код моделей и контроллеров для удалённых модулей можно не трогать, но при обращении к ним появятся ошибки. Логичнее удалить или отключить неиспользуемые модели (Inspections, RiskManagement, SafetyReporting, Documentation, Training, SPI, ExecutiveDiscipline и т.д.), чтобы не обращаться к несуществующим таблицам.
