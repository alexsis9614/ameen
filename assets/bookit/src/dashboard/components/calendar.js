import filters from '@dashboard-calendar/topbar-filters'
import calendar from '@dashboard-calendar/index';
import documentation from '@dashboard-partials/documentation'

export default {
  template: `
    <div class="bookit-app-content">
      <div class="bookit-wrapper">
        <documentation></documentation>
        <div class="bookit-header">
          <filters :autorefreshList="autorefresh_list"></filters>
        </div>
        <div class="admin-theme">
          <calendar></calendar>
        </div>
      </div>
    </div>
  `,
  components: {
    calendar,
    filters,
    documentation
  },
  props: {
    language: String,
    categories: {
      type: Array,
      required: true,
      default: [],
    },
    customers: {
      type: Array,
      required: true,
      default: [],
    },
    services: {
      type: Array,
      required: true,
      default: [],
    },
    filter_services: {
      type: Array,
      required: true,
      default: [],
    },
    staff: {
      type: Array,
      required: true,
      default: [],
    },
    settings: {
      type: Object,
      required: true,
    },
    time_slot_list: {
      type: Array,
      required: true
    },
    payment_methods: {
      type: Object,
      required: true
    },
    appointment_statuses: {
      type: Object,
      required: true
    },
    autorefresh_list: {
      type: Object,
      required: true
    }
  },
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
  },
  created () {
    /** set data to store **/
    this.$store.commit('setCategories', this.categories);
    this.$store.commit('setServices', this.services);
    this.$store.commit('setCustomers', this.customers);
    this.$store.commit('setFilterServices', this.filter_services);
    this.$store.commit('setTimeSlotList', this.time_slot_list);

    this.staff.map((staff) => {
      staff.staff_services.map((staff_service) => {
        let service = this.services.find(s => s.id == staff_service.id);
        staff_service.title = ( typeof service !== 'undefined' ) ? service.title : `#${staff_service.id}`;
      });
      staff.working_hours   = JSON.parse( JSON.stringify( staff.working_hours ).replace(/"NULL"/gi, null) ) || [];
    });

    this.$store.commit('setStaff', this.staff);
    this.$store.commit('setPaymentMethods', this.payment_methods);
    this.$store.commit('setAppointmentStatuses', this.appointment_statuses);
    Object.keys(this.settings).forEach((key) => {
      if ( this.settings[key] === 'true' ) {
        this.settings[key] = true;
      } else if ( this.settings[key] === 'false' ) {
        this.settings[key] = false;
      }
    });

    this.$store.commit('setSettings', this.settings);

    /** set data to store calendar  **/
    this.calendar.curAppointmentsDate = this.moment().startOf('day');
    if ( this.getCookie('bookit_calendar_type') ) {
      this.calendar.type = this.getCookie('bookit_calendar_type');
    }
    if ( this.getCookie('bookit_is_detailed') ) {
      this.calendar.isDetailedView = this.getCookie('bookit_is_detailed') == 'true'? true: false;
    }

    this.moment.updateLocale(this.language, {
      week : {
        dow : 1
      }
    });
  }
}
