import Tabs from '@dashboard-partials/tabs';
import Tab from '@dashboard-partials/tab';
import AppointmentActions from '@dashboard-sections/appointment-actions';
import appointment_form from '@dashboard-sections/appointment/form';
import delete_form from '@dashboard-partials/delete-appointment-form';
import documentation from '@dashboard-partials/documentation';

import tableFilter from '@dashboard-sections/appointment/table-filter';

export default {
  template: `
    <div class="bookit-wrapper appointment-tabs">
      <documentation></documentation>
      
      <tabs>
        <tab :name="translations.all" :tab_rows_status="''" :selected="true">
          <div class="datatable-wrapper all-appointments appointments">
            <div v-if="loading" class="loader">
              <div class="loading"><div v-for="n in 9"></div></div>
            </div>
            <tableFilter v-on:setFilter="setFilter"></tableFilter>
            
            <datatable name="all"  :columns="columns" :data="getAllData" :filter="filter" :perPage="20"></datatable>
            <datatable-pager table="all" v-model="page_all" class="pagination" type="abbreviated"></datatable-pager>
            <div v-if="emptyTable.all" class="no-results">{{ translations.no_results }}</div>
          </div>
        </tab>
        
        <tab :name="translations.pending" :tab_rows_status="'pending'" >
          <div class="datatable-wrapper pending-appointments appointments">
            <div v-if="loading" class="loader">
              <div class="loading"><div v-for="n in 9"></div></div>
            </div>
            <tableFilter></tableFilter>
            
            <datatable name="pending" :columns="columns" :data="getPendingData" :filter="filter" :perPage="20"></datatable>
            <datatable-pager table="pending" v-model="page_pending" class="pagination" type="abbreviated"></datatable-pager>
            <div v-if="emptyTable.pending" class="no-results">{{ translations.no_results }}</div>
          </div>
        </tab>
        
        <tab :name="translations.approved" :tab_rows_status="'approved'">
          <div class="datatable-wrapper approved-appointments appointments">
            <div v-if="loading" class="loader">
              <div class="loading"><div v-for="n in 9"></div></div>
            </div>
            <tableFilter></tableFilter>
            <datatable name="approved" :columns="columns" :data="getApprovedData" :filter="filter" :perPage="20"></datatable>
            <datatable-pager table="approved" v-model="page_approved" class="pagination" type="abbreviated"></datatable-pager>
            <div v-if="emptyTable.approved" class="no-results">{{ translations.no_results }}</div>
          </div>
        </tab>
        
        <tab :name="translations.cancelled" :tab_rows_status="'cancelled'">
          <div class="datatable-wrapper cancelled-appointments appointments">
            <div v-if="loading" class="loader">
              <div class="loading"><div v-for="n in 9"></div></div>
            </div>
            <tableFilter></tableFilter>
            <datatable name="cancelled" :columns="columns" :data="getCancelledData" :filter="filter" :perPage="20"></datatable>
            <datatable-pager table="cancelled" v-model="page_cancelled" class="pagination" type="abbreviated"></datatable-pager>
            <div v-if="emptyTable.cancelled" class="no-results">{{ translations.no_results }}</div>
          </div>
        </tab>
        <a href="#" class="bookit-button temp-add-appointment" @click="showForm()">+&nbsp;{{ translations.add_new }}</a>
      </tabs>

      <appointment_form :type="this.actionType" v-if="showEditAddForm"></appointment_form>
      <delete_form type="appointment" v-if="showDeleteForm"></delete_form>
    </div>
  `,
  components: {
    tabs: Tabs,
    tab: Tab,
    AppointmentActions,
    documentation,
    appointment_form,
    delete_form,
    tableFilter
  },
  data: () => ({
    translations: bookit_window.translations,
    loading: true,
    emptyTable: {'pending': false, 'approved': false, 'cancelled': false, 'all': false},
    filter: [],
    page_pending: 1,
    page_approved: 1,
    page_cancelled: 1,
    page_all: 1,
    columns: [
      { label: '#', field: 'id', class: 'column-id', headerAlign: 'left' },
      { label: bookit_window.translations.customer, headerAlign: 'left', sortable: false, 'class': 'customer',
        representedAs: ({customer_name, customer_phone, notes}) => {

          if(notes && notes.phone) {
            customer_phone = notes.phone;
          }
          if(customer_phone) {
            return `<span>${customer_name}</span><a href="tel:${customer_phone}">${customer_phone}</a>`;
          }else{
            return `<span>${customer_name}</span>`;
          }
        }, interpolate: true
      },
      { label: bookit_window.translations.staff, field: 'staff_name', headerAlign: 'left', sortable: false },
      { label: bookit_window.translations.service, field: 'service_name', headerAlign: 'left', sortable: false, headerClass:'appointment-service' },
      { label: bookit_window.translations.price, field: 'price_row', headerAlign: 'left', sortable: false },
      { label: bookit_window.translations.date, headerAlign: 'left', field: 'date_timestamp_title', sortable: false,},
      { label: bookit_window.translations.time, headerAlign: 'left', field: 'time_title', sortable: false,},
      { label: bookit_window.translations.payment, headerAlign: 'left', sortable: false, interpolate: true, class:'appointment-payment',
        representedAs: ({payment_method, payment_status}) => {
          if (payment_method) {
            return `<b class="text-capitalize">${bookit_window.translations[payment_method] !== undefined ? bookit_window.translations[payment_method]: payment_method}</b><span class="status payment ${payment_status}-payment">${bookit_window.translations[payment_status]}</span>`;
          } else {
            return `<b class="text-capitalize">No payment</b>`;
          }
        }
        },
      { label: bookit_window.translations.status, headerAlign: 'left', sortable: false, interpolate: true, class:'appointment-status',
        representedAs: ({status}) => `<div><span class="${status}-appointment"></span>${bookit_window.translations[status]}</div>` },
      { label: bookit_window.translations.actions, headerClass:'appointment-actions', component: AppointmentActions, type: 'appointment', headerAlign: 'left' }
    ]
  }),
  props: {
    appointment_statuses: {
      type: Object,
      required: true
    },
    customers: {
      type: Array,
      required: true,
      default: [],
    },
    payment_methods: {
      type: Object,
      required: true
    },
    settings: {
      type: Object,
      required: true,
    },
    services: {
      type: Array,
      required: true
    },
    staff: {
      type: Array,
      required: true
    },
    time_format: {
      type: String
    },
    time_slot_list: {
      type: Array,
      required: true
    },
  },
  computed: {
    actionType () {
      return this.$store.getters.getActionType
    },
    customFilter:{
      get(){
        return this.$store.getters.getFilterAppointments;
      },
      set( value ) {
        this.$store.commit('setFilterAppointments', value);
      }
    },
    showEditAddForm () {
      return this.$store.getters.getShowEditAddForm
    },
    showDeleteForm () {
      return this.$store.getters.getShowDeleteForm
    }
  },
  created () {
    this.staff.map((staff) => {
      staff.staff_services.map((staff_service) => {
        let service = this.services.find(s => s.id == staff_service.id);
        staff_service.title = ( typeof service !== 'undefined' ) ? service.title : `#${staff_service.id}`;
      });
      let wourking_hours    = JSON.parse( JSON.stringify( staff.working_hours ).replace(/"NULL"/gi, null) ) || [];
      staff.working_hours   = wourking_hours.sort((a, b) => a.weekday - b.weekday);
    });

    this.$store.commit('setServices', this.services);
    this.$store.commit('setStaff', this.staff);
    this.$store.commit('setPaymentMethods', this.payment_methods);
    this.$store.commit('setAppointmentStatuses', this.appointment_statuses);
    this.$store.commit('setTimeFormat', this.time_format);
    this.$store.commit('setTimeSlotList', this.time_slot_list);
    this.$store.commit('setCustomers', this.customers);
    this.$store.commit('setSettings', this.settings);
  },
  methods: {
    showForm() {
      let newAppointment = {
        'status': 'pending',
        'service_id': '',
        'staff_id': '',
        'payment_status': 'pending',
        'payment_method': 'locally',
        'date_timestamp': this.moment().startOf('day').format('X')
      };
      this.$store.commit('setActionType', 'add');
      this.$store.commit('setEditRow', newAppointment);
      this.$store.commit('setShowEditAddForm', true);
    },
    setFilter( filter ) {
      this.filter = filter;
    },
    async getPendingData( { sortBy, sortDir, perPage, page, filter } ) {
      return await this.getData(sortBy, sortDir, perPage, page, filter, 'pending');
    },
    async getApprovedData( { sortBy, sortDir, perPage, page, filter } ) {
      return await this.getData(sortBy, sortDir, perPage, page, filter, 'approved');
    },
    async getCancelledData( { sortBy, sortDir, perPage, page, filter } ) {
      return await this.getData(sortBy, sortDir, perPage, page, filter, 'cancelled');
    },
    async getAllData( { sortBy, sortDir, perPage, page, filter } ) {
      return await this.getData(sortBy, sortDir, perPage, page, filter);
    },
    async getData( sortBy, sortDir, perPage, page, filter, status = '' ) {
      this.loading      = true;
      const sortParams  = sortBy && sortDir ? {
        order: sortDir,
        sort: sortBy,
      } : {};

      const params      = {
        ...sortParams,
        status: status,
        nonce: bookit_window.nonces.bookit_get_appointments,
        action: 'bookit_get_appointments',
        limit: perPage,
        offset: ( ( page - 1 ) * perPage ) || 0,
      };

      this.filter.forEach(item => {
        params[Object.keys(item)[0]] = item[Object.keys(item)[0]];
      });

      const response = await this.axios
        .get( `${bookit_window.ajax_url}?${ this.getQueryFromObject( params ) }` )
        .then( function( response ) {
          return response.data.data;
        });

      if ( (this.$store.getters.getActiveTab.tab_rows_status) == status ){
        this.$store.commit('setRows', response.appointments);
      }

      if ( status == '' ){
        status = 'all';
      }
      this.emptyTable[status] = ( parseInt( response.total, 10 ) <= 0 );

      this.loading    = false;

      return {
        rows: response.appointments || [],
        totalRowCount: parseInt( response.total || 0, 10 ),
      };
    },
  },
  watch: {
    'customFilter.search'()  {
      if ( this.customFilter.search.length > 2 || this.customFilter.search == ""){
        this.filter.push({'search': this.customFilter.search});
      }else if ( !this.customFilter.hasOwnProperty('search') || this.customFilter.search == null ) {
        this.filter.push({'search': null});
      }
    },
    'customFilter.dates.start'()  {
      if ( this.customFilter.hasOwnProperty('dates') && this.customFilter.dates !== null && this.customFilter.dates.hasOwnProperty('start') ) {
        this.filter.push({'start': this.customFilter.dates.start});
      }else{
        this.filter.push({'start': null});
      }
    },
    'customFilter.dates.end'()  {
      if ( this.customFilter.hasOwnProperty('dates') && this.customFilter.dates !== null && this.customFilter.dates.hasOwnProperty('end') ) {
        this.filter.push({'end': this.customFilter.dates.end});
      }else{
        this.filter.push({'end': null});
      }
    }
  }
}