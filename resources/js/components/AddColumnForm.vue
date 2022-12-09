<template>

  <div class="tw-w-80 flex-shrink-0 tw-pr-6">
    <div v-if="!newColumnOpen" class="w-full max-w-xs rounded-lg shadow overflow-hidden opacity-50 hover:opacity-full">
      <!-- Columns -->
      <div class="p-3 flex justify-between items-center bg-white dark:bg-gray-800 ">
        <h4 class="mr-3 leading-tight text-sm font-bold">
          Title
        </h4>
        <button class="py-1 px-2 text-sm text-orange-500 hover:underline" @click="openAddColumnForm()">
          Add Column
        </button>
      </div>
      <div class="p-2 flex-1 flex flex-col h-full overflow-x-hidden overflow-y-auto bg-white dark:bg-gray-800">
      </div>
    </div>
    <div v-if="newColumnOpen" class="w-full max-w-xs rounded-lg shadow overflow-hidden">
      <!-- Columns -->
      <div class="p-3 flex justify-between items-center bg-white dark:bg-gray-800">
        <form class="flex w-full" @submit.prevent="handleAddNewColumn">
          <input class="flex-grow w-full px-2 py-1 text-lg border-b border-blue-800 rounded" type="text"
            placeholder="Title" v-model.trim="newColumn.title" ref="title" />

          <input class="block w-full px-2 py-1 text-lg border border-gray-200 shadow-inner rounded" type="text"
            placeholder="Value" v-model.trim="newColumn.target_property_value" ref="targetValue" />

          <button class="py-1 px-2 text-sm text-orange-500 hover:underline" type="submit">
            Add
          </button>
        </form>
      </div>
      <div class="p-2 flex-1 flex flex-col h-full overflow-x-hidden overflow-y-auto bg-white dark:bg-gray-800">
      </div>
    </div>
  </div>
</template>


<script>
import { nextTick } from 'process';

export default {
  props: {
    statusId: Number,
    boardId: Number,
  },
  data() {
    return {
      newColumnOpen: false,
      newColumn: {
        title: "",
        target_property_value: "",
        order: null
      },
    }
  },
  methods: {
    mounted() {
      this.focusInput();
    },
    focusInput() {
      this.$refs.title.focus();
      nextTick(() => {
        this.$refs.title.value = '';
        this.$refs.targetValue.value = '';
        this.newColumn.title = '';
        this.newColumn.target_property_value = '';
      });
    },
    openAddColumnForm() {
      this.newColumnOpen = true;
      nextTick(() => {
        this.focusInput();
      });
    },

    closeAddColumnForm() {
      this.newColumnOpen = false;
    },

    handleAddNewColumn() {
      // Basic validation so we don't send an empty task to the server
      if (!this.newColumn.title) {
        this.errorMessage = "The title field is required";
        return;
      }

      // Send new task to server
      axios
        .post("/nova-vendor/nova-kanban/board/" + this.boardId + "/columns", this.newColumn)
        .then(res => {
          // Tell the parent component we've added a new task and include it
          this.$emit("column-added", res.data);
          this.closeAddColumnForm()
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    },
    handleErrors(err) {
      if (err.response && err.response.status === 422) {
        // We have a validation error
        const errorBag = err.response.data.errors;
        if (errorBag.title) {
          this.errorMessage = errorBag.title[0];
        } else if (errorBag.description) {
          this.errorMessage = errorBag.description[0];
        } else {
          this.errorMessage = err.response.message;
        }
      } else {
        // We have bigger problems
        console.log(err.response);
      }
    }
  }
}
</script>