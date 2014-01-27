<?php

use Nhlstats\Repositories\TeamRepository as Team;

class PlayerController extends BaseController {

	public function __construct(
		Team $team,
		PlayersStatsYear $players_stats_year,
		PlayersStatsDays $players_stats_day
	) {
		$this->team = $team;
		$this->players_stats_year = $players_stats_year;
		$this->players_stats_day  = $players_stats_day;
	}

	public function getListFiltered()
	{
		$data = Input::all();

		/* ----------- COUNTS ----------- */
		$all_counts = [
			'50'  => '50',
			'100' => '100',
			'500' => '500',
			'All' => 'All'
		];
		$data['all_counts'] = $all_counts;

		//Default to 50 if not a possible count
		if (!isset($data['count']) || !isset($all_counts[$data['count']]))
		{
			$data['count'] = head($all_counts);
		}

		/* ----------- TEAMS ----------- */
		$data['all_teams'] = ['all' => '---------------'] + $this->team->getWithShortNameAndCity();

		//Default to first team if invalid is passed
		if (!isset($data['team']) || !isset($data['all_teams'][$data['team']]))
		{
			$data['team'] = 'all';
		}

		/* ---------- POSITION ---------- */
		$positions = [
			'All' => 'All',
			'F'   => 'Forward',
			'L'   => 'Left',
			'C'   => 'Center',
			'R'   => 'Right',
			'D'   => 'Defense'
		];
		$data['all_positions'] = $positions;
		if (!isset($data['position']) || !isset($positions[$data['position']]))
		{
			$data['position'] = head($positions);
		}

		/* -------- PLAYER STATS -------- */
		$filter['teams.short_name'] = ['=', $data['team']];
		$data['playersStatsYear'] = Cache::remember(
			"playersStatsYear-{$data['count']}-{$data['team']}",
			60,
			function() use ($data, $filter) {
				return $this->players_stats_year->topPlayersByPoints($data['count'], $filter);
			}
		);

		/* -------- PLAYER STATS BY DAY -------- */
		$data['playersStatsDay'] = Cache::remember(
			"playersStatsDay-{$data['team']}",
			60,
			function() use ($data, $filter) {
				return $this->players_stats_day->topPlayersByPoints($data['count'], $filter);
			}
		);

		// $data['playersStatsDay'] = $this->players_stats_day->topPlayersByPoints($data['count'], $filter);

		$data['asset_path'] = asset('');

		return View::make('players', $data);
	}
}