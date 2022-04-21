<?php

try {
    
    $result = array();
        
    if (empty($_REQUEST["type"])){
        
        $result['success'] = false;
        $result['msg']     = "Falta uma parâmetro na requisição.";
        
    } else {

        $_POST      = json_decode(file_get_contents("php://input"), true);
        $postFields = $_POST;

        $username = "root";
        $password = "";

        $pdo = new PDO("mysql:host=localhost;dbname=trello_clone", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        switch($_REQUEST["type"]){
            
            case "sort_boards":
                
                $boards     = [];

                try {

                    foreach ($postFields as $key => $field) {
                        $boards[$key] = $field;
                    }
                    
                    foreach ($boards['boards'] as $board) {
                        $stmt = $pdo->prepare('UPDATE boards SET board_order=:board_order WHERE id=:id');
                        $stmt->execute([
                            'board_order' => (int) $board['board_order'],
                            'id' => $board['id']
                        ]);
                    }

                } catch (\PDOException $e) {
                    $result['success'] = false;
                    $result['msg'] = "Erro ao atualizar ordem dos quadros.";
                    $result['err'] = $e->getMessage();
                }

            break;

            case "create_board":

                try {

                    $getMaxTaskOrder = $pdo->query("SELECT MAX(board_order) max_board_order, MAX(id) max_id FROM `boards`")->fetch();
                    $maxId = (int) $getMaxTaskOrder['max_id'] + 1;
                    $maxOrder = (int) $getMaxTaskOrder['max_board_order'] + 1;
                    $boardRef = "_newboard$maxId";
                    $boardTitle = "(Novo Board)";
                    $class = "default";

                    $stmt = $pdo->prepare('INSERT INTO boards (ref, title, class, board_order) VALUES(:ref, :title, :class,  :board_order)');
                    $stmt->execute([
                        'ref' => $boardRef,
                        'title' => $boardTitle,
                        'class' => $class,
                        'board_order' => $maxOrder,
                    ]);

                    $getId = $pdo->query("SELECT id FROM boards WHERE ref = '$boardRef'")->fetch();

                    $result['board_id'] = (int) $getId['id'];
                    $result['board_title'] = $boardTitle;

                } catch (\PDOException $e) {
                    $result['success'] = false;
                    $result['msg']     = "Houve um erro ao tentar salvar a informação no banco de dados";
                    $result['err']     = $e->getMessage();
                }

            break;

            case "read_boards":

                $getBoards = $pdo->query("SELECT * FROM `boards` ORDER BY board_order ASC;");
                $boards = [];
                
                while ($row = $getBoards->fetch(PDO::FETCH_ASSOC)) {
                    array_push($boards, $row);
                }
                
                $result = $boards;

            break;

            case "update_board":

                try {

                    $title  = $postFields['board_title'];
                    $ref    = $postFields['board_ref'];
                    $stmt   = $pdo->prepare('UPDATE boards SET title=:title WHERE ref=:ref');
                    $stmt->execute([
                        'ref' => $ref,
                        'title' => $title
                    ]);

                    $result['success']  = true;
                    $result['msg']      = "Quadro atualizado com sucesso";

                } catch (\PDOException $e) {
                    $result['success']  = false;
                    $result['msg']      = "Erro ao atualizar tarefa.";
                    $result['err']      = $e->getMessage();
                }

            break;

            case "delete_board":
            break;

            case "create_task":

                try {

                    $getMaxTaskOrder = $pdo->query("SELECT MAX(task_order) max_task_order, MAX(id) max_id FROM `tasks` WHERE board_id = " . $_POST['board_id'])->fetch();
                    $taskTitle       = $postFields['title'];

                    $stmt = $pdo->prepare('INSERT INTO tasks (board_id, title, task_order) VALUES(:board_id, :title, :task_order)');
                    $stmt->execute([
                        'board_id'   => $postFields['board_id'],
                        'title'      => $taskTitle,
                        'task_order' => (int) $getMaxTaskOrder['max_task_order'] + 1
                    ]);

                    $id = $pdo->lastInsertId();

                    $result['task_id'] = $id;
                    $result['task_title'] = $taskTitle;

                } catch (\Throwable $e) {
                    $result['success'] = false;
                    $result['msg'] = "Erro ao criar tarefa.";
                    $result['err'] = $e->getMessage();
                }
                
            break;

            case "read_tasks":

                $getTasks = $pdo->query("SELECT * FROM `tasks` ORDER BY tasks.task_order;");
                $tasks = [];
                while ($row = $getTasks->fetch(PDO::FETCH_ASSOC)) {
                    array_push($tasks, $row);
                }
                $result = $tasks;

            break;

            case "update_task":

                $tasks = [];

                try {

                    foreach ($postFields as $key => $item) {
                        $tasks[$key] = $item;
                    }

                    foreach ($tasks['tasks'] as $task) {
                        $stmt = $pdo->prepare('UPDATE tasks SET board_id=:board_id, task_order=:task_order WHERE id=:id');
                        $stmt->execute([
                            'board_id' => $task['board_id'],
                            'task_order' => $task['task_order'] == 0 ?  (int) $task['task_order'] + 1 : (int) $task['task_order'] + 1,
                            'id' => $task['id']
                        ]);
                    }
                    
                    $result['success'] = true;
                    $result['msg'] = "Tarefa atualizada com sucesso";

                } catch (\Throwable $e) {
                    $result['success'] = false;
                    $result['msg'] = "Erro ao atualizar tarefa.";
                    $result['err'] = $e->getMessage();
                }
            break;

            case "delete_task":

                try {

                    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
                    $stmt->execute([
                        'id' => $_REQUEST['task_id']
                    ]);

                    $result['success'] = true;
                    $result['msg'] = "Tarefa excluída com sucesso";

                } catch (\PDOException $e) {
                    $result['success'] = false;
                    $result['msg'] = "Erro ao excluir tarefa.";
                    $result['err'] = $e->getMessage();
                }

            break;

        }
        
    }

    header("Content-Type: application/json;");
    echo json_encode($result);

} catch(\Exception $e){
    $result['success'] = false;
    $result['msg']     = "Houve um erro generalizado";
    $result['err']     = $e->getMessage();
}