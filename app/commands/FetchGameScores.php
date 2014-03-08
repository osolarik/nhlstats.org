<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

class FetchGameScores extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nhl:fetch-scores';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch game scores from espn.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$games = $this->getScoresArray();
		$this->saveGameScores($games);
	}

	private function getScoresArray()
	{
		$client = Goutte::getNewClient();
		$dateESPN = Carbon::parse($this->argument('date'))->format('Ymd');

		$gameDayURL = "http://scores.espn.go.com/nhl/scoreboard?date=$dateESPN";
		$crawler = $client->request('GET', $gameDayURL);
		$lines = $crawler->filter('.game-details tr');

		$line_cells = [];
		$lines->each(function ($line) use (&$line_cells) {
			$line_cells[] = $line->filter('td')->extract(array('_text'));
		});

		$noGame = 0;
		$home = false;
		$games = [];

		foreach ($line_cells as $cells) {
			//Header line of a new game
			if (empty($cells[0])) {
				$noGame++;
				$home = true;
			} else {
				if ($home) {
					$games[$noGame]['team1']     = $cells[0];
					$games[$noGame]['score1_1']  = $cells[1];
					$games[$noGame]['score1_2']  = $cells[2];
					$games[$noGame]['score1_3']  = $cells[3];
					$games[$noGame]['score1_OT'] = $cells[4];
					$games[$noGame]['score1_T']  = $cells[5];
				} else {
					$games[$noGame]['team2']     = $cells[0];
					$games[$noGame]['score2_1']  = $cells[1];
					$games[$noGame]['score2_2']  = $cells[2];
					$games[$noGame]['score2_3']  = $cells[3];
					$games[$noGame]['score2_OT'] = $cells[4];
					$games[$noGame]['score2_T']  = $cells[5];
				}
				$home = false;
			}
		}
		return $games;
	}

	private function saveGameScores($games)
	{
		$dateFetched = $this->argument('date');
		foreach ($games as $game)
		{
			$team1_id = Team::whereRaw("CONCAT(city, ' ', name) = '{$game['team1']}'")->pluck('id');
			$team2_id = Team::whereRaw("CONCAT(city, ' ', name) = '{$game['team2']}'")->pluck('id');

			$gameDB = GameScores::firstOrNew([
				'date_game'  => $dateFetched,
				'team1_id'   => $team1_id,
				'team2_id'   => $team2_id,
			]);
			// var_dump($game);
			$gameDB->score1_1  = $game['score1_1'];
			$gameDB->score1_2  = $game['score1_2'];
			$gameDB->score1_3  = $game['score1_3'];
			$gameDB->score1_OT = $game['score1_OT'];
			// $gameDB->score1_SO = $game['score1_SO'];
			$gameDB->score1_T  = $game['score1_T'];

			$gameDB->score2_1  = $game['score2_1'];
			$gameDB->score2_2  = $game['score2_2'];
			$gameDB->score2_3  = $game['score2_3'];
			$gameDB->score2_OT = $game['score2_OT'];
			// $gameDB->score2_SO = $game['score2_SO'];
			$gameDB->score2_T  = $game['score2_T'];

			$gameDB->year      = '1314';
			$gameDB->save();
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('date', InputArgument::OPTIONAL, 'Date to fetch scores in format Ymd.',
				Carbon::today()->format("Y-m-d")),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		// 	array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}
}
