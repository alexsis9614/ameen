import DeleteForm from '@dashboard-partials/delete-form';
import EditAddForm from '@dashboard-partials/edit-add-form';
import EditAddColumn from '@dashboard-partials/edit-add-column';
import Categories from '@dashboard-sections/categories';
import documentation from '@dashboard-partials/documentation';

export default {
  template: `
    <div class="bookit-row flex-start">
      <div class="bookit-wrapper no-padding col-3-4">
        <div class="bookit-header">
          <input type="text" class="bookit-filter-input" v-model="filter" :placeholder="translations.search" @keydown="$event.stopImmediatePropagation()">
          <a href="#" class="bookit-button" @click="showAddForm($event)" v-html="translations.add_new"></a>
        </div>
        <template>
          <div class="datatable-wrapper">
            <datatable :columns="columns" :data="rows" :filter="filter" :per-page="15"></datatable>
            <datatable-pager v-model="page" class="pagination" type="abbreviated"></datatable-pager>
            <div v-if="rows.length == 0" class="no-results">{{ translations.no_results }}</div>
          </div>
        </template>
        <edit-add-form type="service" v-if="showEditAddForm"></edit-add-form>
        <delete-form type="service" v-if="showDeleteForm"></delete-form>
      </div>
      <div class="bookit-wrapper no-padding no-height col-4">
        <documentation></documentation>
        <div class="bookit-header">
          <h4>{{ translations.categories }}</h4>
        </div>
        <categories></categories>
      </div>
    </div>
  `,
  components: {
    'edit-add-form': EditAddForm,
    'delete-form': DeleteForm,
    EditAddColumn,
    Categories,
    documentation
  },
  data: () => ({
    translations: bookit_window.translations,
    filter:  '',
    columns: [
      { label: bookit_window.translations.title, field: 'title', headerAlign: 'left' },
      { label: bookit_window.translations.category, representedAs: (row) => { let category = row.unset.categories.find(x => x.id === row.category_id); if (category) return category.name; }, headerAlign: 'left' },
      { label: bookit_window.translations.icon, representedAs: (row) => { if (row.unset.media_url) return `<img src="${row.unset.media_url}" width="50"/>`; }, interpolate: true, headerAlign: 'left', sortable: false, },
      { label: bookit_window.translations.duration, field: 'unset.duration', headerAlign: 'left', sortable: false, },
      { label: bookit_window.translations.price, field: 'price', headerAlign: 'left' },
      { label: bookit_window.translations.actions, component: EditAddColumn, type: 'service', headerAlign: 'center' }
    ],
    page: 1
  }),
  props: {
    rows: {
      type: Array,
      required: true
    },
    categories: {
      type: Array,
      required: true
    }
  },
  computed: {
    showEditAddForm: {
      get() {
        return this.$store.getters.getShowEditAddForm;
      },
      set( value ) {
        this.$store.commit('setShowEditAddForm', value);
      }
    },
    showDeleteForm () {
      return this.$store.getters.getShowDeleteForm
    }
  },
  created () {
    this.rows.map((object) => {
      object.unset.categories = this.categories;
      object.unset.duration   = this.secondsToFormatted( object.duration, true );
    });
    this.$store.commit('setRows', this.rows);
    this.$store.commit('setCategories', this.categories);
  },
  methods: {
    showAddForm( event ) {
      event.preventDefault();
      this.showEditAddForm = true;
    }
  }
}