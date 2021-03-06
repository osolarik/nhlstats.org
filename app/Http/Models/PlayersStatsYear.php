<?php

namespace Nhlstats\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlayersStatsYear extends Model
{
    protected $guarded = [];

    public static $rules = [];

    public function player()
    {
        return $this->belongsTo('Nhlstats\Http\Models\Player');
    }

    public static function topPlayersByPoints($count, $filters = [], $filtersRaw = [])
    {
        if ($count == 'All') {
            $count = -1;
        }
        $query = DB::table('players_stats_years')
                ->join('players', 'players.id', '=', 'players_stats_years.player_id')
                ->join('teams', 'teams.id', '=', 'players.team_id')
                ->join('divisions', 'divisions.id', '=', 'teams.division_id')
                ->take($count)
                ->select(
                    'players_stats_years.*',
                    'divisions.conference',
                    'teams.name as team_name',
                    'players.*',
                    'teams.short_name',
                    'teams.city'
                )
                ->orderBy('points', 'desc')
                ->orderBy('goals', 'desc')
                ->orderBy('games', 'asc')
                ->orderBy('plusminus', 'desc')
                ->orderBy('players.name', 'asc');

        self::buildtopPlayersByPointsFilter($query, $filters, $filtersRaw);

        return $query->get();
    }

    public static function buildtopPlayersByPointsFilter(&$query, $filters, $filtersRaw = [])
    {
        foreach ($filters as $field => $condition) {
            list($operator, $value) = $condition;
            if ($field === 'players.position' && $value === 'F') {
                $operator = '<>';
                $value = 'D';
            }
            if ($value != 'all') {
                $query = $query->where($field, $operator, $value);
            }
        }
        foreach ($filtersRaw as $filter) {
            $query = $query->whereRaw($filter);
        }
    }

    public static function pointsByPosition($filters = [])
    {
        $query = DB::table('players_stats_years')
                ->join('players', 'players.id', '=', 'players_stats_years.player_id')
                ->join('teams', 'teams.id', '=', 'players.team_id')
                ->select(DB::raw('SUM(players_stats_years.points) AS points'), 'players.position')
                // ->orderBy('players.name', 'asc');
                ->groupBy('players.position');
        foreach ($filters as $condition => $value) {
            $query = $query->where($condition, $value[0], $value[1]);
        }

        return $query->get();
    }
}
