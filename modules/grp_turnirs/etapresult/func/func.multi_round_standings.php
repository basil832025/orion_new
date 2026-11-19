<?php

function multi_round_group_stages(int $turnirId): array
{
    if ($turnirId <= 0) {
        return [];
    }

    return db_list('SELECT id, name_etap FROM '.T_ETAPS.'
        WHERE turnir_id='.$turnirId.' AND type_etap=1 ORDER BY id') ?: [];
}

function multi_round_escape(string $name): string
{
    return htmlspecialchars(stripslashes($name), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function multi_round_enabled(array $tournament): bool
{
    return !empty($tournament['is_multi_round']) && empty($tournament['is_command'])
        && empty($tournament['is_team_league']);
}

function multi_round_tournament(int $turnirId): array
{
    static $tournaments = [];
    return $tournaments[$turnirId] ??= db_row('SELECT t.*, l.is_team_league
        FROM '.T_TURNIRS.' t LEFT JOIN bs_leagues l ON l.id=t.league_id
        WHERE t.id='.$turnirId) ?: [];
}

function multi_round_calculate(array $rounds, array $participants, array $matches): array
{
    $rows = [];
    $groups = [];
    $playedPairs = array_fill_keys(array_column($rounds, 'id'), []);
    foreach ($participants as $participant) {
        $id = (int)$participant['player_id'];
        $groups[$participant['etap_id']][$participant['group_num']][$id] = true;
        $rows[$id] = [
            'name' => $participant['name'] ?? '#'.$id,
            'clubRating' => (float)($participant['club_rating'] ?? 0),
            'played' => 0, 'wins' => 0, 'points' => 0, 'setsFor' => 0, 'setsAgainst' => 0,
            'roundPoints' => [], 'opponentPoints' => [],
        ];
    }

    $provisional = count($rounds) < 2 || count($rows) < 2;
    foreach ($matches as $match) {
        $roundId = (int)$match['etap_id'];
        $first = (int)$match['pl_id_1'];
        $second = (int)$match['pl_id_2'];
        $firstScore = (string)$match['set_1'];
        $secondScore = (string)$match['set_2'];
        $firstSets = tie_set_to_int($firstScore);
        $secondSets = tie_set_to_int($secondScore);
        if ($first === $second || !isset($rows[$first], $rows[$second])
            || $firstSets === $secondSets) {
            $provisional = true;
            continue;
        }

        $pair = min($first, $second).':'.max($first, $second);
        if (isset($playedPairs[$roundId][$pair])) {
            $provisional = true;
            continue;
        }
        $playedPairs[$roundId][$pair] = true;
        if (!empty($match['perenos_etap'])) {
            continue;
        }
        foreach ([
            [$first, $second, $firstScore, $secondScore, $firstSets, $secondSets],
            [$second, $first, $secondScore, $firstScore, $secondSets, $firstSets],
        ] as [$id, $opponent, $score, $opponentScore, $setsFor, $setsAgainst]) {
            $points = etapresult_get_match_points($score, $opponentScore);
            $row = &$rows[$id];
            $row['played']++;
            $row['wins'] += (int)($points === 2);
            $row['points'] += $points;
            $row['setsFor'] += $setsFor;
            $row['setsAgainst'] += $setsAgainst;
            $row['roundPoints'][$roundId] = ($row['roundPoints'][$roundId] ?? 0) + $points;
            $row['opponentPoints'][$opponent] = ($row['opponentPoints'][$opponent] ?? 0) + $points;
            unset($row);
        }
    }

    foreach ($playedPairs as $roundId => $pairs) {
        $expectedMatches = 0;
        foreach ($groups[$roundId] ?? [] as $members) {
            $size = count($members);
            $expectedMatches += intdiv($size * ($size - 1), 2);
        }
        $provisional = $provisional || $expectedMatches === 0 || count($pairs) !== $expectedMatches;
    }

    $missingRatings = [];
    foreach ($rows as &$row) {
        $headToHead = 0;
        foreach ($row['opponentPoints'] as $opponent => $points) {
            if ($rows[$opponent]['points'] === $row['points']) {
                $headToHead += $points;
            }
        }
        $row['rank'] = [$row['points'], $headToHead, $row['setsFor'] - $row['setsAgainst']];
        if ($row['clubRating'] <= 0) {
            $missingRatings[implode(':', $row['rank'])] = true;
        }
    }
    unset($row);

    foreach ($rows as &$row) {
        // Apply the rating only when every participant tied on match results has one.
        $row['rank'][] = isset($missingRatings[implode(':', $row['rank'])]) ? 0.0 : $row['clubRating'];
    }
    unset($row);

    usort($rows, static function ($a, $b) {
        return ($b['rank'] <=> $a['rank']) ?: strnatcasecmp($a['name'], $b['name']);
    });
    return [$rows, $provisional];
}

function multi_round_render(int $turnirId, array $rounds, bool $isDoubles): string
{
    $html = '<div class="container-fluid"><div class="big-table overflow-auto"><div class="big-table_left">'
        .'<div class="obertka_table"><h2 class="zagolovokGrp mb-0">Загальний залік</h2>';
    $close = '</div></div></div></div>';
    if (!$rounds) {
        return $html.'<p>Створіть групові етапи турніру, щоб відобразити загальний залік.</p>'.$close;
    }

    $ids = implode(',', array_map('intval', array_column($rounds, 'id')));
    $participants = db_list('SELECT DISTINCT m.etap_id, m.groups AS group_num, m.player_id, p.name,
        (SELECT MAX(tp.beg_reiting) FROM '.T_TURNIR_PLAYERS.' tp
            WHERE tp.turnir_id=m.turnir_id AND tp.player_id=m.player_id) AS club_rating
        FROM '.T_ETAPS_PLAYER_MESTA.' m LEFT JOIN '.T_PLAYERS.' p ON p.id=m.player_id
        WHERE m.turnir_id='.$turnirId.' AND m.etap_id IN ('.$ids.') AND m.player_id>0') ?: [];
    $matches = db_list('SELECT etap_id, pl_id_1, pl_id_2, set_1, set_2, perenos_etap FROM '.T_REITING.'
        WHERE turnir_id='.$turnirId.' AND etap_id IN ('.$ids.') AND type_game=1
          AND COALESCE(match_id,\'\')=\'\' ORDER BY id') ?: [];

    [$rows, $provisional] = multi_round_calculate($rounds, $participants, $matches);
    if ($provisional) {
        $html .= '<p class="ft14 mb-0">Попередні результати</p>';
    }
    if (!$rows) {
        return $html.'<p>У групових етапах ще немає учасників.</p>'.$close;
    }

    $heading = '<th scope="col" class="ft14 fw700 text-center num2">';
    $cell = '<td class="text-center ft14 align-middle">';
    $html .= '<table class="table bordered2 table-hover table-bordered rounded-pill border-light-subtle">'
        .'<thead class="th_color_rose"><tr>'.$heading.'Місце</th><th scope="col" class="fio ft14 fw700">'
        .($isDoubles ? 'Пара' : 'Гравець').'</th>'.$heading.'Матчі</th>'.$heading.'Перемоги</th>';
    foreach ($rounds as $round) {
        $html .= $heading.multi_round_escape($round['name_etap']).'</th>';
    }
    $html .= $heading.'Очки всього</th>'.$heading.'Особисті зустрічі</th>'
        .$heading.'Сети (+/−)</th>'.$heading.'Рейтинг клубу</th></tr></thead><tbody>';
    $previousRank = null;
    $place = 0;
    foreach ($rows as $index => $row) {
        if ($row['rank'] !== $previousRank) {
            $place = $index + 1;
        }
        $previousRank = $row['rank'];
        $html .= '<tr>'.$cell.($row['played'] ? $place : '—').'</td>'
            .'<th scope="row" class="fio ft14 align-middle fw-normal">'.multi_round_escape($row['name']).'</th>'
            .$cell.$row['played'].'</td>'.$cell.$row['wins'].'</td>';
        foreach ($rounds as $round) {
            $html .= $cell.($row['roundPoints'][$round['id']] ?? '—').'</td>';
        }
        $html .= $cell.'<strong>'.$row['points'].'</strong></td>'.$cell.$row['rank'][1].'</td>'.$cell
            .$row['setsFor'].'–'.$row['setsAgainst'].' ('.sprintf('%+d', $row['rank'][2]).')</td>'
            .$cell.($row['clubRating'] > 0 ? rtrim(rtrim(number_format($row['clubRating'], 2, '.', ''), '0'), '.') : '—')
            .'</td></tr>';
    }
    return $html.'</tbody></table>'.$close;
}
