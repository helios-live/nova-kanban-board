<template>
  <div>

    <Head :title="board.title" />

    <Heading class="tw-pl-8 tw-pt-6">
      {{ board.title }}

      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" @click="syncUpstream"
        class="tw-inline-block dark:tw-fill-gray-500 tw-fill-gray-400  dark:hover:tw-fill-gray-400 tw-cursor-pointer">
        <path class="heroicon-ui"
          d="M6 18.7V21a1 1 0 0 1-2 0v-5a1 1 0 0 1 1-1h5a1 1 0 1 1 0 2H7.1A7 7 0 0 0 19 12a1 1 0 1 1 2 0 9 9 0 0 1-15 6.7zM18 5.3V3a1 1 0 0 1 2 0v5a1 1 0 0 1-1 1h-5a1 1 0 0 1 0-2h2.9A7 7 0 0 0 5 12a1 1 0 1 1-2 0 9 9 0 0 1 15-6.7z" />
      </svg>
    </Heading>
    <Heading v-if="board.model" class="tw-pl-8 tw-text-xs">{{ board.model.name }} {{ board.model.target_column_attribute
    }}
    </Heading>

    <kanban-board :initial-data="initialData" :board-id="board.id" ref="kb"
      :base="this.basepath + 'resources/' + this.resource" />
  </div>
</template>

<script>
import KanbanBoard from "../components/KanbanBoard.vue"; // import the component
export default {
  props: {
    board: Object,
    initialData: {
      type: String,
      required: true,
    },
    basepath: String,
    resource: String,
  },
  components: { KanbanBoard }, // register component
  mounted() {
  },
  beforeMount() {
    this.board.model = JSON.parse(this.board.model)
    document.getElementById("app").setAttribute("data-app", "NovaKanban")
  },
  beforeUnmount() {

    console.log("unmounted")
    document.getElementById("app").setAttribute("data-app", "")
  },
  methods: {
    syncUpstream() {
      this.$refs.kb.syncUpstream()
    }
  },
  data() {
    return {

    }
  }
}
</script>

<style>
/* Scoped Styles */
</style>
