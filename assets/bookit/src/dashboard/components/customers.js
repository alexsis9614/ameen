import DeleteForm from '@dashboard-partials/delete-form';
import EditAddForm from '@dashboard-partials/edit-add-form';
import EditAddColumn from '@dashboard-partials/edit-add-column';
import documentation from '@dashboard-partials/documentation';

export default {
  template: `
    <div class="bookit-wrapper">
      <documentation></documentation>
      <div class="bookit-header">
        <input type="text" class="bookit-filter-input" :placeholder="translations.search" @keyup="initFilter($event)" @keydown="$event.stopImmediatePropagation()">
        <a href="#" class="bookit-button" @click="showAddForm($event)">{{ translations.add_new }}</a>
      </div>
      
      <template>
        <div class="datatable-wrapper">
          <div  v-if="loading" class="loader">
            <div class="loading"><div v-for="n in 9"></div></div>
          </div>
          <datatable :columns="columns" :data="getData" :filter="filter" :perPage="15"></datatable>
          <datatable-pager v-model="page" class="pagination" type="abbreviated"></datatable-pager>
          <div v-if="emptyTable" class="no-results">{{ translations.no_results }}</div>
        </div>
      </template>
      <edit-add-form type="customer" v-if="showEditAddForm"></edit-add-form>
      <delete-form type="customer" v-if="showDeleteForm"></delete-form>
    </div>
  `,
  components: {
    'edit-add-form': EditAddForm,
    'delete-form': DeleteForm,
    EditAddColumn,
    documentation
  },
  data: () => ({
    translations: bookit_window.translations,
    loading: true,
    emptyTable: false,
    filter:  '',
    page: 1,
    columns: [
      { label: '#', field: 'id', class: 'column-id', headerAlign: 'left' },
      { label: bookit_window.translations.full_name, field: 'full_name', headerAlign: 'left' },
      { label: bookit_window.translations.email, field: 'email', headerAlign: 'left' },
      { label: bookit_window.translations.phone, field: 'phone', headerAlign: 'left' },
      { label: bookit_window.translations.wp_user, field: 'unset.wp_user.display_name', headerAlign: 'left' },
      { label: bookit_window.translations.actions, component: EditAddColumn, type: 'customer', headerAlign: 'center' }
    ]
  }),
  props: {
    wp_users: {
      type: Array,
      required: true
    }
  },
  computed: {
    showEditAddForm () {
      return this.$store.getters.getShowEditAddForm
    },
    showDeleteForm () {
      return this.$store.getters.getShowDeleteForm
    }
  },
  created () {
    this.$store.commit('setWpUsers', this.wp_users);
  },
  methods: {
    showAddForm( event ) {
      event.preventDefault();
      this.$store.commit('setShowEditAddForm', true);
    },
    async getData( { sortBy, sortDir, perPage, page, filter } ) {
      this.loading      = true;
      const sortParams  = sortBy && sortDir ? {
        order: sortDir,
        sort: sortBy,
      } : {};

      const params      = {
        ...sortParams,
        search: filter,
        nonce: bookit_window.nonces.bookit_get_customers,
        action: 'bookit_get_customers',
        limit: perPage,
        offset: ( ( page - 1 ) * perPage ) || 0,
      };

      let response = await this.axios
        .get( `${bookit_window.ajax_url}?${ this.getQueryFromObject( params ) }` )
        .then( function( response ) {
          return response.data.data;
        });

      response.customers.map((object) => {
        object.unset          = { wp_users: this.wp_users };
        object.unset.wp_user  = this.wp_users.find( (wp_user) => wp_user.ID === object.wp_user_id );
      });

      this.$store.commit('setRows', response.customers);

      this.emptyTable = ( parseInt( response.total, 10 ) <= 0 );
      this.loading    = false;

      return {
        rows: response.customers || [],
        totalRowCount: parseInt( response.total || 0, 10 ),
      };
    },
    initFilter( event ) {
      this.loading = true;
      clearTimeout(timing);
      let timing = setTimeout( () => {
        this.filter = event.target.value;
      }, 1000);
    }
  }
}