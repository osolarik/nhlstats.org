<?php

use Nhlstats\Repositories\TeamRepository as Team;

class PlayerController extends BaseController {

	public function __construct(Team $team)
	{
		$this->team = $team;
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
		if (!isset($all_counts[$data['count']]))
		{
			$data['count'] = head($all_counts);
		}

		/* ----------- TEAMS ----------- */
		$data['all_teams'] = $this->team->getWithShortNameAndCity();

		//Default to first team if invalid is passed
		if (!isset($data['all_teams'][$data['team']]))
		{
			$data['team'] = key($data['all_teams']);
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
		if (!isset($positions[$data['position']]))
		{
			$data['position'] = head($positions);
		}

		/* -------- PLAYER STATS -------- */
		$playersStatsYear = PlayersStatsYear::take(50)->with('player.team.division')->get();
		$data['playersStatsYear'] = $playersStatsYear;
		// var_dump($data);

		return View::make('players', $data);
	}
}