# Мини-план: упрощенные номинации (PHP 7 / MySQL 5.x)

## Цель
- Сделать 2 номинации без групп, клубов и городов:
  - `Найактивніший гравець по місяцях` (`type=bp`)
  - `Найбільший прогрес` (`type=pm`)
- Вывод: один общий список `TOP 10`.

## Модули и файлы
- Новый модуль (не ломать текущий): `modules/nomination_simple/`
  - `object.nomination_simple.php`
  - `action/show.php`
  - `func/func.nomination_simple.php`
  - `action/toexcel_nomination.php` (опционально)
- Меню/ссылка:
  - `config/const_admin.php` -> добавить пункт `#nomination_simple-show`

## Маршрут и параметры
- URL формат:
  - `#nomination_simple-show-type=bp&year=2026&month=3`
  - `#nomination_simple-show-type=pm&year=2026&month=3`
- Параметры: только `type`, `year`, `month`.

## Логика `show`
1. Прочитать `type/year/month`.
2. Определить `minYear/maxYear` и доступные месяцы выбранного года.
3. Сформировать период:
   - `dbeg = YYYY-MM-01`
   - `dend = YYYY-MM-last_day`
4. В зависимости от `type` вызвать SQL-функцию (`bp` или `pm`).
5. Нарисовать одну таблицу результатов (без секций групп).

## SQL: Найактивніший гравець (`bp`)
```sql
SELECT
    p.id,
    p.name,
    COUNT(DISTINCT tp.turnir_id) AS cnt_turnirs,
    SUM(tp.cnt_games) AS cnt_games,
    SUM(tp.cnt_sets) AS cnt_sets,
    SUM(tp.cnt_wins) AS cnt_wins,
    ROUND((SUM(tp.cnt_wins) / NULLIF(SUM(tp.cnt_games), 0)) * 100, 2) AS proc_wins
FROM bs_turnirplayers tp
JOIN bs_turnirs t ON t.id = tp.turnir_id
JOIN bs_players p ON p.id = tp.player_id
WHERE t.dat BETWEEN :dbeg AND :dend
  AND tp.cnt_games > 0
  AND t.ispara = 0
GROUP BY p.id, p.name
ORDER BY cnt_turnirs DESC, cnt_games DESC, cnt_sets DESC, proc_wins DESC
LIMIT 10;
```

## SQL: Найбільший прогрес (`pm`)
```sql
SELECT
    p.id,
    p.name,
    SUM(tp.end_reiting - tp.beg_reiting) AS diff_reit,
    COUNT(DISTINCT tp.turnir_id) AS cnt_turnirs,
    SUM(tp.cnt_wins) AS cnt_wins,
    SUM(tp.cnt_lose) AS cnt_lose,
    ROUND((SUM(tp.cnt_wins) / NULLIF(SUM(tp.cnt_games), 0)) * 100, 2) AS proc_wins
FROM bs_turnirplayers tp
JOIN bs_turnirs t ON t.id = tp.turnir_id
JOIN bs_players p ON p.id = tp.player_id
WHERE t.dat BETWEEN :dbeg AND :dend
  AND tp.cnt_games > 0
  AND t.ispara = 0
GROUP BY p.id, p.name
HAVING diff_reit IS NOT NULL
ORDER BY diff_reit DESC, proc_wins DESC
LIMIT 10;
```

## UI (упрощение)
- Оставить только фильтры:
  - месяц
  - год
- Убрать:
  - город
  - клуб
  - разбиение на группы (`Діти/Дорослі/...`).

## Совместимость с PHP 7 / MySQL 5.x
- Не использовать CTE (`WITH`), оконные функции, JSON SQL.
- Только `JOIN`, `GROUP BY`, `HAVING`, агрегаты, `NULLIF`.
- PHP-стиль без typed properties/union types.

## Мини-чеклист внедрения
1. Создать `nomination_simple` и роут `show`.
2. Добавить 2 SQL-функции (`getSQLBestPlayerSimple`, `getSQLBestDiffSimple`).
3. Добавить 2 рендера таблиц (`bp/pm`).
4. Подключить в меню `const_admin.php`.
5. Проверить hash-навигацию и 2 типа отчета.
6. (Опционально) подключить Excel-экспорт для нового модуля.
