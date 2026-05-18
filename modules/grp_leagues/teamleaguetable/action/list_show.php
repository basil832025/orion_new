<?php

class list_showAction extends ActionModule
{
    protected $content = '';
    protected $subMenu = array();
    protected $Java_script = '';

    function init()
    {
        $league_id = (int)poste('league_id');
        $turnir_id = (int)poste('turnir_id');

        if ($league_id <= 0) {
            SystemClass::setMessage_user('Не знайдено лігу.');
            $this->content = '';
            return;
        }

        $league_name = db_field('SELECT name FROM `bs_leagues` WHERE id='.$league_id, 'name');
        $title = !empty($league_name) ? $league_name : '';

        if ($_SESSION['is_mobile']) {
            $zagl = '<div class="compare_zagl">Таблиця ліг "'.$title.'"</div>';
        } else {
            $zagl = '<div class="poriv_zag">Таблиця ліг "'.$title.'"</div>';
        }
        SystemClass::setZaglModule('<div align="center" class="zagl">'.$zagl.'</div>');

        $sql_max = 'SELECT MAX(group_num) AS max_group FROM `bs_league_team_groups` WHERE league_id='.$league_id;
        $max_group = (int)db_field($sql_max, 'max_group');

        if ($max_group <= 0) {
            $this->content = '<div class="container-fluid"><div class="alert alert-warning" role="alert">Немає розподілених команд для цієї ліги.</div></div>';
            return;
        }

        $turnir_filter = '';
        if ($turnir_id > 0) {
            $turnir_filter = ' AND t.id='.(int)$turnir_id.' ';
        }

        $reiting_turnir_filter = '';
        if ($turnir_id > 0) {
            $reiting_turnir_filter = ' AND r.turnir_id='.(int)$turnir_id.' ';
        }

        $sql_stats = 'SELECT team_id,
                             SUM(win_cnt) AS wins,
                             SUM(lose_cnt) AS losses,
                             SUM(sets_win) AS sets_win,
                             SUM(sets_lose) AS sets_lose
                      FROM (
                          SELECT
                              r.pl_id_1 AS team_id,
                              CASE WHEN r.win_player = r.pl_id_1 THEN 1 ELSE 0 END AS win_cnt,
                              CASE WHEN r.lose_player = r.pl_id_1 THEN 1 ELSE 0 END AS lose_cnt,
                              (r.set_1+0) AS sets_win,
                              (r.set_2+0) AS sets_lose
                          FROM `'.T_REITING.'` r
                          INNER JOIN `'.T_TURNIRS.'` t ON t.id=r.turnir_id AND t.league_id='.$league_id.' AND t.virt=0 AND COALESCE(t.is_no_league_stat,0)=0'.$turnir_filter.'
                          WHERE (r.pair_number = 0 OR r.pair_number IS NULL OR r.pair_number = "")
                                AND (r.match_id IS NOT NULL AND r.match_id != "")
                                AND r.pl_id_1 > 0'.$reiting_turnir_filter.'
                          UNION ALL
                          SELECT
                              r.pl_id_2 AS team_id,
                              CASE WHEN r.win_player = r.pl_id_2 THEN 1 ELSE 0 END AS win_cnt,
                              CASE WHEN r.lose_player = r.pl_id_2 THEN 1 ELSE 0 END AS lose_cnt,
                              (r.set_2+0) AS sets_win,
                              (r.set_1+0) AS sets_lose
                          FROM `'.T_REITING.'` r
                          INNER JOIN `'.T_TURNIRS.'` t ON t.id=r.turnir_id AND t.league_id='.$league_id.' AND t.virt=0 AND COALESCE(t.is_no_league_stat,0)=0'.$turnir_filter.'
                          WHERE (r.pair_number = 0 OR r.pair_number IS NULL OR r.pair_number = "")
                                AND (r.match_id IS NOT NULL AND r.match_id != "")
                                AND r.pl_id_2 > 0'.$reiting_turnir_filter.'
                      ) s
                      GROUP BY team_id';

        $sql = 'SELECT ltg.group_num, ltg.team_id, p.name AS team_name,
                       COALESCE(rs.wins,0) AS wins,
                       COALESCE(rs.losses,0) AS losses,
                       COALESCE(rs.sets_win,0) AS sets_win,
                       COALESCE(rs.sets_lose,0) AS sets_lose,
                       (COALESCE(rs.wins,0)*2 + COALESCE(rs.losses,0)) AS points_total
                FROM `bs_league_team_groups` ltg
                INNER JOIN `'.T_PLAYERS.'` p ON p.id=ltg.team_id
                LEFT JOIN ('.$sql_stats.') rs ON rs.team_id=ltg.team_id
                WHERE ltg.league_id='.$league_id.' AND p.is_team=1 AND p.not_use=0
                GROUP BY ltg.group_num, ltg.team_id, p.name, rs.wins, rs.losses, rs.sets_win, rs.sets_lose
                ORDER BY ltg.group_num ASC,
                         points_total DESC,
                         wins DESC,
                         losses ASC,
                         (sets_win - sets_lose) DESC,
                         sets_win DESC,
                         p.name ASC';
        $rows = db_list($sql);

        $groups = array();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $g = !empty($row['group_num']) ? (int)$row['group_num'] : 0;
                if ($g > 0) {
                    if (empty($groups[$g])) {
                        $groups[$g] = array();
                    }
                    $groups[$g][] = $row;
                }
            }
        }

        $this->Java_script = '
