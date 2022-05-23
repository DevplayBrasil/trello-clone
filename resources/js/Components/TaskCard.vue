<template>
  <div class="px-3 pt-3 pb-5 bg-white border border-white rounded shadow">
    <div class="flex justify-between">
      <p class="font-sans text-sm font-semibold tracking-wide text-gray-700">{{task.title}}</p>

      <img
        class="w-6 h-6 ml-3 rounded-full"
        src="/img/default.png"
        alt="Avatar"
      >
    </div>
    <div class="flex items-center justify-between mt-4">
      <span class="text-sm text-gray-600">{{task.created_at}}</span>
      <badge v-if="task.type" :color="badgeColor">{{task.type}}</badge>
    </div>
  </div>
</template>
<script>
import Badge from "./Badge.vue";
export default {
  components: {
    Badge
  },
  props: {
    task: {
      type: Object
    }
  },
  computed: {
    badgeColor() {
      const mappings = {
        Design: "purple",
        "Feature Request": "teal",
        Backend: "blue",
        QA: "green",
        default: "teal"
      };
      return mappings[this.task.type] || mappings.default;
    }
  }
};
</script>
