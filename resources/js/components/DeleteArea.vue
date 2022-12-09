<template>
  <div
    class="tw-fixed tw-z-100 tw-top-0 tw-left-0 tw-w-full tw-h-14 tw-bg-red-700 tw-flex tw-items-center tw-justify-center tw-font-extrabold text-white tw-cursor-pointer tw-opacity-90">
    Drop here to delete
    <draggable
      class="flex-1 tw-overflow-hidden tw-min-h-[50px] tw-opacity-0 tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full"
      v-model="trash" v-bind="trashOptions" item-key="id" @change="handleChange">
      <template #item="{ element }">
        <div>Deleting {{ element.title }}</div>
      </template>
    </draggable>
  </div>
</template>
<script>
import draggable from "vuedraggable";
export default {
  props: {},
  components: { draggable },
  computed: {
    trashOptions() {
      return {
        group: {
          name: 'trash',
          draggable: '.dropitem',
          put: () => true,
          pull: false
        },
      }
    }
  },
  data() {
    return {
      trash: []
    }
  },
  methods: {

    handleChange(evt) {
      let elem = this.trash[0]
      this.$emit('deletingItem', elem)

      // Send the entire list of columns to the server
      axios.post("/nova-vendor/nova-kanban/delete", { id: elem.id, model: elem.model }).then(() => {
        this.trash.shift()
        this.$emit('itemDeleted', elem)
      })
        .catch(err => {

          // Handle the error returned from our request
          this.handleErrors(err);
        });;
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
        alert(this.errorMessage)
      } else {
        alert(err.response.data.message)
        console.log(err)
      }
    }
  }
}
</script>