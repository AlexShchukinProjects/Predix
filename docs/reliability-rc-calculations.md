## # of RC (Number of RC)

**Определение:** количество записей в RC Master Data, у которых CUST. CARD содержит MPD данного отказа.


## Max Hours on RC

**Определение:** максимальное значение ACT. TIME (часов) среди записей RC Master Data, у которых CUST. CARD содержит **Task card** данного отказа.



**Особенности:**

- Для поиска в RC Master Data используется поле **Task card** отказа (`work_order_number`), а не MPD.
- В БД `act_time` может храниться как строка; для корректного максимума используется `CAST(... AS DECIMAL(15,2))`.
- Если подходящих строк нет или все `act_time` пустые/нечисловые, результат — `NULL` (в интерфейсе показывается как «—»).
- В таблице отказов значение выводится с двумя знаками после запятой.

---

## # of STR NRCs

**Определение:** количество записей в NRC Master Data, у которых SRC. CUST. CARD содержит MPD данного отказа **и** PRIM. SKILL содержит подстроку «STR».

**Источник:** таблица `NRC_master_data` (вкладка NRC в Master Data: `?source=nrc`). Колонка C — SRC. CUST. CARD (`src_cust_card`), колонка E — PRIM. SKILL (`prim_skill`). Поле отказа для поиска — **MPD** (как в Excel колонка D).

**Формула (логика):**

```
# of STR NRCs = COUNT(*) по строкам NRC_master_data,
                где MPD отказа не пустой
                и src_cust_card содержит MPD отказа
                и prim_skill содержит 'STR'
```

**SQL-подзапрос (как в коде):**

```sql
SELECT COUNT(*)
FROM NRC_master_data
WHERE COALESCE(TRIM(rel_stub.mpd), '') != ''
  AND NRC_master_data.src_cust_card LIKE CONCAT('%', rel_stub.mpd, '%')
  AND NRC_master_data.prim_skill LIKE '%STR%'
```

**Соответствие Excel:** `=СЧЁТЕСЛИМН(лист!$C:$C; "*"&D53&"*"; лист!$E:$E; "*STR*")` — C = SRC. CUST. CARD, E = PRIM. SKILL, D = MPD.

---

## Max MHs on STR NRC

**Определение:** максимальное значение ACT. TIME (часов) среди записей NRC Master Data, у которых SRC. CUST. CARD содержит MPD данного отказа **и** PRIM. SKILL содержит подстроку «STR».

**Источник:** таблица `NRC_master_data`. Колонка G — ACT. TIME (`act_time`), C — SRC. CUST. CARD (`src_cust_card`), E — PRIM. SKILL (`prim_skill`). Поле отказа — **MPD** (D в Excel).

**Формула (логика):** те же условия, что и для # of STR NRCs; берётся **MAX(ACT. TIME)** вместо COUNT.

**SQL-подзапрос (как в коде):**

```sql
SELECT MAX(CAST(NRC_master_data.act_time AS DECIMAL(15,2)))
FROM NRC_master_data
WHERE COALESCE(TRIM(rel_stub.mpd), '') != ''
  AND NRC_master_data.src_cust_card LIKE CONCAT('%', rel_stub.mpd, '%')
  AND NRC_master_data.prim_skill LIKE '%STR%'
```

**Соответствие Excel:** `=МАКСЕСЛИ(лист!$G:$G; лист!$C:$C; "*"&D51&"*"; лист!$E:$E; "*STR*")` — G = ACT. TIME, C = SRC. CUST. CARD, E = PRIM. SKILL, D = MPD.

---

## AVG STR MHs

**Определение:** среднее ACT. TIME (часов) по записям NRC Master Data с теми же условиями (SRC. CUST. CARD содержит MPD, PRIM. SKILL содержит «STR»), делённое на **# of RC** (E в Excel). При ошибке или делении на ноль — 0.

**Формула Excel:** `=ЕСЛИОШИБКА(СРЗНАЧЕСЛИМН(лист!$G:$G; лист!$C:$C; "*"&D51&"*"; лист!$E:$E; "*STR*"); 0) / E51` — G = ACT. TIME, C = SRC. CUST. CARD, E = PRIM. SKILL, D = MPD, E51 = # of RC.

