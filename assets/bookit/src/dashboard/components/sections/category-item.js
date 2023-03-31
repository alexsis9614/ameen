import DeleteForm from '@dashboard-partials/delete-form';
export default {
  template: `
    <div>
      <div class="category-item">
        <input type="text" v-autowidth="{maxWidth: '300px', minWidth: '70px', comfortZone: 0}" v-model="category.name" @change="saveCategory">
        <i class="delete-icon" @click="deleteCategoryForm($event, category)" :title="[translations.delete, translations.category].join(' ')"></i>
      </div>
      <delete-form type="category" v-on:setShowCategoryDeleteForm="setShowCategoryDeleteForm" v-if="showCategoryDeleteForm"></delete-form>
    </div>
  `,
    components: {
      'delete-form': DeleteForm,
    },
    data: () => ({
      showCategoryDeleteForm: false,
      translations: bookit_window.translations,
    }),
    props: {
    category: {
      type: Object,
      required: true
    }
  },
  methods: {
    setShowCategoryDeleteForm(value) {
      this.showCategoryDeleteForm = value;
    },
    saveCategory() {
      this.category.nonce = bookit_window.nonces.bookit_save_category;
      this.axios.post(`${bookit_window.ajax_url}?action=bookit_save_category`, this.generateFormData(this.category), this.getPostHeaders()).then((res) => {
        let response = res.data;
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    deleteCategoryForm( event, row ) {
      event.preventDefault();
      this.getAssosiatedData(row);
    },
    getAssosiatedData(row) {
      const data = {
        nonce: bookit_window.nonces[`bookit_get_category_assosiated_total_data`],
        id: row.id
      };

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_get_category_assosiated_total_data`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
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
            this.showCategoryDeleteForm = true;
          } else {
            this.deleteCategory( row );
          }
        }
      });
    },

    deleteCategory( category ) {
      this.axios.get(`${bookit_window.ajax_url}?action=bookit_delete_category&nonce=${bookit_window.nonces.bookit_delete_item}&id=${category.id}`).then((res) => {
        let response = res.data;
        if ( response.success ) {
          this.$store.dispatch(`delete_category`, category);
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    }
  }
}