export default {
  template: `
    <div :class="['bookit-modal-body', 'position-relative', {'appointment-view': viewType == 'appointmentView'}, {'appointment-view': viewType == 'edit'}]">
      <div v-if="loading" class="loader">
        <div class="loading"><div v-for="n in 9"></div></div>
      </div>
      <div :class="['accordion', {active: accordion.details}]">
        <a class="accordion-title" @click.self="switchAccordion('details', accordion.details)">{{ translations.details }}
          <span @click.prevent="setEditView" v-if="viewType == 'appointmentView'" :class="['icon', {'edit': !editView}, {'view': editView} ]"></span>
          <span class="float-right accordion-icon" @click.self="switchAccordion('details', accordion.details)"></span>
        </a>
        
        <div v-show="accordion.details" class="accordion-body">
          <div class="view" v-if="viewType == 'edit' || (viewType == 'appointmentView' && editView)">
            <div class="bookit-row normal">
              <div class="form-group col-3">
                <label for="service">{{ translations.service }}</label>
                <select v-model="appointment.service_id" @change="handleChangeService($event)" :class="{error:errors.service_id}">
                  <option v-for="service in services" :value="service.id">{{ service.title }}</option>
                </select>
                <span class="error-tip" v-if="errors.service_id">{{ errors.service_id }}</span>
              </div>
              <div class="form-group col-3">
                <label for="staff">{{ translations.staff }}</label>
                <select v-model="appointment.staff_id" @change="handleChangeStaff($event)" :class="{error:errors.staff_id || errors.staff_service}">
                  <option v-for="staff in availableStaff" :value="staff.id">{{ staff.full_name }}</option>
                </select>
                <span class="error-tip" v-if="errors.staff_id">{{ errors.staff_id }}</span>
                <span class="error-tip" v-if="errors.staff_service">{{ errors.staff_service }}</span>
              </div>
            </div>
            <div class="bookit-row normal">
              <div class="form-group col-3">
                <label for="date">{{ translations.date }}</label>
                <div class="form-date">
                  <input type="date" :class="['date', {error:errors.date_timestamp}]" :value="selectedDate | moment('YYYY-MM-DD')" :min="moment().startOf('day') | moment('YYYY-MM-DD')"
                         @change="handleChangeDate($event)">
                </div>
                <span class="error-tip" v-if="errors.date_timestamp">{{ errors.date_timestamp }}</span>
                <span class="info-tip width-lower" v-if="isGoogleBusyTime" > {{ translations.gc_busy_event_info }}</span>
              </div>
              <div class="form-group col-3">
                <label for="time">{{ translations.time }}</label>
                <div class="appointment-time">
                  <select :disabled="staffTimeSlots.start[0].value == null" :class="['start-time',{error:errors.dates}]" class="" name="selectedTimeStart" v-model="selectedTimeStart" @change="handleChangeTime($event)">
                    <option v-for="slot_start in staffTimeSlots.start" :value="slot_start.value">{{ slot_start.label }}</option>
                  </select>
                  <select :disabled="staffTimeSlots.end[0].value == null" :class="['end-time',{error:errors.dates}]" name="selectedTimeEnd" v-model="selectedTimeEnd" @change="handleChangeTime($event)">
                    <option v-for="slot_end in staffTimeSlots.end" :value="slot_end.value">{{ slot_end.label }}</option>
                  </select>
                </div>
                <span class="error-tip" v-if="errors.dates">{{ errors.dates }}</span>
              </div>
            </div>
            <div class="bookit-row">
              <div class="form-group">
                <label for="status">{{ translations.comment }}</label>
                <input name="comment" type="text" v-model="appointment.comment" :placeholder="translations.comment_placeholder">
              </div>
            </div>
          </div>
          <div class="view" v-else-if="viewType == 'appointmentView' && !editView">
            <div class="view-row">
              <div class="field-info md col-4">
                <div class="icon service"></div>
                <div class="info">
                  <span class="title">{{ translations.service }}:</span>
                  <span class="value">{{ selectedService.title }}</span>
                </div>
              </div>
              <div class="field-info md col-4">
                <div class="icon staff"></div>
                <div class="info">
                  <span class="title upper">{{ translations.staff }}:</span>
                  <span class="value">{{ selectedStaff.full_name }}</span>
                </div>
              </div>
            </div>
            <div class="view-row">
              <div class="field-info md col-4">
                <div class="icon date"></div>
                <div class="info">
                  <span class="title">{{ translations.date }}:</span>
                  <span class="value">{{ selectedDate.format('DD/MM/YYYY') }}</span>
                </div>
              </div>
              <div class="field-info md col-4">
                <div class="icon time"></div>
                <div class="info">
                  <span class="title">{{ translations.time }}:</span>
                  <span class="value">{{ moment.unix(appointment.start_time).format( wpTimeFormat ) }} — {{ moment.unix(appointment.end_time).format( wpTimeFormat ) }}</span>
                </div>
              </div>
              <div class="field-info md col-4">
                <div class="icon id"></div>
                <div class="info">
                  <span class="title upper">{{ translations.id }}:</span>
                  <span class="value">{{ appointment.id }}</span>
                </div>
              </div>
            </div>
            <div class="view-row">
              <div :class="['field-info col-12', {'md': !appointment.comment}]">
                <div class="icon comment"></div>
                <div class="info">
                  <span class="title">{{ translations.comment }}:</span>
                  <span class="value">{{ appointment.comment }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      <div :class="['accordion', {active: accordion.customer}]">
      <a class="accordion-title" @click.self="switchAccordion('customer', accordion.customer)">
        {{ translations.customer }}
        <span @click.prevent="setEditView" v-if="viewType == 'appointmentView'" :class="['icon', {'edit': !editView}, {'view': editView} ]"></span>
        <span class="float-right accordion-icon" @click.self="switchAccordion('customer', accordion.customer)"></span>
      </a>
      <div v-show="accordion.customer" class="accordion-body">
        <div class="view" v-if="viewType == 'edit' || (viewType == 'appointmentView' && editView)">
          <div class="view-row">
            <div class="field-info no-border sm col-6">
              <div class="customer-info">
                {{ appointment.customer_name }}
              </div>
            </div>
            <div class="field-info no-border sm col-6">
              <div class="appointment-action">
                <button type="button" v-if="appointment.status != 'pending'"  class="pending-btn" @click="$emit('changeStatus', appointment, 'pending')">
                  <i class="btn-icon"></i>{{ translations.pending }}
                </button>
                <button type="button" v-if="appointment.status != 'approved'"  class="approve-btn" @click="$emit('changeStatus', appointment, 'approved')">
                  <i class="btn-icon"></i>{{ translations.approve }}
                </button>
                <button type="button" v-if="appointment.status == 'pending'"  class="reject-btn" @click="$emit('changeStatus', appointment, 'cancelled')">
                  <i class="btn-icon"></i>{{ translations.reject }}
                </button>
                <button type="button" v-if="appointment.status == 'approved'" class="cancel-btn" @click="$emit('changeStatus', appointment, 'cancelled')">
                  <i class="btn-icon"></i>{{ translations.cancel }}
                </button>

                <span :class="['edit-arrow-icon', {'down': !editCustomer}, {'up': editCustomer}]" @click="editCustomer = !editCustomer"></span>
              </div>
            </div>
            <div v-if='editCustomer' class="field-info col-12 top-border">
              <div class="form-group">
                <label for="phone">{{ translations.phone }}</label>
                <input type="text" v-model="appointment.customer_phone" :class="{error:errors.customer_phone}">
                <span class="error-tip" v-if="errors.customer_phone">{{ errors.customer_phone }}</span>
              </div>
              <div class="form-group">
                <label for="email">{{ translations.email }}</label>
                <input type="text" v-model="appointment.customer_email" :class="{error:errors.customer_email}" disabled>
                <span class="error-tip" v-if="errors.customer_email">{{ errors.customer_email }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="view" v-else-if="viewType == 'appointmentView' && !editView">
          <div class="view-row no-wrap">
            <div class="field-info no-border sm col-3">
              <div class="customer-info">
                <span class="title">{{ appointment.customer_name }}</span>
              </div>
            </div>
            <div class="field-info no-border sm col-3">
              <div class="customer-info">
                <a v-if="appointment.customer_phone" :href="'tel:' + appointment.customer_phone">{{ appointment.customer_phone }}</a>
              </div>
            </div>
            <div class="field-info no-border sm col-3">
              <div class="customer-info">
                <a v-if="appointment.customer_email" :href="'mailto:' + appointment.customer_email">{{ appointment.customer_email }}</a>
              </div>
            </div>
            <div class="field-info no-border sm col-3">
              <div class="customer-info status">
                <span :class="['status-appointment', appointment.status ]"></span>
                <span class="title">{{ translations[appointment.status] }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
      
      <div :class="['accordion', {active: accordion.payment}]">
        <a class="accordion-title" @click.self="switchAccordion('payment', accordion.payment)">{{ translations.payment }}
          <span @click.prevent="setEditView" v-if="viewType == 'appointmentView'" :class="['icon', {'edit': !editView}, {'view': editView} ]"></span>
          <span class="float-right accordion-icon" @click.self="switchAccordion('payment', accordion.payment)"></span>
        </a>
        <div v-show="accordion.payment" class="accordion-body">
          <div class="view" v-if="viewType == 'edit' || (viewType == 'appointmentView' && editView)">
            <div class="view-row">
              <div class="field-info no-border sm col-6">
                <div class="payment-info pl-10">
                  {{ appointment.customer_name }}
                </div>
              </div>
              <div class="field-info no-border sm col-6">
                <div class="payment-info right">
                  <span class="title upper">{{ translations.total }}:</span>
                  <span class="value">
                    <span v-if="settings.currency_position === 'left'">{{ settings.currency_symbol }}</span>
                    {{ appointment.price }}
                    <span v-if="settings.currency_position === 'right'">{{ settings.currency_symbol }}</span>
                  </span>
                  <span :class="['edit-arrow-icon', {'down': !editPayment}, {'up': editPayment}]" @click="editPayment = !editPayment"></span>
                </div>
              </div>
              <div v-if='editPayment' class="field-info col-12 top-border">
                <div class="form-group col-4">
                  <label for="price">{{ translations.price }} {{ settings.currency_symbol }}</label>
                  <div class="input-type-number-wrapper">
                    <span onclick="this.parentNode.querySelector('input[type=number]').stepDown()" class="down"></span>
                    <input type="number" step="0.01" v-model="appointment.price" required>
                    <span onclick="this.parentNode.querySelector('input[type=number]').stepUp()" class="up"></span>
                  </div>
                </div>
                <div class="form-group col-4">
                  <label for="payment_method">{{ translations.payment_method }}</label>
                  <select id="payment_method" v-model="appointment.payment_method" :class="{error:errors.payment_method}">
                    <option :value="null" disabled hidden>--{{ translations.select }} {{ translations.payment_method }}--</option>
                    <option v-for="(payment_method_title, payment_method_key) in paymentMethods" :value="payment_method_key">
                      {{ payment_method_title }}
                    </option>
                  </select>
                  <span class="error-tip" v-if="errors.payment_method">{{ errors.payment_method }}</span>
                </div>
                <div class="form-group col-4">
                  <label for="payment_status">{{ translations.payment_status }}</label>
                  <select id="payment_status" v-model="appointment.payment_status" :class="{error:errors.payment_status}">
                    <option v-for="(payment_status_title, payment_status) in appointmentStatuses.payment" :value="payment_status">
                      {{ payment_status_title }}
                    </option>
                  </select>
                  <span class="error-tip" v-if="errors.payment_status">{{ errors.payment_status }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="view" v-else-if="viewType == 'appointmentView' && !editView">
            <div class="view-row no-wrap">
              <div class="field-info no-border sm col-3">
                <div class="payment-info customer">
                  {{ appointment.customer_name }}
                </div>
              </div>
              <div class="field-info no-border sm col-2">
                <div class="payment-info">
                  <span class="title">{{ translations.date }}:</span>
                  <span class="value">{{ selectedDate.format('DD/MM/YYYY') }}</span>
                </div>
              </div>
              <div class="field-info no-border sm col-2">
                <div class="payment-info">
                  <span class="title">{{ translations.status }}:</span>
                  <span class="value">{{ translations[appointment.payment_status] }}</span>
                </div>
              </div>
              <div class="field-info no-border sm col-3">
                <div class="payment-info">
                  <span class="title upper">{{ translations.payment_method }}: </span>
                  <span class="value" v-if="translations[appointment.payment_method] !== undefined">
                    {{ translations[appointment.payment_method] }}
                  </span>
                  <span class="value" v-else>
                    {{ appointment.payment_method }}
                  </span>
                </div>
              </div>
              <div class="field-info no-border sm col-2">
                <div class="payment-info">
                  <span class="title upper">{{ translations.total }}:</span>
                  <span class="value">
                    <span v-if="settings.currency_position === 'left'">{{ settings.currency_symbol }}</span>
                    {{ appointment.price }}
                    <span v-if="settings.currency_position === 'right'">{{ settings.currency_symbol }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  data: () => ({
    accordion: {
      details: true,
      customer:false,
      payment: false,
    },
    editCustomer: false,
    editPayment: false,
    isGoogleBusyTime: false,
    selectedServiceID: null,
    selectedStaffID: null,
    selectedDate: null,
    selectedTimeStart: null,
    selectedTimeEnd: null,
    translations: bookit_window.translations,
  }),
  props: {
    appointment: {
      type: Object,
      required: true
    },
    appointmentStatuses: {
      type: Object,
      required: true,
    },
    disabledTimeSlots: {
      type: Array,
      required: true,
    },
    loading: {
      type: Boolean,
      required: false,
      default:  true,
    },
    paymentMethods: {
      type: Object,
      required: true,
    },
    services: {
      type: Array,
      required: true,
    },
    settings: {
      type: Object,
      required: true,
    },
    staff: {
      type: Array,
      required: true,
    },
    timeSlotList: {
      type: Array,
      required: true,
    },
    viewType: {
      type: String,
      required: false,
    }
  },
  computed: {
    availableStaff () {
      let staff = [...this.staff];
      return staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == this.selectedServiceID )
      });
    },
    editView: {
      get() {
        return this.$store.getters.getEditView;
      },
      set( editView ) {
        this.$store.commit('setEditView', editView);
      }
    },
    errors() {
      return this.$store.getters.getErrors;
    },
    selectedService () {
      return this.services.find((service) => service.id == this.selectedServiceID);
    },
    selectedStaff () {
      return this.staff.find((staff) => staff.id == this.selectedStaffID);
    },
    staffTimeSlots() {

      if ( this.selectedStaff !== undefined && this.selectedService !== undefined ) {
        const workingHours = this.selectedStaff.working_hours.find(
          wh => wh.weekday === this.selectedDate.isoWeekday()
        );
        const selectedStaffDisabledTimeSlots = this.disabledTimeSlots.filter(
            dts => parseInt(dts.staff_id) === parseInt(this.selectedStaff.id)
        );
        let timeSlots = this.getSeparateTimeSlots(
          this.appointment.id,
          workingHours,
          this.timeSlotList,
          selectedStaffDisabledTimeSlots,
          this.selectedService.duration,
          this.selectedDate
        );
        if ( timeSlots.start.length > 0 ) {
          let s_index = timeSlots.start.findIndex((ts) => this.strTimeToMoment(ts.value, true) == this.appointment.start_time) || 0;
          if ( s_index < 0 ) s_index = 0;
          this.selectedTimeStart = timeSlots.start[s_index].value;

          let e_index = timeSlots.end.findIndex((ts) => this.strTimeToMoment(ts.value, true) == this.appointment.end_time) || 0;
          if ( e_index < 0 ) e_index = 0;
          this.selectedTimeEnd   = timeSlots.end[e_index].value;

          this.$emit('setNotAvailable', false);
        } else {

          this.selectedTimeStart = null;
          this.selectedTimeEnd   = null;
          timeSlots.start.push({ value: null, label: '--' });
          timeSlots.end.push({ value: null, label: '--' });

          this.$emit('setNotAvailable', true);
        }

        this.changeTime( this.selectedTimeStart );
        this.changeTime( this.selectedTimeEnd, 'selectedTimeEnd');
        return timeSlots;

      }else{

        let timeSlots = {'start': [], 'end': []};
        timeSlots.start.push({ value: null, label: '--' });
        timeSlots.end.push({ value: null, label: '--' });
        this.$emit('setNotAvailable', true);

        return timeSlots;
      }
    },

    wpTimeFormat() {
      return this.getWPSettingsTimeFormat();
    },
  },
  created() {
    this.editView = false;

    if ( this.appointment.notes && this.appointment.notes.hasOwnProperty('phone') ) {
      this.appointment.customer_phone = this.appointment.notes.phone;
    }

    if ( this.appointment.notes && this.appointment.notes.hasOwnProperty('comment') ) {
      this.appointment.comment = this.appointment.notes.comment;
    }

    this.selectedServiceID  = this.appointment.service_id;
    this.selectedStaffID    = this.appointment.staff_id;
    this.selectedDate       = this.moment.unix(this.appointment.date_timestamp);
  },
  mounted() {
    /** update row time if service time duration was changed **/
    if (this.viewType == 'edit' && ( this.strTimeToMoment(this.selectedTimeEnd, true) != this.appointment.end_time
        || this.appointment.start_time != this.strTimeToMoment(this.selectedTimeStart, true) ) ){
      this.appointment.end_time   = this.strTimeToMoment(this.selectedTimeEnd, true);
      this.appointment.start_time = this.strTimeToMoment(this.selectedTimeStart, true);
    }
  },
  methods: {
    setEditView( ) {
      this.editView = !this.editView;
    },
    /** accordion methods **/
    errorsInAccordion() {
      var accordionErrors = {
        'details' :['dates', 'staff_id', 'service_id', 'staff_service'],
        'payment' :['payment_status', 'payment_method'],
        'customer' : ['customer_phone', 'customer_id'],
      };
      var result = {};

      Object.keys(accordionErrors).forEach((key) => {
        for (var i = 0; i < accordionErrors[key].length; i ++ ) {
          if( this.errors.hasOwnProperty(accordionErrors[key][i]) ) {
            result[key] = true;
          }
        }
      });

      /** firstly show details accordion if errors for this part exist **/
      if ( result.hasOwnProperty('details') && !this.accordion.details ) {
        this.switchAccordion('details', this.accordion.details);
      }
      if ( result.hasOwnProperty('customer') && !result.hasOwnProperty('details') && !this.accordion.customer) {
        this.switchAccordion('customer', this.accordion.customer);
      }
      if ( result.hasOwnProperty('payment') && !result.hasOwnProperty('details') && !result.hasOwnProperty('customer') && !this.accordion.payment) {
        this.switchAccordion('payment', this.accordion.payment);
      }
    },
    switchAccordion( key, value ) {
      Object.keys(this.accordion).forEach(i => this.accordion[i] = false);
      this.accordion[key] = !value;
    },
    /** accordion methods | end **/

    /** change handle methods **/
    handleChangeService( event ) {
      this.selectedServiceID = event.target.value;

      /** is exist staff for choosen service **/
      var selectedStaffList   = this.staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == event.target.value )
      });

      if ( selectedStaffList.length > 0 ) {
        var selectedStaff = selectedStaffList[0];

        if ( this.appointment.hasOwnProperty('staff_id') && this.appointment.staff_id != null ) {
          var existInAvailable = selectedStaffList.filter( staff => { return staff.id == this.appointment.staff_id; });
          if ( existInAvailable.length > 0 ) {
            selectedStaff = existInAvailable[0];
          }
        }
      }

      /** remove staff data from appointment if no staff for choosen service **/
      if ( !selectedStaff || selectedStaff === undefined ) {
        this.appointment.staff_id = null;
        // delete this.appointment.price;
        return;
      }

      this.selectedTimeStart          = null;
      if ( this.appointment.hasOwnProperty('start_time') ) {
        this.selectedTimeStart  = this.moment.unix(this.appointment.start_time).format('HH:mm:ss');
      }

      this.appointment.staff_id       = '';
      this.appointment.service_name   = this.selectedService.title;
    },
    handleChangeStaff( event ) {
      if ( !event.target.value ) {
        return;
      }
      this.selectedStaffID        = event.target.value;
      this.appointment.staff_id   = this.selectedStaff.id;
      this.appointment.staff_name = this.selectedStaff.full_name;
      this.appointment.price      = this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedServiceID).price;

      var errors = this.errors;
      if ( errors.hasOwnProperty(event.target.name) ) {
        delete errors[event.target.name];
        this.$store.commit('setErrors', errors);
      }
    },
    handleChangeTime( event ) {
      this.changeTime( event.target.value, event.target.name );
    },
    handleChangeDate( event ) {
      this.selectedDate       = this.moment(event.target.value, 'YYYY-MM-DD').startOf('day');
      this.appointment.date_timestamp = this.selectedDate.unix();

      this.$emit('disableTimeSlots');
      this.changeTime( this.selectedTimeStart );
    },
    changeTime( value, name ) {
      /** Update start if changed end or end if changed start **/

      if ( !value || value === undefined ) {
        this.selectedTimeEnd      = null;
        this.selectedTimeStart    = null;
        return;
      }
      if ( name == 'selectedTimeEnd' ) {

        this.appointment.end_time       = this.strTimeToMoment(value, true);
        this.appointment.start_time     = this.strTimeToMoment(value).subtract(this.selectedService.duration, 'seconds').unix();
        this.selectedTimeStart  = this.strTimeToMoment(value).subtract(this.selectedService.duration, 'seconds').format('HH:mm:ss');
      }else{

        this.appointment.end_time       = this.strTimeToMoment(value).add(this.selectedService.duration, 'seconds').unix();
        this.appointment.start_time     = this.strTimeToMoment(value, true);
        this.selectedTimeEnd    = this.strTimeToMoment(value).add(this.selectedService.duration, 'seconds').format('HH:mm:ss');
      }

      var errors = this.errors;
      if ( errors.hasOwnProperty('dates') ) {
        delete errors['dates'];
        this.$store.commit('setErrors', errors);
      }
    },
    /** change handle methods | end **/

    strTimeToMoment( str_time, unix = false ) {
      let moment_time = this.moment(`${this.selectedDate.format('YYYY-MM-DD')} ${str_time}`);
      if ( unix ) moment_time = moment_time.unix();
      return moment_time;
    },
  },
  watch: {
    errors() {
      this.errorsInAccordion();
    },
    staffTimeSlots() {
      if ( this.staffTimeSlots.hasOwnProperty('busy_by_google') ) {
        this.isGoogleBusyTime = this.staffTimeSlots.busy_by_google.includes(this.selectedTimeStart);
      }else{
        this.isGoogleBusyTime = false;
      }
    },
    selectedStaff() {
      this.$emit('disableTimeSlots');
    },
  }
}