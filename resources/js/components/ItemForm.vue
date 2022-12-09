<template>
  <form class="" @submit.prevent="handleSaveForm">
    <div class="p-3 flex-1">
      <!-- <input class="block w-full px-2 py-1 text-lg border border-gray-200 shadow-inner rounded" type="text"
        placeholder="Enter a title" v-model.trim="item.title" ref="title" /> -->
      <textarea
        class="mt-3 px-2 block w-full py-1 tw-shadow-inner text-sm rounded bg-gray-100 dark:bg-gray-900 focus:tw-ring-1 focus:tw-ring-offset-0 tw-ring-gray-500 dark:tw-outline-0"
        placeholder="Add a description (optional)" v-model.trim="item.title" ref="title"></textarea>
      <div v-show="errorMessage">
        <span class="text-xs text-red-500">
          {{ errorMessage }}
        </span>
      </div>
    </div>
    <div class="p-3 flex justify-between items-end text-sm bg-gray-100 dark:bg-gray-700">
      <button @click="handleCancelButton" type="reset" class="py-1 leading-5 text-gray-600 dark:tw-text-gray-400">
        Cancel
      </button>
      <button type="submit" class="px-3 py-1 leading-5 text-gray-600 dark:tw-text-gray-400 rounded">
        {{ label }}
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    label: String,
    columnId: Number,
    myItem: {
      type: Object,
      default(rawProps) {
        return {
          title: "",
          description: "",
          kanban_column_id: null
        }
      }
    },
  },
  data() {
    return {
      errorMessage: "",
      item: Object.assign({}, this.myItem),
    };
  },
  mounted() {
    this.item.kanban_column_id = this.columnId;
    this.focusInput()
  },
  methods: {
    focusInput() {
      this.$refs.title.focus();
    },
    handleSaveForm() {

      this.$emit("form-saved", this.item);
      // Basic validation so we don't send an empty task to the server
      if (!this.item.title) {
        this.errorMessage = "The title field is required";
        return;
      }

    },
    handleCancelButton() {
      // this.item = this.myItem
      this.$emit("task-canceled");
      this.item.title = this.myItem.title
      console.log(this.myItem)
    }
  }
};
</script>