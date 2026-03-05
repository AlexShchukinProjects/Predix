# Обзор данных Excel и проектирование базы данных

## 1. Что это такое

Это данные для **анализа критичности инспекций** (Critical Findings Analysis) в авиационном техническом обслуживании:

- **Routine Inspection (RC)** — плановые карты инспекций по источникам: MPD, AD, SB, EO, заказные задачи клиента.
- **Non-Routine Cards (NRC)** — неплановые карты (в т.ч. по структуре и другим skill code), открытые по результатам RC.
- **EEF (Engineering Enquiry)** — инженерные запросы, связанные с NRC.
- **Материалы** — заказы запчастей/материалов по картам (RC/NRC), чтобы оценивать потребность в материалах при похожих находках.

Цель процесса (из `explain.md`):

1. Получить от заказчика список источников плановых инспекций (MPD / AD / SB / EO / заказные задачи).
2. По мастер-данным в Excel посчитать:
   - сколько раз выполнялась одна и та же плановая карта (RC);
   - сколько по ней поднималось неплановых карт (NRC);
   - макс. трудозатраты (man-hours) по NRC по этой RC;
   - количество инженерных запросов (EEF) по этим NRC.
3. Оценить **критичность инспекции**:
   - если по RC/NRC были EEF — разобрать предыдущие кейсы (PDF) и решить, критично ли;
   - если макс. трудозатраты NRC > 50 ч — ручной разбор и оценка критичности;
   - вероятность критичности = (число карт с критичными находками) / (общее число предыдущих карт).

Дополнительно используется **IC_0097 Material Data (WING)**: материалы, заказанные по RC/NRC, чтобы понимать, какие материалы понадобятся при похожей находке.

---

## 2. Структура данных по Master File Structure.pdf

Описание сущностей и связей (как должно быть в Master Data и смежных источниках).

### 2.1 PR_0059 – Work Card Inquiry (WINGS)

| Поле | Описание | Связь |
|------|----------|--------|
| TC# (GAES WO Ref.) | Номер рабочего порядка GAES | Связь с EEF Registry |
| Project | Проект | → PR_0112, PR_0030 |
| A/C Registration | Бортовой номер ВС | |
| Source Card Ref. | Источник карты (MPD/AD/SB/EO/заказное) | |
| RC/NRC Description | Описание плановой/неплановой карты | |
| Rectification action & Ref. | Устранение и ссылка | |
| Skill Code | Код специализации (в т.ч. Structure и др.) | |
| MHrs Spent | Затрачено человеко-часов | |
| No. of Child card | Количество неплановых карт (NRC) по данной RC | |

**Роль:** ядро для анализа — какая RC выполнялась, сколько по ней было NRC и трудозатрат.

### 2.2 PR_0112 – Project Inquiry (WINGS)

| Поле | Описание | Связь |
|------|----------|--------|
| Project | Проект | ← PR_0059 |
| Scope | Объём работ | |
| Customer | Заказчик | |
| Flight Cycles (FC) | Накопленные циклы | |
| Flight Hours (FH) | Накопленные часы | |

**Роль:** контекст по проекту и заказчику для Work Cards.

### 2.3 PR_0030 – Aircrafts (WINGS)

| Поле | Описание | Связь |
|------|----------|--------|
| Project | Проект | ← PR_0059 |
| MSN | Manufacturer Serial Number | → Airfleets |

**Роль:** привязка проекта к конкретному ВС (MSN).

### 2.4 A/C Data Extraction (Airfleets)

| Поле | Описание | Связь |
|------|----------|--------|
| MSN | Серийный номер | ← PR_0030 |
| First Flight | Первый полёт (для возраста ВС на момент индукции) | |
| Current A/C Status | Текущий статус ВС | |

**Роль:** возраст и статус ВС при анализе инспекций.

### 2.5 EEF Registry (Excel → EEF Data)

| Поле | Описание | Связь |
|------|----------|--------|
| TC# (GAES WO Ref.) | Номер WO | ← PR_0059 |
| EEF Ref# | Номер EEF | |
| EEF Subject | Тема запроса | |
| EEF remarks | Замечания | |
| Source Card Ref. | Источник карты (MPD/AD/SB/EO/заказное) | |

