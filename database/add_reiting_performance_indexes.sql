-- Performance indexes for tournament games and team-league result pages.
-- Safe to run once; check existing index names before applying manually.

ALTER TABLE `bs_reiting`
  ADD INDEX `idx_reiting_turnir_etap_perenos` (`turnir_id`, `etap_id`, `perenos_etap`),
  ADD INDEX `idx_reiting_match_etap_pair` (`match_id`, `etap_id`, `pair_number`),
  ADD INDEX `idx_reiting_etap_group_pair1` (`etap_id`, `group_num`, `pl_num_grp1`),
  ADD INDEX `idx_reiting_etap_group_pair2` (`etap_id`, `group_num`, `pl_num_grp2`),
  ADD INDEX `idx_reiting_etap_olimp` (`etap_id`, `olimp16_num`),
  ADD INDEX `idx_reiting_player1_turnir` (`pl_id_1`, `turnir_id`),
  ADD INDEX `idx_reiting_player2_turnir` (`pl_id_2`, `turnir_id`);

ALTER TABLE `bs_etaps_players_mesta`
  ADD INDEX `idx_epm_turnir_etap_group_num` (`turnir_id`, `etap_id`, `groups`, `grp_num`),
  ADD INDEX `idx_epm_etap_player` (`etap_id`, `player_id`),
  ADD INDEX `idx_epm_pred` (`etap_id_pred`, `groups_pred`, `grp_num_pred`);

ALTER TABLE `bs_team_pairs`
  ADD INDEX `idx_team_pairs_match_etap_pair` (`match_id`, `etap_id`, `pair_number`);

ALTER TABLE `bs_team_lineups`
  ADD INDEX `idx_team_lineups_match_etap_team` (`match_id`, `etap_id`, `team_id`);
