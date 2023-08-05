<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function getGameTurns(Request $request)
    {
        $numberOfPlayers = $request->input('players', 3);
        $numberOfTurns = $request->input('turns', 3);
        $startingPlayer = strtoupper($request->input('starting_player', 'A'));

        if (!ctype_alpha($startingPlayer) || strlen($startingPlayer) !== 1) {
            return response()->json(['error' => 'Invalid starting player, Use a single uppercase letter A to Z.'], 400);
        }

        if ($numberOfPlayers > 26) {
            return response()->json(['error' => 'Number of players should not be more than 26.'], 400);
        }

        $alphabet = range('A', 'Z');
        $startIndex = array_search($startingPlayer, $alphabet);
        $turns = [];
        $direction = 1;
        $reset = 0;

        for ($turn = 0; $turn < $numberOfTurns; $turn++) {
            $currentTurn = [];
            if($turn == ($numberOfPlayers*2) && $direction == 1){
                $reset = 1;
                break;
            }
            for ($i = 0; $i < $numberOfPlayers; $i++) {
                $playerIndex = ($startIndex + $i * $direction) % $numberOfPlayers;
                if ($playerIndex < 0) {
                    $playerIndex += $numberOfPlayers;
                }
                $currentTurn[] = $alphabet[$playerIndex];
            }

            $turns[] = $currentTurn;

            // Update the start index for the next turn
            if($turn == ($numberOfPlayers -1)){
                $startIndex = array_search($turns[$numberOfPlayers -1][0], $alphabet);
            }elseif($direction == -1 && $turn !== ($numberOfPlayers -1)){
                if($numberOfPlayers == ($startIndex +1)){
                    $startIndex = 0;
                }else{
                    $startIndex += 1;
                }
            }else{
                $startIndex = ($startIndex + $direction) % $numberOfPlayers;
            }
            if ($startIndex < 0) {
                $startIndex += $numberOfPlayers;
            }

            // Check if we need to start reversing the order of turns
            if (($turn + 1) % $numberOfPlayers === 0) {
                $direction *= -1;
            }
        }
        //repeat turns if group of normal and reversed turns done and still required more turns
        if($reset){
            $count = $numberOfTurns - count($turns);
            for($i= 0; $i < $count; $i++){
                $turns[] = $turns[$i];
            }
        }
        return response()->json($turns);
    }
}