**Роль:** по наличию EEF по карте решается, нужно ли вручную оценивать критичность (особенно для структуры).

### 2.6 External Sources (From Customers → Sample Customer Data)

| Поле | Описание |
|------|----------|
| WO Ref# | Номер WO |
| A/C Registration | Бортовой номер |
| Source Card Ref. | MPD/AD/SB/EO/заказное |
| RC/NRC Description | Описание |
| Rectification action & Ref | Устранение и ссылка |
| Flight Cycles (FC) | Циклы |
| Flight Hours (FH) | Часы |

**Роль:** данные от заказчиков для того же анализа, когда нет WINGS.

### 2.7 GAES Data – IC_0097 Material Data (WING)

Реальный файл в проекте: `excel/GAES Data/IC_0097_Material_Data.csv`.

Содержит строки заказов материалов по картам, в т.ч.:

- **PROJECT #**, **WORK ORDER #** — проект и WO (связь с PR_0059 по TC#/WO).
- **SOURCE CARD #**, **CUSTOMER WORK CARD**, **SOURCE CUSTOMER CARD** — источник карты (MPD/AD/SB/EO и т.д.).
- **TAIL #** — борт (A/C Registration).
- **CARD DESCRIPTION** — описание задачи (RC/NRC).
- **PART #**, **DESCRIPTION**, **REQ. QTY.**, **ORDER QTY.**, даты заказа/поставки, стоимость и т.д.

**Роль:** список материалов по RC/NRC для оценки потребности при похожих находках (как в `explain.md`).

### 2.8 Sample Previous Case Analysis

По `explain.md` — это **PDF предыдущих кейсов**: по картам, у которых были EEF или большие трудозатраты NRC, вручную просматриваются PDF и определяется, были ли критические находки. В базе достаточно хранить ссылку на файл/путь и привязку к WO или Source Card Ref.

---

## 3. Взаимосвязь данных (схема)

```
┌─────────────────┐     Project      ┌─────────────────┐
│   PR_0112       │◄────────────────│   PR_0059       │
│   Project       │                  │   Work Card     │
│   (Scope,       │                  │   (TC#, RC/NRC, │
│   Customer,     │                  │   MHrs, NRC     │
│   FC, FH)       │                  │   count, etc.)  │
└─────────────────┘                  └────────┬───────┘
                                               │
                    ┌──────────────────────────┼──────────────────────────┐
                    │ TC# (GAES WO Ref.)        │                          │
                    ▼                          ▼                          │
           ┌─────────────────┐       ┌─────────────────┐                  │
           │  EEF Registry   │       │ IC_0097         │                  │
           │  (EEF Ref#,     │       │ Material Data   │                  │
           │   Subject,      │       │ (PART#, QTY,    │                  │
           │   Source Card)  │       │  per WO/Card)   │                  │
           └─────────────────┘       └─────────────────┘                  │
                                                                          │
┌─────────────────┐     Project      ┌─────────────────┐                  │
│   PR_0030       │◄────────────────│   A/C Data      │                  │
│   Aircrafts     │                  │   (Airfleets)   │                  │
│   (Project,     │──── MSN ────────►│   (MSN, First   │                  │
│   MSN)          │                  │   Flight,       │                  │
└─────────────────┘                  │   Status)       │                  │
                                     └─────────────────┘                  │
                                                                          │
                    External (Customer): WO Ref#, A/C Reg, Source Card,   │
                    RC/NRC Description, Rectification, FC, FH             │
                    → те же сущности по смыслу (Work Card + проект/ВС)    │
```

- **Связи:** Project связывает Work Cards (PR_0059), Projects (PR_0112) и Aircrafts (PR_0030). TC# (GAES WO Ref.) связывает Work Card с EEF Registry и с Material Data (WORK ORDER #). MSN связывает PR_0030 с A/C Data (Airfleets).

---

## 4. Как сделать базу данных и работать с данными

### 4.1 Предлагаемые таблицы (нормализованная схема)