**Логика:** сначала считается **AVG(ACT. TIME)** по NRC при условиях (как для # of STR NRCs); при отсутствии строк среднее считаем 0; результат делится на # of RC. Если # of RC = 0 — выводится 0.

**SQL-подзапрос (среднее):** `AVG(CAST(NRC_master_data.act_time AS DECIMAL(15,2)))` с тем же `WHERE`, что у # of STR NRCs. Итоговое значение в приложении: **(avg_str_mhs_raw ?? 0) / num_rc** при num_rc > 0, иначе 0. В таблице — с двумя знаками после запятой.

---

## EEF Count

**Определение:** количество записей в NRC Master Data, у которых SRC. CUST. CARD содержит MPD данного отказа, PRIM. SKILL содержит «STR» **и** колонка EEF (I) не пустая (`<> ""` в Excel).

**Формула Excel:** `=СЧЁТЕСЛИМН(лист!$C:$C; "*"&D51&"*"; лист!$E:$E; "*STR*"; лист!$I:$I; "<>")` — C = SRC. CUST. CARD, E = PRIM. SKILL, I = EEF (не пусто), D = MPD.

**SQL-подзапрос (как в коде):** те же условия, что у # of STR NRCs, плюс `COALESCE(TRIM(NRC_master_data.eef), '') != ''`.

---

## % EEF

**Определение:** доля записей с непустым EEF среди STR NRC, в процентах.

**Формула:** `=ЕСЛИОШИБКА(L/H; 0)` — L = EEF Count, H = # of STR NRCs. При делении на ноль (когда # of STR NRCs = 0) результат 0.

В приложении: если # of STR NRCs > 0, то **% EEF** = (EEF Count / # of STR NRCs) × 100; иначе 0. В таблице выводится с двумя знаками после запятой (например, 25.00).

---

## Probabile Critical Findings

**Определение:** произведение двух процентных полей **%** (I) и **% EEF** (M), результат в процентах.

**Формула:** `=I2*M2` в Excel (I = %, M = % EEF). Поскольку оба поля — проценты (0–100), при перемножении процентов результат переводится в проценты: **(% / 100) × (% EEF / 100) × 100 = (% × % EEF) / 100**. Например: 25% × 10% = 2,5%.

В приложении: **Probabile Critical Findings** = ((% × % EEF) / 100). Выводится с двумя знаками после запятой (например, 2.50).

---

## % (доля STR NRC от RC)

**Определение:** доля записей STR NRC в общем числе RC, в процентах.

**Формула:** `=ЕСЛИОШИБКА(H/E; 0)` — H = # of STR NRCs, E = # of RC. При делении на ноль (когда # of RC = 0) результат 0.

В приложении: если # of RC > 0, то **%** = (# of STR NRCs / # of RC) × 100; иначе 0. В таблице выводится с двумя знаками после запятой (например, 25.00).

---

## Где используется

- **Экран:** модуль Reliability — список отказов (таблица на странице `http://127.0.0.1:8000/modules/reliability`).
- **Расчёт:** выполняется в `App\Http\Controllers\Modules\Reliability\ReliabilityController` при формировании выборки отказов (подзапросы в `selectRaw`).
- **Фильтрация:** по полям «Max Hours on RC» и «# of STR NRCs» можно отфильтровать строки (показываются отказы с значением не меньше введённого).


---

## Соответствие Excel

В Excel использовалась формула вида:

- Для Max: `=МАКСЕСЛИ(лист!$G:$G; лист!$E:$E; "*" & B94 & "*")`
  - G — ACT. TIME  
  - E — CUST. CARD  
  - B94 — значение для поиска (в приложении для **Max Hours on RC** это **Task card** отказа).

В приложении:
- **# of RC** — поиск по **MPD** отказа в колонке CUST. CARD (RC Master Data), подсчёт количества записей.
- **Max Hours on RC** — поиск по **Task card** отказа в колонке CUST. CARD (RC Master Data), расчёт максимума ACT. TIME.
- **# of STR NRCs** — поиск по **MPD** отказа в колонке SRC. CUST. CARD (NRC Master Data) и отбор по PRIM. SKILL содержащему «STR», подсчёт количества записей.
- **Max MHs on STR NRC** — те же условия (MPD в SRC. CUST. CARD, «STR» в PRIM. SKILL), расчёт максимума ACT. TIME по NRC Master Data.
- **AVG STR MHs** — среднее ACT. TIME по NRC при тех же условиях (СРЗНАЧЕСЛИМН), затем деление на # of RC; при ошибке или num_rc=0 — 0.
- **EEF Count** — количество записей NRC с теми же условиями (MPD в SRC. CUST. CARD, «STR» в PRIM. SKILL) и непустым полем EEF (колонка I).
- **% EEF** — EEF Count / # of STR NRCs (при num_str_nrcs = 0 — 0), в процентах.
- **Probabile Critical Findings** — произведение % (I) и % EEF (M).
