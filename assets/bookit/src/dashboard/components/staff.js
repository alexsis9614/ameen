import DeleteForm from '@dashboard-partials/delete-form';
import EditAddForm from '@dashboard-partials/edit-add-form';
import EditAddColumn from '@dashboard-partials/edit-add-column';
import documentation from '@dashboard-partials/documentation';

export default {
  template: `
    <div class="bookit-wrapper">
      <documentation></documentation>
      <div class="bookit-header" >
        <input v-if="!bookit_user.is_staff" type="text" class="bookit-filter-input" v-model="filter" :placeholder="translations.search" @keydown="$event.stopImmediatePropagation()">
        <a v-if="!bookit_user.is_staff" href="#" class="bookit-button" @click="showAddForm($event)">{{ translations.add_new }}</a>
      </div>
      
      <template>
        <div class="datatable-wrapper">
          <datatable :columns="columns" :data="rows" :filter="filter" :per-page="15"></datatable>
          <datatable-pager v-model="page" class="pagination" type="abbreviated"></datatable-pager>
          <div v-if="rows.length == 0" class="no-results">{{ translations.no_results }}</div>
        </div>
      </template>
      <edit-add-form type="staff" v-if="showEditAddForm"></edit-add-form>
      <delete-form type="staff" v-if="showDeleteForm"></delete-form>
    </div>
  `,
  components: {
    'edit-add-form': EditAddForm,
    'delete-form': DeleteForm,
    EditAddColumn,
    documentation
  },
  data: () => ({
    bookit_user: bookit_window.bookit_user,
    translations: bookit_window.translations,
    filter:  '',
    page: 1,
    columns: [
      { label: bookit_window.translations.full_name, field: 'full_name', headerAlign: 'left' },
      { label: bookit_window.translations.email, field: 'email', headerAlign: 'left' },
      { label: bookit_window.translations.phone, field: 'phone', headerAlign: 'left' },
      { label: bookit_window.translations.services, representedAs: ({ staff_services }) => staff_services.map((object) => object.title).join(', ').replace(/(^[,\s]+)|([,\s]+$)/g, ''),
        headerAlign: 'left',
        headerClass:'staff-service'
      },
      { label: bookit_window.translations.working_hours, sortable: false, headerAlign: 'center', interpolate: true, align:'center',
        representedAs: ({ working_hours }) => working_hours.map((object) => {
          if ( object.start_time ) {
            return `</div><div class="wh-day"><span>${object.start_time.substring(0, 5)}</span><span>${object.end_time.substring(0, 5)}</span></div>`;
          } else {
            return `<div class="wh-day off"><span>${bookit_window.translations.day_off}</span></div>`;
          }
        } ).join('') },
      { label: bookit_window.translations.actions, headerClass:'column-actions', component: EditAddColumn, type: 'staff', headerAlign: 'center' }
    ]
  }),
  props: {
    addons: {
      type: Array,
      required: true
    },
    answer: {},
    staff: {
      type: Array,
      required: true
    },
    services: {
      type: Array,
      required: true
    },
    wp_users: {
      type: Array,
      required: true
    },
  },
  computed: {
    rows: {
      get() {
        return this.$store.getters.getRows;
      },
      set( rows ) {
        this.$store.commit('setRows', rows);
      }
    },
    showEditAddForm () {
      return this.$store.getters.getShowEditAddForm;
    },
    showDeleteForm () {
      return this.$store.getters.getShowDeleteForm
    }
  },
  created() {
    /** show answer if exist ( for now used for google calendar connection )**/
    if ( this.answer ) {
      this.$toasted.show(this.answer.message, {
        type: (this.answer.status) ? 'success' : 'error'
      });
      /** clean from google get params **/
      window.history.pushState(null, null, window.location.pathname + '?page=bookit-staff');
    }
    this.staff.map((staff) => {
      staff.staff_services.map((staff_service) => {
        let service = this.services.find(s => s.id == staff_service.id);
        staff_service.title = ( typeof service !== 'undefined' ) ? service.title : `#${staff_service.id}`;
      });
      let wourking_hours    = JSON.parse( JSON.stringify( staff.working_hours ).replace(/"NULL"/gi, null) ) || [];

      staff.working_hours   = wourking_hours.sort((a, b) => a.weekday - b.weekday);
    });
    this.rows = this.staff;

    this.$store.commit('setAddons', this.addons);
    this.$store.commit('setServices', this.services);
    this.$store.commit('setWpUsers', this.wp_users);
  },
  methods: {
    showAddForm( event ) {
      event.preventDefault();
      this.$store.commit('setShowEditAddForm', true);
    },
  }
}