| Таблица | Назначение | Ключ |
|---------|------------|------|
| **projects** | Проекты (PR_0112) | id |
| **aircrafts** | ВС по проектам (PR_0030 + Airfleets) | id, msn |
| **work_cards** | Карты работ RC/NRC (PR_0059 + External) | id, tc_number (GAES WO Ref) |
| **eef_registry** | EEF по WO (EEF Data) | id, work_card_id / tc_number |
| **work_card_materials** | Материалы по картам (IC_0097) | id, work_order_id / work_card_id |
| **source_card_refs** | Справочник источников (MPD/AD/SB/EO/Customer) | id, code, name |
| **previous_case_analyses** | Ссылки на PDF предыдущих кейсов + критичность | id, work_card_id, file_path, is_critical |

Связи:

- `work_cards.project_id` → `projects.id`
- `work_cards.aircraft_id` или `work_cards.ac_registration` (можно денормализовать хвост)
- `eef_registry.work_card_id` → `work_cards.id` (или связь по tc_number)
- `work_card_materials.work_card_id` или `work_order_number` → `work_cards.tc_number`
- `work_cards.source_card_ref_id` → `source_card_refs.id` (или хранить строку Source Card Ref)

### 4.2 Поля таблицы `work_cards` (ядро анализа)

- tc_number (GAES WO Ref.), project_id, ac_registration, source_card_ref (или id)
- rc_nrc_description, rectification_action_ref, skill_code
- mhrs_spent, no_of_child_cards (число NRC)
- source: 'wings' | 'customer' (PR_0059 vs External)
- flight_cycles, flight_hours (если есть)

### 4.3 Поля для материалов (из IC_0097)

- project_number, work_order_number (связь с work_cards.tc_number)
- tail_number, card_description, source_card_number
- part_number, description, req_qty, order_qty, unit_cost, currency
- даты: req_dt, order_dt, receipt_dt и т.д.

### 4.4 Как работать с данными в приложении

1. **Импорт**
   - Загружать CSV/Excel в соответствующие таблицы (проекты, work_cards, eef_registry, work_card_materials).
   - Для EEF Data, Master Data, Sample Customer Data — тот же маппинг колонок из PDF; при необходимости один и тот же импортёр с разными маппингами для WINGS и External.
   - Sample Previous Case Analysis: хранить путь к PDF и связь с work_card/source_card; критичность (да/нет) — отдельное поле после ручного разбора.

2. **Анализ критичности (как в explain.md)**
   - Группировка по Source Card Ref (или по source_card_ref_id): для каждой RC считать количество выполнений, количество NRC, макс. MHrs по NRC, количество EEF.
   - Фильтры: задачи с EEF; задачи с max NRC man-hours > 50.
   - Для отобранных: список предыдущих кейсов (ссылки на PDF) и ручная отметка критичности; затем расчёт доли критичных от общего числа карт (вероятность критичности).

3. **Материалы**
   - По выбранной RC/NRC (или по Source Card Ref) выбирать из `work_card_materials` типичный набор запчастей/материалов для планирования при похожих находках.

4. **Интерфейс**
   - Справочники: проекты, ВС, источники карт (MPD/AD/SB/EO).
   - Список Work Cards с фильтрами по проекту, источнику, наличию EEF, диапазону MHrs.
   - Агрегаты по Source Card: количество RC, NRC, max MHrs, число EEF, рассчитанная/введённая критичность.
   - Экран «Предыдущие кейсы»: привязка PDF к карте, флаг «критично».
   - Отчёты/экспорт по материалам по выбранным картам.

### 4.5 Пошаговый план внедрения

1. Создать миграции для таблиц: `projects`, `aircrafts`, `work_cards`, `eef_registry`, `work_card_materials`, `source_card_refs`, `previous_case_analyses`.
2. Реализовать импорт из CSV (начиная с `IC_0097_Material_Data.csv`) в `work_card_materials` и при необходимости в `work_cards` (если по одному CSV можно восстановить карты по WORK ORDER #).
3. Добавить импорт для EEF Registry и Master Data (PR_0059, PR_0112, PR_0030), когда файлы появятся в папках EEF Data и Master Data.
4. Реализовать экраны: список карт, агрегаты по Source Card, связь с EEF и материалами.
5. Добавить хранение ссылок на PDF предыдущих кейсов и поле критичности; форму расчёта вероятности критичности по объяснению из `explain.md`.

После этого система будет опираться на единую БД и сможет использовать все перечисленные источники (WINGS, EEF, материалы, данные заказчика, предыдущие кейсы) для анализа критичности инспекций и планирования материалов.