if (typeof window.showTeamRoster !== "function") {
window.showTeamRoster = function(element, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    var teamId = element.getAttribute("data-team-id");
    var turnirId = element.getAttribute("data-turnir-id") || 0;

    if (!teamId) {
        alert("Помилка: не вдалося визначити команду");
        return;
    }

    if (typeof window.__teamRosterRequestId === "undefined") {
        window.__teamRosterRequestId = 0;
    }
    var requestId = ++window.__teamRosterRequestId;

    var formData = new FormData();
    formData.append("ajax_method", "1");
    formData.append("module", "etapresult");
    formData.append("action", "team_roster");
    formData.append("team_id", teamId);
    formData.append("turnir_id", turnirId);
    formData.append("return_content_bool", "1");

    fetch("", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.text();
    })
    .then(text => {
        if (requestId !== window.__teamRosterRequestId) {
            return;
        }
        try {
            var data = JSON.parse(text);
            var rosterData = data;
            if (data.content && typeof data.content === "string") {
                rosterData = JSON.parse(data.content);
            }

            if (rosterData.error || !rosterData.success) {
                alert("Помилка: " + (rosterData.error || "не вдалося отримати склад команди"));
                return;
            }

            window.showTeamRosterModal(rosterData);
        } catch (e) {
            alert("Помилка при обробці відповіді сервера: " + e.message);
        }
    })
    .catch(function() {
        alert("Помилка при завантаженні складу команди");
    });
};

