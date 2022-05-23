let boards = [];
let tasks = [];
let formattedBoard = [];
const boardsIds = {};
const boardObjs = [];

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
})

function makeBoardTitle(title, id){
    return `<input type='text' value='${title}' class='inputEdit' id='input${id}' data-id='${id}' /> <span id='title${id}' class='boardTitle' data-id='${id}'>${title}</span> <i class='bi-pencil btnRight btnEditTitle' data-id='${id}'></i> <br style='clear:both' />`;
}

function makeTaskTitle(title, id){
  return `${title} <i class='bi-trash btnRight btnRemoveTask' data-id='${id}'></i>`;
}

function buildRemoveTask(){
  let btnsRemoveTask = document.getElementsByClassName("btnRemoveTask");
  for (let index = 0; index < btnsRemoveTask.length; index++) {
    const btnRemoveTask = btnsRemoveTask[index];

    btnRemoveTask.addEventListener("click", function(){
      Swal.fire({
          title: 'Você tem certeza?',
          text: "Está ação é irreversível!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sim, Remover',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
            if (result.isConfirmed) {
              const dataId = this.getAttribute('data-id');
              deleteTask(dataId);
            }
        })

    })
  }
}

var Kanban = new jKanban({
    element: "#trello",
    gutter: "10px",
    widthBoard: "450px",
    itemHandleOptions:{
      enabled: true,
    },

    dragendBoard: function(el){

      let getAllBoards = document.querySelectorAll(".kanban-board");
      let sorted = [];
      getAllBoards.forEach(function (value, index, array) {
          sorted.push({
              id: boardsIds[value.getAttribute('data-id')],
              board_order: value.getAttribute('data-order')
          })
      });

      sortBoards(sorted);
      
    },

    dropEl: function(el, target, source, sibling){

      const sorted      = [];
      const nodes       = Kanban.getBoardElements(target.parentElement.getAttribute('data-id'));
      let currentOrder  = 0;

      nodes.forEach(function (value, index, array) {
        sorted.push({
            "id": value.getAttribute("data-eid"),
            "task_order": currentOrder++,
            "board_id": boardsIds[target.parentElement.getAttribute('data-id')],
        })
      });

      updateTask(sorted);

    },

    buttonClick: function(el, boardRef) {

      var formItem = document.createElement("form");
      formItem.setAttribute("class", "itemform");
      formItem.innerHTML = '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Salvar</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancelar</button></div>';

      Kanban.addForm(boardRef, formItem);

      formItem.addEventListener("submit", function(e) {

        e.preventDefault();
        var text = e.target[0].value;

        const createTask = async () => {
            try {

              var params = { board_id: boardsIds[boardRef], title: text }

              await axios.post('functions/index.php', params, { params: { type: "create_task" }}, { headers: { 'Content-Type': 'application/json' }})
                .then(function (response) {

                  Kanban.addElement(boardRef, {
                      "id": response.data.task_id,
                      "title": makeTaskTitle(response.data.task_title, response.data.task_id),
                      "class": ['task'],
                      "task-title": response.data.task_title
                  });
                  
                  buildRemoveTask();
                  formItem.parentNode.removeChild(formItem);

                });
            } catch (err) {
            console.error(err)
            }
        }

        createTask();
        
        
      });

      document.getElementById("CancelBtn").addEventListener("click", function(){
        if (formItem)  formItem.parentNode.removeChild(formItem);
      })

    },

    itemAddOptions: {
      enabled: true,
      content: '+ Add New Card',
      class: 'custom-button',
      footer: true
    },
    boards: [
      
    ]
});

const sortBoards = async (sorted) => {
  
  try {

    const params = { boards: sorted }
    await axios.post("functions/index.php", params, { params: { type: "sort_boards" }}, { headers: { 'Content-Type': 'application/json'}});

  } catch (error){
    console.log(err)
  }

}

const updateBoard = async (boardId, boardTitle) => {

  try {

    const params = {
      board_ref: boardId,
      board_title: boardTitle
    }

    await axios.post('functions/index.php', params, { params: { type: "update_board" }}, { headers: { 'Content-Type': 'application/json' }});


  } catch (error) {
    console.log(err)
  }

}

const updateTask = async (payload) => {
    
  try {
    var params = { tasks: payload }
    await axios.post('functions/index.php', params, { params: { type: "update_task" }, }, { headers: { 'Content-Type': 'application/json' } });
  } catch (err) {
    console.error(err)
  }
  
}

