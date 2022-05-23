<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import JetButton from '@/Jetstream/Button.vue';
import Welcome from '@/Jetstream/Welcome.vue';
</script>

<script>
export default {
    methods: {
        createBoard() {
            axios.post("/create-board").then((response) => {
                this.getBoards();
            });
        },
        getBoards(){
            this.$refs.welcome.getBoards();
        },
    },
};
</script>

<template>
    <AppLayout title="Página Inicial">
        <template #header>
            <div class="grid grid-cols-6 gap-4">

                <div class="col-start-1 col-end-3">
                    <JetButton type="button" @click="createBoard">
                        + Novo Board
                    </JetButton>
                </div>

                <div class="col-span-2 col-end-7 ">
                    <form class="flex items-center">
                        <label for="simple-search" class="sr-only">Pesquisar Tarefa ...</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input type="text" id="simple-search"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Pesquisar Tarefa ..." required>
                        </div>
                    </form>
                </div>

            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <Welcome ref="welcome" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
