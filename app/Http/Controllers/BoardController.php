<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Http\Request;
use PDOException;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [];
        $boards = Board::orderBy("board_order")->get();
        foreach ($boards as $key => $board) {
            $result[$key]['id'] = $board->id;
            $result[$key]['title'] = $board->title;
            $result[$key]['tasks'] = $board->tasks;
        }

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $maxID = (int) Board::max('id') + 1;
            $maxOrder = (int) Board::max('board_order') + 1;

            $board = new Board;
            $board->ref = "_newboard$maxID";
            $board->title = "(Novo Board)";
            $board->board_order = $maxOrder;

            $board->save();
        } catch (PDOException $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function edit(Board $board)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Board $board)
    {

        $tasks = $request->all()['board'][0]['tasks'];
        
        if (empty($tasks) || empty($board)) return response()->json(['error' => 'Um ou mais parâmetros obrigatórios estão faltando. Atualize a página e tente novamente'], 400);

        try{            
            foreach ($tasks as $key => $item) {
                $task = Task::findOrFail($item['id']);
                $task->board_id = $board->id;
                $task->task_order = $key;
                $task->save();
            }
        }catch(PDOException $e){
            return response()->json(['error' => "Não foi possível atualizar o board. Erro: $e->getMessage()"], 400);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function destroy(Board $board)
    {
    }
}