const getBoards = async () => {
  try {
      await axios.get('functions/index.php', { params: { type: "read_boards" }, }, { headers: { 'Content-Type': 'application/json'}})
        .then(function (response) {
            boards = response.data;

            if (boards) {
                            
                for (const board in boards) {
                    let ref             = boards[board].ref
                    let boardId         = boards[board].id
                    boardsIds[ref]      = boardId;                    
                    boardsIds[boardId]  = ref;

                    Kanban.addBoards([{
                      id: boards[board].ref,
                      title: makeBoardTitle(boards[board].title, boards[board].ref),
                      class: boards[board].class,
                      dragTo: boards[board].dragTo,
                      item: []
                    }]);

                    buildEditTitle();                  
                }

                getTasks();
            }
        });
  } catch (err) {
      console.error(err)
  }
}

getBoards();

const searchInput = document.getElementById("search");

var delayTimer;

searchInput.addEventListener("input", function(){

  const searchTerm = this.value;
  const getTasks = document.getElementsByClassName("task")

  delayTimer = setTimeout(function() {

    for (let i = 0; i < getTasks.length; i++) {        
      const taskTitle = getTasks.item(i).getAttribute("data-task-title");
      getTasks[i].style.display = "none";

      if (searchTerm != "" && (taskTitle.toLowerCase()).includes(searchTerm.toLowerCase())) {
        getTasks[i].style.display = "block"
      }
      
      if (searchTerm == "") {
        getTasks[i].style.display = "block"
      }
    }
    
  }, 500)
})

const getTasks = async () => {
    try {
        await axios.get('functions/index.php', { params: { type: "read_tasks" }, }, { headers: { 'Content-Type': 'application/json' } })
          .then(function (response) {
              tasks = response.data;

              for (const task in tasks) {
                let boardId = tasks[task].board_id;
                
                Kanban.addElement(boardsIds[boardId], {
                    "id": tasks[task].id,
                    "title": makeTaskTitle(tasks[task].title, tasks[task].id),
                    "class": ["task"],
                    "task-title": tasks[task].title
                });

                buildRemoveTask();
              }
          })
    } catch (err) {
        console.error(err)
    }
}

const deleteTask = async (taskId) => {

  try {

    await axios.get("functions/index.php", { params: { type: "delete_task", task_id: taskId}}, { headers: { 'Content-Type': 'application/json' }})
      .then(function (response){
        
        if (response.data.success == true) {

          Toast.fire({ icon: 'success', title: 'Tarefa apagada com sucesso!' });
          Kanban.removeElement(taskId);
          
        } else {
          Toast.fire({  icon: 'error', title: 'Aconteceu um erro ao atualizar a tarefa!' });
        }

      })
      .catch(function(){
        Toast.fire({  icon: 'error', title: 'Aconteceu um erro na comunicação com a base de dados!' });
      })

  }catch(erro){
    console.log(error);
  }
}

var addBoardDefault = document.getElementById("addDefault");
addBoardDefault.addEventListener("click", function() {

  const createBoard = async () => {
    try {

      await axios.post("functions/index.php", null, { params: { type: "create_board" }}, { headers: { 'Content-type': 'application/json'}})

    } catch (err) {

    }
  }

  createBoard();
});

function buildEditTitle(){
  var btnsEditTitle = document.getElementsByClassName('btnEditTitle');
  for (let index = 0; index < btnsEditTitle.length; index++) {
      const btnEditTitle = btnsEditTitle[index];

      btnEditTitle.addEventListener("click", function() {
      const dataId = this.getAttribute('data-id');
      document.getElementById('title'+dataId).style.display = 'none';
      document.getElementById('input'+dataId).style.display = 'block';
      document.getElementById('input'+dataId).focus();
      });    
      
  }

  var elements = document.getElementsByClassName("inputEdit");

  var switchField = function () {
      const dataRef = this.getAttribute('data-id');
      const newTitle = this.value;
      document.getElementById('title' + dataRef).innerHTML = newTitle;
      document.getElementById('title' + dataRef).style.display = 'block';
      document.getElementById('input' + dataRef).style.display = 'none';

      updateBoard(dataRef, newTitle);
  };

  for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('blur', switchField, false);
  }

}

  