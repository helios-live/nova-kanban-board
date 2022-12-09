<template>
  <div>
    <DeleteArea v-if="drag" @deleting-item="handleDeletingItem" />
    <div class="tw-w-full tw-overflow-auto tw-p-6">
      <draggable :sort="true" @start="drag = true" @end="handleColumnMoved" v-model="columns" item-key="id"
        class="relative tw-h-full tw-flex tw-items-start tw-flex-nowrap gap-6 z-10" ghost-class="opacity-40"
        handle=".handle">
        <template #item="{ index, element: column }">
          <div class="tw-w-80 rounded-lg shadow overflow-hidden flex-shrink-0">
            <!-- Columns (columns) -->
            <div v-if="columnEditId !== column.id"
              class="pt-2 px-3 py-1 flex justify-between tw-items-center bg-white dark:bg-gray-800 handle">
              <h4 class="tw-flex-grow mr-3 leading-tight text-sm font-bold" @dblclick="editColumn(column.id)">
                <div class="block">{{ column.title }}</div>
                <div class="bloc tw-text-xs tw-font-extralight">{{ column.target_property_value }}</div>
              </h4>
              <button class=" py-1 px-2 text-sm text-orange-500 hover:underline" @click="openItemForm(column.id)">
                Add
              </button>
            </div>
            <div v-if="columnEditId === column.id"
              class="p-3 flex justify-between items-center bg-white dark:bg-gray-800 handle">
              <input class="block w-full px-2 py-1 text-lg border border-gray-200 shadow-inner rounded" type="text"
                placeholder="Title" v-model.trim="column.title" ref="colTitle" />

              <input class="block w-full px-2 py-1 text-lg border border-gray-200 shadow-inner rounded" type="text"
                placeholder="Target" v-model.trim="column.target_property_value" ref="targetValue" />

              <button class="py-1 px-2 text-sm text-orange-500 hover:underline" @click="saveColumn(column.id)">
                Save
              </button>
            </div>
            <div class="p-2 flex-1 flex flex-col tw-h-full overflow-x-hidden overflow-y-auto bg-white dark:bg-gray-800">

              <!-- Items -->
              <draggable
                class="flex-1 min-h-[50px] divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700"
                v-model="column.items" v-bind="taskDragOptions" :move="fixFooter" @start="drag = true"
                @end="handleItemMoved" item-key="id" :component-data="{ name: column.title }" :sort="true">
                <template #item="{ element: item, index }">
                  <div class="tw-shadow-xs tw-shadow-gray-200 ">
                    <div v-if="(editItemId != item.id)" @dblclick="editItem(item.id)"
                      class="p-2 flex flex-col bg-white dark:bg-gray-800 rounded-md  transform cursor-pointer text-gray-900 dark:text-gray-400">
                      <span v-if="(editItemId != item.id)" class="block mb-2 text-xl ">
                        {{ item.title }}
                      </span>
                      <div v-if="(editItemId != item.id && item.target)" class="tw-text-xs">

                        <a :href="this.base + '/' + item.target['id']">{{ item.target['name'] }}</a>
                      </div>
                    </div>
                    <ItemForm v-if="(editItemId == item.id)" :my-item="item" v-on:item-added="handleItemAdded"
                      v-on:task-canceled="closeItemForms" v-on:form-saved="handleUpdateItem" :label="'Save'" />
                  </div>
                </template>
                <template #footer>
                  <div v-show="!column.items.length && newItemForColumn !== column.id"
                    class="flex-1 p-4 flex flex-col items-center justify-center">
                    <span class="text-gray-600">No items yet</span>
                    <button class="mt-1 text-sm text-orange-600 hover:underline" @click="openItemForm(column.id)">
                      Add one
                    </button>
                  </div>
                  <ItemForm v-if="newItemForColumn === column.id" :column-id="column.id"
                    v-on:task-canceled="closeItemForms" v-on:form-saved="handleAddItem" :label="'Add'" />
                </template>
              </draggable>
              <!-- ./Items -->

            </div>
          </div>
        </template>
        <template #footer>
          <AddColumnForm v-on:column-added="handleColumnAdded" :board-id="boardId" />
        </template>
      </draggable>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import AddColumnForm from "./AddColumnForm.vue"; // import the component
import DeleteArea from "./DeleteArea.vue"; // import the component
import ItemForm from "./ItemForm.vue"; // import the component

