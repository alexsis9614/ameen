import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

export default {
  name: 'filters',
  template: `
    <div class="topbar-filters">
      <div class="bookit-row flex-start">
        
        <div class="form-group col-3">

          <treeselect name="service" v-model="filterService" v-on:input="changeService" ref="treeselect"
                      :placeholder="[translations.services, translations.all].join(': ')"
                      value-consists-of="LEAF_PRIORITY"
                      :multiple="true"
                      :searchable="false"
                      :flat="false"
                      :default-expand-level="1"
                      :autoFocus="false"
                      :options="filter_services">
          </treeselect>
        </div>

        <div class="form-group col-2">
          <select @change="applyFilter($event)" name="staff" v-model="filterStaff">
            <option :value="null">
              <span v-if="filterStaff == null">{{ translations.staff }}: </span>{{ translations.all }}
            </option>
            <option :value="employee.id" v-for="employee in staff">
              <span v-if="filterStaff == employee.id">{{ translations.staff }}: </span>{{ employee.full_name }}
            </option>
          </select>
        </div>
        
        <div class="form-group col-2">
          <select @change="applyFilter($event)" name="status" v-model="filterStatus">
            <option :value="null">
              <span v-if="filterStatus == null">{{ translations.status }}: </span>{{ translations.all }}
            </option>
            <option :value="value" v-for="(status, value) in statusList.appointment">
              <span v-if="filterStatus == value">{{ translations.status }}: </span>{{ status }}
            </option>
          </select>
        </div>
        
        <div class="form-group col-2" v-if="autorefreshList">
          <select @change="setAutorefresh($event)" name="autorefresh" v-model="autorefresh">
            <option :value="0">
              <span v-if="autorefresh == 0">{{ translations.autorefresh }}: </span>{{ translations.disabled }}</option>
            <option :value="value" v-for="(title, value) in autorefreshList">
              <span v-if="autorefresh == value">{{ translations.autorefresh }}: </span>{{ title }}
            </option>
          </select>
        </div>
      </div>
    </div>
  `,
  components: {
    Treeselect
  },
  data: () => ({
    value: null,
    autorefresh: 0,
    autorefreshTimer: null,
    filterCategory: null,
    filterStaff: null,
    filterService: [],
    filterStatus: null,
    translations: bookit_window.translations
  }),
  computed: {
    isUpdated() {
      return this.$store.getters.getIsObjectUpdated;
    },
    calendar () {
      return this.$store.getters.getCalendar;
    },
    services() {
      return this.$store.getters.getServices;
    },
    filter_services() {
      return this.$store.getters.getFilterServices;
    },
    staff() {
      return this.$store.getters.getStaff;
    },
    statusList() {
      return this.$store.getters.getAppointmentStatuses;
    },
    calendarLoading: {
      get() {
        return this.$store.getters.getCalendarLoading;
      },
      set( value ) {
        this.$store.commit('setCalendarLoading', value);
      }
    },
  },
  props: {
    autorefreshList: {
      type: Object,
      default: {}
    },
  },
  created () {
    this.$store.commit('setCalendarAppointmentsDate', this.getCalendarCurrentAppointmentDate());
    this.setCalendarType();
  },
  mounted () {
    this.setFilters();
    if (this.autorefresh > 0) {
      this.autorefreshData();
    }
  },
  methods: {
    generateSelectedServiceElement( service_length ) {
      let selectedServices = document.createElement('span');
      selectedServices.textContent = [this.translations.selected, service_length].join(': ');
      selectedServices.className = 'chosen-service';
      return selectedServices;
    },

    changeService( value ) {
      var selectedServices = this.generateSelectedServiceElement(value.length);

      if( value.length > 0 ){
        if( this.$refs.treeselect.$el.getElementsByClassName('chosen-service').length == 0){
          this.$refs.treeselect.$el.getElementsByClassName('vue-treeselect__multi-value')[0].prepend(selectedServices);
        }else{
          this.$refs.treeselect.$el.getElementsByClassName('vue-treeselect__multi-value')[0].replaceChild(
              selectedServices, this.$refs.treeselect.$el.getElementsByClassName('chosen-service')[0]);
        }
      }else{
        this.$refs.treeselect.$el.getElementsByClassName('vue-treeselect__multi-value')[0].removeChild(
            this.$refs.treeselect.$el.getElementsByClassName('chosen-service')[0]);
      }

      this.setCookie(['bookit_filter', 'service'].join('_'), value);
      this.$store.commit('setIsObjectUpdated', true);
    },

    async getAppointments() {
      this.calendarLoading  = true;
      const data      = {
        nonce: bookit_window.nonces.bookit_get_calendar_appointments,
        is_detail: this.getIsDetailedView(),
        start_timestamp: this.getStartDate(),
        end_timestamp: this.getEndDate(),
        service_ids: (this.filterService.length > 0) ? this.filterService.join() : '',
        staff_id: (this.filterStaff) ? this.filterStaff : '',
        status: (this.filterStatus) ? this.filterStatus : '',
      };

      await this.axios.post(`${bookit_window.ajax_url}?action=bookit_get_calendar_appointments`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          this.$store.commit('setAppointments', response.data);
          this.calendarLoading = false;
          this.$store.commit('setIsObjectUpdated', false);
        }
      });
    },
    autorefreshData: function () {
      this.autorefreshTimer = setInterval(function () {
        this.getAppointments();
      }.bind(this), (this.autorefresh * 1000));
    },
    setAutorefresh( event ) {
      this.autorefresh = event.target.value;
      this.setCookie(['bookit_filter', event.target.name].join('_'), event.target.value);
      clearInterval(this.autorefreshTimer);

      if ( event.target.value > 0 ){
        this.autorefreshData();
      }
    },
    setFilters() {

      /** set staff **/
      if ( this.getCookie('bookit_filter_staff') ) {
        var exist = this.staff.some((st) => { return st.id === this.getCookie('bookit_filter_staff'); });
        if ( exist === true ) {
          this.filterStaff   = this.getCookie('bookit_filter_staff');
        }
      }

      /** set service **/
      if ( this.getCookie('bookit_filter_service') ) {
        this.filterService = this.getCookie('bookit_filter_service').split(",");
        this.filterService.forEach((selectedService, index) => {
          var exist = this.services.some(function(service) { return service.id === selectedService; });
          /** remove selected service if not exist in current list **/
          if ( exist == false ) {
            this.filterService.splice(index, 1);
          }
        });

        if ( this.filterService.length > 0 ) {
          var selectedServices = this.generateSelectedServiceElement(this.filterService.length);
          this.$refs.treeselect.$el.getElementsByClassName('vue-treeselect__multi-value')[0].prepend(selectedServices);
        }
      }

      if ( this.getCookie('bookit_filter_status') ) {
        this.filterStatus  = this.getCookie('bookit_filter_status');
      }
      if ( this.getCookie('bookit_filter_autorefresh') ) {
        this.autorefresh  = this.getCookie('bookit_filter_autorefresh');
      }
    },
    setCalendarType() {
      if ( this.getCookie('bookit_calendar_type') ) {
        this.calendar.type = this.getCookie('bookit_calendar_type');
      }
    },
    getStartDate() {
      return this.calendar.curAppointmentsDate.clone().startOf(`${this.calendar.type}`).unix();
    },
    getEndDate() {
      return this.calendar.curAppointmentsDate.clone().endOf(`${this.calendar.type}`).unix();
    },
    getIsDetailedView(){
      return this.getCookie('bookit_is_detailed') == 'true'? 1: 0;
    },
    /** get from cookie if exist, if not use now **/
    getCalendarCurrentAppointmentDate() {
      if ( this.moment(this.getCookie('bookit_current_appointment_date'), 'DD MM YY').isValid() ) {
        return this.moment(this.getCookie('bookit_current_appointment_date'), 'DD MM YY');
      }
      return this.moment().startOf('day');
    },
    applyFilter( event ) {
      if ( event.target.value != null ){
        this.setCookie(['bookit_filter', event.target.name].join('_'), event.target.value);
        this.$store.commit('setIsObjectUpdated', true);
      }
      this.setFilters();
    },
  },
  watch: {
    'calendar.curAppointmentsDate' (value) {
      this.setCookie('bookit_current_appointment_date', value.format('DD MM YY'));
      this.getAppointments();
    },
    'calendar.type'() {
      this.getAppointments();
    },
    isUpdated() {
      this.getAppointments();
      this.$store.commit('setIsObjectUpdated', false);
    }
  }
}