window.showTeamRosterModal = function(data) {
    var playersHtml = "";
    if (data.players && data.players.length > 0) {
        data.players.forEach(function(player, index) {
            playersHtml += "<tr>" +
                "<td class=\"text-center\" style=\"width:60px;\">" + (index + 1) + "</td>" +
                "<td>" + player.name + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting || "") + "</td>" +
                "<td class=\"text-center\" style=\"width:120px;\">" + (player.reiting_ukraine || "") + "</td>" +
                "</tr>";
        });
    } else {
        playersHtml = "<tr><td colspan=\"4\" class=\"text-center text-muted\" style=\"padding:18px;\">У команди поки немає активних гравців</td></tr>";
    }

    var modalElement = document.getElementById("teamRosterModal");
    if (!modalElement) {
        var modalHtml = "<div class=\"modal fade\" id=\"teamRosterModal\" tabindex=\"-1\" aria-hidden=\"true\">" +
            "<div class=\"modal-dialog modal-dialog-centered\" style=\"max-width:700px;\">" +
            "<div class=\"modal-content\">" +
            "<div class=\"modal-header\">" +
            "<h5 class=\"modal-title team-roster-title\" style=\"flex:1; text-align:center;\"></h5>" +
            "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Закрити\"></button>" +
            "</div>" +
            "<div class=\"modal-body\" style=\"padding:0;\">" +
            "<table class=\"table table-sm mb-0\">" +
            "<thead class=\"table-light\"><tr><th class=\"text-center\" style=\"width:60px;\">№</th><th>Гравець</th><th class=\"text-center\" style=\"width:120px;\">Рейт. клубу</th><th class=\"text-center\" style=\"width:120px;\">Рейт. ФНТУ</th></tr></thead>" +
            "<tbody class=\"team-roster-body\"></tbody>" +
            "</table>" +
            "</div>" +
            "<div class=\"modal-footer\" style=\"justify-content:center;\">" +
            "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">ОК</button>" +
            "</div>" +
            "</div></div></div>";
        document.body.insertAdjacentHTML("beforeend", modalHtml);
        modalElement = document.getElementById("teamRosterModal");
        modalElement.addEventListener("hidden.bs.modal", function() {
            var modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.dispose();
            }
        });
    }

    var titleElement = modalElement.querySelector(".team-roster-title");
    var bodyElement = modalElement.querySelector(".team-roster-body");
    if (titleElement) {
        titleElement.textContent = "Склад команди: " + (data.team_name || "");
    }
    if (bodyElement) {
        bodyElement.innerHTML = playersHtml;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
};
}
';

        $content = '<div class="container" style="max-width:1200px; margin:0 auto;">'
            .'<style>'
            .'.teamleaguetable{font-size:0.95rem;}'
            .'.teamleaguetable td,.teamleaguetable th{padding:6px 8px; white-space:nowrap;}'
            .'.teamleaguetable .team-roster-link{cursor:pointer; text-decoration:underline dotted;}'
            .'@media (max-width: 768px){'
            .'.teamleaguetable{font-size:0.8rem;}'
            .'.teamleaguetable td{padding:8px 6px !important; line-height:1.35 !important; height:auto !important; vertical-align:middle !important;}'
            .'.teamleaguetable th{padding:2px 4px !important; line-height:1 !important; height:auto !important; font-size:0.72rem !important; vertical-align:middle !important;}'
            .'.teamleaguetable tbody tr{height:auto !important;}'
            .'.teamleaguetable .team-name{white-space:normal;}'
            .'.teamleaguetable .th_players_mob{height:25px !important; min-height:25px !important;}'
            .'}'
            .'</style>';
        for ($g = 1; $g <= $max_group; $g++) {
            $content .= '<div class="poriv_zag">Ліга '.$g.'</div>';
            $content .= '<table cellpadding="0" cellspacing="1" class="table table-sm table-striped bordered2 table-hover table-bordered table_mob_turn border-light-subtle text-center w-100 mx-auto teamleaguetable" border="0">';
            $content .= '<thead class="th_color_rose"><tr class="th_players_mob">';
            $content .= '<th style="width:40px">№</th>';
            $content .= '<th style="width:55%">Команда</th>';
            $content .= '<th style="width:60px">Перемог</th>';
            $content .= '<th style="width:60px">Поразок</th>';
            $content .= '<th style="width:70px">Сети</th>';
            $content .= '<th style="width:60px">Очки</th>';
            $content .= '</tr></thead><tbody>';

            $num = 1;
            if (!empty($groups[$g])) {
                foreach ($groups[$g] as $row) {
                    $team_name = !empty($row['team_name']) ? $row['team_name'] : '';
                    $team_id = !empty($row['team_id']) ? (int)$row['team_id'] : 0;
                    $wins = isset($row['wins']) ? (int)$row['wins'] : 0;
                    $losses = isset($row['losses']) ? (int)$row['losses'] : 0;
                    $sets_win = isset($row['sets_win']) ? (int)$row['sets_win'] : 0;
                    $sets_lose = isset($row['sets_lose']) ? (int)$row['sets_lose'] : 0;
                    $points = isset($row['points_total']) ? (int)$row['points_total'] : 0;
                    $sets_ratio = $sets_win.':'.$sets_lose;
                    $team_name_html = htmlspecialchars((string)$team_name, ENT_QUOTES, 'UTF-8');
                    if ($team_id > 0) {
                        $team_name_html = '<span class="team-roster-link" data-team-id="'.$team_id.'" data-turnir-id="0" onclick="showTeamRoster(this, event)">'.$team_name_html.'</span>';
                    }
                    $content .= '<tr>';
                    $content .= '<td>'.$num.'</td>';
                    $content .= '<td class="text-start team-name">'.$team_name_html.'</td>';
                    $content .= '<td class="td_align_center">'.$wins.'</td>';
                    $content .= '<td class="td_align_center">'.$losses.'</td>';
                    $content .= '<td class="td_align_center">'.$sets_ratio.'</td>';
                    $content .= '<td class="td_align_center">'.$points.'</td>';
                    $content .= '</tr>';
                    $num++;
                }
            } else {
                $content .= '<tr><td colspan="6" class="text-center">Немає команд у цій лізі</td></tr>';
            }

            $content .= '</tbody></table>';
        }
        $content .= '</div>';

        $this->content = $content;
    }

    function getContent()
    {
        return $this->content;
    }
    function getSubMneu()
    {
        return $this->subMenu;
    }
    function getJavaScript()
    {
        return $this->Java_script;
    }
}

?>
