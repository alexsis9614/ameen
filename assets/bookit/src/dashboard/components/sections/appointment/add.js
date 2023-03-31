import customerAutocomplete from '@dashboard-sections/customer/autocomplete';

export default {
  template: `
    <div class="bookit-modal-body position-relative appointment-form">
      <div v-if="loading" class="loader">
        <div class="loading"><div v-for="n in 9"></div></div>
      </div>
      <div :class="['accordion', {active: accordion.details}]">
        <a class="accordion-title" @click="switchAccordion('details', accordion.details)">{{ translations.details }} <span class="float-right accordion-icon"></span></a>
        <div v-show="accordion.details" class="accordion-body">
          <div class="bookit-row normal">
            <div class="form-group col-3">
              <label for="service">{{ translations.service }}</label>
              <select name="service_id" v-model="appointment.service_id" @change="handleChangeService($event)" :class="{error:errors.service_id}">
                <option v-if="services.length == 0" value="null">{{ translations.no }} {{ translations.service }}</option>
                <option v-else value="" disabled hidden>{{ translations.select }} {{ translations.service }}</option>
                <option v-for="service in services" :value="service.id">{{ service.title }}</option>
              </select>
              <span class="error-tip" v-if="errors.service_id">{{ errors.service_id }}</span>
            </div>
            <div class="form-group col-3">
              <label for="staff">{{ translations.staff }}</label>
              <select name="staff_id" v-model="appointment.staff_id" @change="handleChangeStaff($event)" 
                      :class="{error:errors.staff_id || errors.staff_service}" 
                      :disabled="calendarType == 'day'">
                <option v-if="(availableStaff.length == 0 && ( selectedServiceID !== null )) || services.length == 0" value="null">{{ translations.no }} {{ translations.staff }}</option>
                <option v-else value="">{{ translations.select }} {{ translations.staff }}</option>

                <option v-if="availableStaff.length > 0" v-for="staff in availableStaff" :value="staff.id">{{ staff.full_name }}</option>
              </select>
              <span class="error-tip" v-if="errors.staff_id">{{ errors.staff_id }}</span>
              <span class="error-tip" v-if="errors.staff_service">{{ errors.staff_service }}</span>
            </div>
          </div>
          <div class="bookit-row normal">
            <div class="form-group col-3">
              <label for="date">{{ translations.date }}</label>

              <input name="date" type="date" :class="{error:errors.date_timestamp}" :value="selectedDate | moment('YYYY-MM-DD')" :min="moment().startOf('day') | moment('YYYY-MM-DD')" @change="handleChangeDate($event)">
              <span class="error-tip" v-if="errors.date_timestamp">{{ errors.date_timestamp }}</span>
              <span class="info-tip width-lower" v-if="isGoogleBusyTime" > {{ translations.gc_busy_event_info }}</span> 
            </div>
            <div class="form-group col-3">
              <label for="time">{{ translations.time }}</label>
              <div class="appointment-time">
                <select name="selectedTimeStart" :class="['start-time',{error:errors.dates}]"  v-model="selectedTimeStart" @change="handleChangeTime($event)">
                  <option v-for="slot_start in staffTimeSlots.start" :value="slot_start.value">{{ slot_start.label }}</option>
                </select>
                <select name="selectedTimeEnd"  :class="['end-time',{error:errors.dates}]" v-model="selectedTimeEnd" @change="handleChangeTime($event)">
                  <option v-for="slot_end in staffTimeSlots.end" :value="slot_end.value">{{ slot_end.label }}</option>
                </select>
              </div>
              <span class="error-tip" v-if="errors.dates">{{ errors.dates }}</span>
            </div>
          </div>
          <div class="bookit-row normal">
            <div class="form-group">
              <label for="status">{{ translations.comment }}</label>
              <input name="comment" type="text" v-model="appointment.comment" :placeholder="translations.comment_placeholder">
            </div>
          </div>
        </div>
      </div>
      <div :class="['accordion', {active: accordion.customer}]">
        <a class="accordion-title" @click="switchAccordion('customer', accordion.customer)">{{ translations.customer }} <span class="float-right accordion-icon"></span></a>
        
        <div v-show="accordion.customer" class="accordion-body">

          <!-- Create new customer form-->
          <div v-if="createNewCustomer">
            <div class="bookit-row" >
              <div class="form-group col-3">
                <label for="full_name">{{ translations.full_name }}</label>
                <input :placeholder="translations.customer_name" v-on:input="clearCustomerError($event)" type="text" name="full_name" v-model="customer.full_name" :class="{error:errors.full_name}" >
                <span class="error-tip" v-if="errors.full_name" v-html="errors.full_name"></span>
              </div>
              <div class="form-group col-3">
                <label for="email">{{ translations.email }}</label>
                <input :placeholder="translations.email" v-on:input="clearCustomerError($event)" type="email" name="email" v-model="customer.email" :class="{error:errors.email}" >
                <span class="error-tip" v-if="errors.email" v-html="errors.email"></span>
              </div>
              <div class="form-group col-3">
                <label for="phone">{{ translations.phone }}</label>
                <input :placeholder="translations.phone" v-on:input="clearCustomerError($event)" type="text" name="phone" v-model="customer.phone" :class="{error:errors.phone}">
                <span class="error-tip" v-if="errors.phone" v-html="errors.phone"></span>
              </div>
            </div>
            <div class="bookit-row">
              <div class="form-group col-3" v-if="settings.booking_type === 'registered'">
                <label for="password">{{ translations.password }}</label>
                <input :placeholder="translations.password_placeholder" v-on:input="clearCustomerError($event)" name="password" type="password" v-model="customer.password" :class="{error:errors.password}">
                <span class="error-tip" v-if="errors.password" v-html="errors.password "></span>
              </div>
              <div class="form-group col-3" v-if="settings.booking_type === 'registered'">
                <label for="password_confirmation">{{ translations.password_confirmation }}</label>
                <input :placeholder="translations.repeat_password_placeholder" v-on:input="clearCustomerError($event)" name="password_confirmation" type="password" v-model="customer.password_confirmation" :class="{error:errors.password_confirmation}">
                <span class="error-tip" v-if="errors.password_confirmation" v-html="errors.password_confirmation"></span>
              </div>
              <div :class="['form-group', 'col-3', 'customer-action', {'registered': settings.booking_type === 'registered' }]">
                <button type="button" class="add-customer-btn" @click="createCustomer();">
                  <i class="add-icon"></i> {{ translations.add }}
                </button>
                <button type="button" class="cancel-btn" @click="createNewCustomer = !createNewCustomer;">
                  {{ translations.cancel }}
                </button>
              </div>
            </div>
          </div>
          <!-- Create new customer form | end -->

          <!--Find from exist ones customers-->
          <div class="bookit-row" v-else >
            <div class="form-group" >
              <label for="customer">{{ translations.customer }}</label>
              <div class="appointment-customer">
                <customerAutocomplete :customer_id="appointment.customer_id" :error="errors.customer_id" v-on:setChoosenValue="setCustomerValue" :options="customers" :placeholder="translations.search_placeholder"></customerAutocomplete>
                <button type="button" class="add-customer-btn" @click="createNewCustomer = !createNewCustomer;">
                  <i class="add-icon"></i> {{ translations.new_customer }}
                </button>
              </div>
            </div>
          </div>

          <div class="view" v-if="this.customer.id">
            <div class="view-row">
              <div class="field-info no-border sm col-6">
                <div class="customer-info">
                  {{ appointment.customer_name }}
                </div>
              </div>
              <div class="field-info no-border sm col-6">
                <div class="appointment-action">
                  
                  <div :class="['custom-select', { 'open': isOpenAppointmentStatus } ]" @click="isOpenAppointmentStatus = !isOpenAppointmentStatus;" >
                    <div class="value">
                      <i :class="appointment.status"></i>
                      {{  translations[appointment.status] }}
                    </div>
                    <div :class="['custom-options', { 'open': isOpenAppointmentStatus } ]">
                      <span v-if="appointment.status != status" @click="handleChangeStatus(status)" class="custom-option" v-for="(status_title, status) in appointmentStatuses.appointment" :value="status"> 
                        <i :class="status"></i>
                        {{ status_title }}
                      </span>
                    </div>
                  </div>
                  <span :class="['edit-arrow-icon', {'down': !editCustomer}, {'up': editCustomer}]" @click="editCustomer = !editCustomer"></span>
                </div>
              </div>
              <div v-if='editCustomer' class="field-info col-12">
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
          <!--Find from exist ones customers | end-->
        </div>
      </div>
      
      <div :class="['accordion', {active: accordion.payment}]">
        <a class="accordion-title" @click="switchAccordion('payment', accordion.payment)">{{ translations.payment }} <span class="float-right accordion-icon"></span></a>
        <div v-show="accordion.payment" class="accordion-body">
          <div class="view" v-if="this.customer.id && appointment.service_id && appointment.staff_id">
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
              <div v-if='editPayment' class="field-info col-12">
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
                    <option v-for="(payment_method_title, payment_method) in paymentMethods" :value="payment_method">
                      {{ payment_method_title }}
                    </option>
                  </select>
                  <span class="error-tip" v-if="errors.payment_method">{{ errors.payment_method }}</span>
                </div>
                <div class="form-group col-4">
                  <label for="payment_status">{{ translations.payment_status }}</label>
                  <select id="payment_status" v-model="appointment.payment_status" :class="{error:errors.payment_status}">
                    <option v-for="(payment_status_title, payment_method_key) in appointmentStatuses.payment" :value="payment_method_key">
                      {{ payment_status_title }}
                    </option>
                  </select>
                  <span class="error-tip" v-if="errors.payment_status">{{ errors.payment_status }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="view notice-info" v-else>
            {{ translations.appointment_payment_notice }}
          </div>
        </div>
      </div>
    </div>
  `,
  components: {
    customerAutocomplete,
  },
  data: () => ({
    accordion: {
      details: true,
      customer:false,
      payment: false,
    },
    calendarType: null,
    createNewCustomer: false,
    customer: {},
    editCustomer: false,
    editPayment: false,
    isGoogleBusyTime: false,
    isOpenAppointmentStatus: false,
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
    settings: {
      type: Object,
      required: true,
    },
    services: {
      type: Array,
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
  },
  computed: {
    availableStaff () {
      let staff = [...this.staff];
      return staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == this.selectedServiceID )
      });
    },
    customers () {
      return this.$store.getters.getCustomers;
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

        if ( this.selectedTimeStart != null ) {
          timeSlots.start.push({ value: this.selectedTimeStart, label: this.moment.unix(this.appointment.start_time).format('HH:mm a') });
        }
        if ( this.selectedTimeEnd != null ) {
          timeSlots.end.push({ value: this.selectedTimeEnd, label: this.moment.unix(this.appointment.end_time).format('HH:mm a') });
        }

        if ( this.selectedTimeEnd == null && this.selectedTimeStart == null && ['week', 'day'].includes(this.calendarType) ) {
          this.$emit('setNotAvailable', true);
        }

        return timeSlots;
      }
    },
  },
  created() {
    this.selectedDate = this.moment.unix(this.appointment.date_timestamp).startOf('day');

    this.appointment.status = 'pending';

    if ( this.services.length == 0 ) {
      this.appointment.service_id = null;
      this.appointment.staff_id   = null;
    }

    /** if day used to disable staff select **/
    if ( this.appointment.hasOwnProperty('calendarType') ) {
      this.calendarType = this.appointment.calendarType;
    }

    if ( this.appointment.hasOwnProperty('start_time') ) {
      this.selectedTimeStart  = this.moment.unix(this.appointment.start_time).format('HH:mm:ss');

      var freeStaff = this.getFreeStaffByStartTime( this.staff, this.services, this.disabledTimeSlots, this.appointment.start_time );
      if ( freeStaff.length == 0 ) {
        this.selectedTimeStart = null;
      }
    }

    /** set staff data and set 1st staff service by default, based on staff **/
    if ( this.appointment.hasOwnProperty('staff_id') && this.appointment.staff_id !== null && this.appointment.staff_id.length > 0) {
      var selectedStaff           =  this.staff.filter(staff => staff.id == this.appointment.staff_id)[0];
      this.selectedStaffID        = selectedStaff.staff_id;
      this.selectedServiceID      = this.services[0].id;
      this.appointment.staff_name = selectedStaff.full_name;
    }
  },

  methods: {
    /** customer functions **/
    setCustomerValue( customer ) {
      this.customer                   = customer;
      this.appointment.customer_name  = customer.full_name;
      this.appointment.customer_id    = customer.id;
      this.appointment.customer_phone = customer.phone;
      this.appointment.customer_email = customer.email;
    },
    clearCustomerError( event ) {
      var errors = this.errors;
      if ( errors.hasOwnProperty(event.target.name) ) {
        delete errors[event.target.name];
        this.$store.commit('setErrors', errors);
      }
    },
    validateCustomer() {
      let errors = {};

      if ( ! this.customer.full_name || this.customer.full_name.length === 0 ) {
        errors.full_name = bookit_window.translations.required_field;
      }

      if ( ! this.checkEmail( this.customer.email ) ) {
        errors.email = bookit_window.translations.invalid_email;
      }

      if ( this.settings.booking_type === 'registered' ) {
        if ( ! this.customer.password || this.customer.password.length === 0 ) {
          errors.password = bookit_window.translations.required_field;
        }
        if ( this.customer.password !== this.customer.password_confirmation ) {
          errors.password_confirmation = bookit_window.translations.confirmation_mismatched;
        }
      }
      return errors;
    },
    createCustomer() {
      this.$store.commit('setErrors', {});
      var errors = this.validateCustomer();

      if (Object.keys(errors).length > 0){
        this.$store.commit('setErrors', errors);
        return;
      }

      this.customer.nonce = bookit_window.nonces.bookit_save_item;
      this.customer.from  = 'calendar';
      this.$emit('setLoading', true);

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_create_customer`, this.generateFormData(this.customer), this.getPostHeaders()).then((res) => {
        let response = res.data;

        if (response.data.errors && Object.keys(response.data.errors).length > 0){
          this.$store.commit('setErrors', response.data.errors);
        }

        if ( response.success ) {
          if ( Object.keys(response.data.customer).length > 0 ) {
            this.setCustomerValue(response.data.customer);
            this.$store.dispatch('addCustomer', response.data.customer);
            this.createNewCustomer  = false;
          }
        }
        this.$emit('setLoading', false);
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    /** customer functions | end **/

    /** change handle methods **/
    handleChangeStaff( event ) {
      if ( !event.target.value ) {
        return;
      }
      this.selectedStaffID          = event.target.value;

      this.appointment.staff_id     = this.selectedStaff.id;
      this.appointment.staff_name   = this.selectedStaff.full_name;
      this.appointment.price        = this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedServiceID).price;

      var errors = this.errors;
      if ( errors.hasOwnProperty(event.target.name) ) {
        delete errors[event.target.name];
        this.$store.commit('setErrors', errors);
      }
    },
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
        delete this.appointment.price;
        return;
      }

      this.selectedTimeStart          = null;
      if ( this.appointment.hasOwnProperty('start_time') ) {
        this.selectedTimeStart  = this.moment.unix(this.appointment.start_time).format('HH:mm:ss');
      }

      this.appointment.staff_id       = '';
      this.appointment.service_name   = this.selectedService.title;
    },
    handleChangeStatus( status ) {
      this.appointment.status = status;
    },
    handleChangeTime( event ) {
      this.changeTime( event.target.value, event.target.name );
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
    async handleChangeDate( event ) {
      this.selectedDate       = this.moment(event.target.value, 'YYYY-MM-DD').startOf('day');
      this.appointment.date_timestamp = this.selectedDate.unix();

      this.$emit('disableTimeSlots');
      this.changeTime( this.selectedTimeStart );
    },
    /** change handle methods | end **/

    /** accordion methods **/
    errorsInAccordion() {

      if ( this.loading ) {
        return;
      }
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
    disabledTimeSlots() {
      this.staffTimeSlots;
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
