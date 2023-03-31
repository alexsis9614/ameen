
export default {
  template: `
    <div class="actions">
      <button :title="translations.edit" class="bookit-button edit-btn" @click="showEditForm($event, row)" ></button>
      <button v-if="!bookit_user.is_staff" :title="translations.delete" class="bookit-button delete-btn" @click="showDeleteForm($event, row, column.type)" ></button>
    </div>
  `,
  data: () => ({
    translations: bookit_window.translations,
    bookit_user: bookit_window.bookit_user,
  }),
  props: {
    row: {
      type: Object,
      required: true
    },
    column : {
      type: Object,
      required: true
    }
  },
  methods: {
    showEditForm( event, row ) {
      event.preventDefault();
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowEditAddForm', true);
    },
    showDeleteForm( event, row , type) {
      event.preventDefault();
      this.getAssosiatedData(type, row);
    },
    getAssosiatedData(type, row) {
      const data = {
        nonce: bookit_window.nonces[`bookit_get_${type}_assosiated_total_data`],
        id: row.id,
      };

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_get_${type}_assosiated_total_data`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success && response.data.total && Object.keys(response.data.total).length > 0) {
          var assosiated_total = response.data.total;
          for (var prop in assosiated_total) {
            if ( parseInt(assosiated_total[prop]) > 0) {
              this.isNeedApprove = true;
            }
          }

          if (this.isNeedApprove){ // show popup if have assosiated data
            row.assosiatedTotal = assosiated_total;
            this.$store.commit('setEditRow', row);
            this.$store.commit('setShowDeleteForm', true);
          }else {
            this.deleteRow( row, type );
          }
        }
      });
    },
    deleteRow( row , type) {
      this.axios.get(`${bookit_window.ajax_url}?action=bookit_delete_${type}&id=${row.id}&nonce=${bookit_window.nonces.bookit_delete_item}`).then((res) => {
        let response = res.data;
        if ( response.success ) {
          this.$store.dispatch('delete_item', row);
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
  }
}