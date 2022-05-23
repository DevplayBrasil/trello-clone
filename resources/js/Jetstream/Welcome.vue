
<script>
import Draggable from 'vuedraggable';
import TaskCard from "@/Components/TaskCard.vue";
import axios from 'axios';

export default {
    name: "App",

    components: {
        TaskCard,
        Draggable
    },

    data() {
        return {

            boards: [],

            drag: false,
        };
    },

    methods: {
        getBoards () {
            axios.get("/read-boards").then((response) => {
                this.boards = response.data;
            });
        }
    },

    mounted: function(){
        this.getBoards();        
    },
    

};

</script>

<template>
    <div class="flex justify-center">
        <div class="flex min-h-screen px-4 py-12 overflow-x-scroll">
            <div v-for="board in boards" :key="board.title" class="px-3 py-3 mr-4 bg-gray-100 rounded column-width">

                <p class="font-sans text-sm font-semibold tracking-wide text-gray-700">{{ board.title }}</p>

                <draggable v-model="board.tasks" group="tasks" :animation="200" ghost-class="ghost-card"
                    @start="drag = true" @end="drag = false">
                    
                    <template #item="{ element }">
                        <task-card :task="element" :key="element.id" class="mt-3 cursor-move">
                        </task-card>
                    </template>
                    
                </draggable>
                
            </div>
        </div>
    </div>
</template>


<style scoped>
.column-width {
    min-width: 320px;
    width: 320px;
}

/* Unfortunately @apply cannot be setup in codesandbox, 
but you'd use "@apply border opacity-50 border-blue-500 bg-gray-200" here */
.ghost-card {
    opacity: 0.5;
    background: #F7FAFC;
    border: 1px solid #4299e1;
}
</style>