export default {
  components: { draggable, ItemForm, AddColumnForm, DeleteArea }, // register component
  props: {
    initialData: String,
    boardId: Number,
    base: String,
  },
  data() {
    return {
      columns: [],
      newItemForColumn: 0, // track the ID of the column we want to add to
      columnEditId: 0, // track the ID of the column we want to add to
      drag: false,
      editItemId: 0,
      componentKey: 0,
    };
  },
  computed: {
    taskDragOptions() {
      return {
        handle: "div",
        animation: 200,
        group: "task-list",
        ghostClass: "status-drag"
      };
    }
  },
  mounted() {
    // 'clone' the columns so we don't alter the prop when making changes
    console.log("asdf", this.initialData)
    this.columns = JSON.parse(JSON.stringify(this.initialData));
  },
  methods: {
    editItem(itemId) {
      this.editItemId = itemId
    },
    saveColumn(columnId) {
      const columnIndex = this.columns.findIndex(
        column => column.id === columnId
      );

      axios
        .put("/nova-vendor/nova-kanban/board/" + this.boardId + "/columns/" + columnId, this.columns[columnIndex])
        .then(res => {
          this.columnEditId = 0;
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },
    editColumn(columnId) {
      this.columnEditId = columnId
      this.$nextTick(() => {
        this.$refs.colTitle.focus();
      });
    },
    // set the columnId and trigger the form to show 
    openItemForm(columnId) {
      this.newItemForColumn = columnId;
    },
    // reset the columnId and close form
    closeItemForms() {
      this.newItemForColumn = 0;
      this.editItemId = 0;
    },
    handleUpdateItem(evt) {

      console.log('update-item', evt)

      axios
        .put("/nova-vendor/nova-kanban/board/" + this.boardId + "/items/" + evt.id, evt)
        .then(res => {
          this.handleItemUpdated(res.data)
          console.log('item-added 1', res.data)
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },
    handleItemUpdated(item) {
      console.log('item-updated', item)
      const columnIndex = this.columns.findIndex(
        column => column.id === item.kanban_column_id
      );

      const itemIndex = this.columns[columnIndex].items.findIndex(
        itm => itm.id === item.id
      );
      // Add newly created task to our column
      this.columns[columnIndex].items[itemIndex] = item

      // Reset and close the ItemForm
      this.closeItemForms();
    },
    handleAddItem(evt) {

      console.log('add-item', evt)

      axios
        .post("/nova-vendor/nova-kanban/board/" + this.boardId + "/items", evt)
        .then(res => {
          this.handleItemAdded(res.data)
          console.log('item-added 1', res.data)
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },
    handleItemAdded(newItem) {
      console.log('item-added 2', newItem)
      // add a task to the correct column in our list
      // Find the index of the column where we should add the task
      const columnIndex = this.columns.findIndex(
        column => column.id === newItem.kanban_column_id
      );

      // Add newly created task to our column
      this.columns[columnIndex].items.push(newItem);

      // Reset and close the ItemForm
      this.closeItemForms();
    },
    handleDeletingItem(item) {
      console.log('deleting-item', item)

      this.preventNextSync = true;
    },
    handleColumnAdded(column) {
      // add a task to the correct column in our list
      // Find the index of the column where we should add the task

      // Add newly created task to our column
      this.columns.push(column);

      // Reset and close the ItemForm
      this.closeItemForms();
    },
    handleItemMoved(evt) {
      console.log(evt)
      this.drag = false
      this.sync()
    },
    handleColumnMoved(evt) {
      console.log(evt)
      this.drag = false
      this.sync()
    },
    sync() {
      if (this.preventNextSync) {
        this.preventNextSync = false;
        console.log('one sync prevented')
        return;
      }
      // Send the entire list of columns to the server
      return axios.put("/nova-vendor/nova-kanban/board/" + this.boardId + "/sync", { columns: this.columns }).then((resp) => {

        this.columns = resp.data
        console.log('synced')
      }).catch(err => {
        console.log(err.response);
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
    },
    fixFooter(e) {
      if (e.relatedContext.list.length === e.draggedContext.futureIndex) {
        console.log("fix?", e.relatedContext.list.length, e.draggedContext.futureIndex)
        var newListDom = e.to

        var newListFooter = e.to.lastChild
        if (newListFooter === null) {
          return
        }

        this.$nextTick(() => {
          newListDom.appendChild(newListFooter);
        });
      }
    },

    syncUpstream() {

      axios
        .post("/nova-vendor/nova-kanban/board/" + this.boardId + "/sync-upstream")
        .then(res => {
          this.columns = res.data;
          // this.forceRerender();
          // alert('done');
        })
        .catch(err => {
        });
    },
    forceRerender() {
      this.componentKey += 1;
    }
  }
};
</script>
<style scoped>
.status-drag {
  transition: transform 0.5s;
  transition-property: all;
  opacity: 0.5;
}
</style>