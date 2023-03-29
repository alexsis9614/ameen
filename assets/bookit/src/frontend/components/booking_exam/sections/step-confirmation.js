export default {
  name: 'stepConfirmation',
  template: `
    <div class="confirmation">
      <div v-if="Object.keys(errors).length > 0" class="errors">
        <div class="error" v-for="(error, index) in errors">
          <span>{{ error }}</span> <i @click="deleteError(index)" class="close-icon"></i>
        </div>
      </div>
      <p class="date" >
        {{ moment.unix(appointment.date_timestamp).format('DD MMMM YYYY') }}
      </p>
      <p class="time">{{ moment.unix(appointment.start_time).format( wpTimeFormat ) }} — {{ moment.unix(appointment.end_time).format( wpTimeFormat  ) }}</p>
      
      <div class="appointment-info">
        <div class="appointment-detail">
          <div class="detail-icon">
            <span class="icon service"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.service }}:</span>
            <span class="value">{{ selectedService.title }}</span>
          </div>
        </div>
        <div class="appointment-detail">
          <div class="detail-icon">
            <span class="icon user"><i></i></span>
            
          </div>
          <div class="info">
            <span class="title">{{ translations.client_name }}:</span>
            <span class="value">{{ appointment.full_name }}</span>
          </div>
        </div>
      </div>
    </div>
    `,
  components: {},
  data: () => ({
    selectedPayment: 0,
    stripe: {
      stripe: '',
      elements: '',
      card: '',
      client_secret: ''
    },
    translations: bookit_window.translations
  }),
  computed: {
    appointment: {
      get() {
        return this.$store.getters.getAppointment;
      },
      set( appointment ) {
        this.$store.commit('setAppointment', appointment);
      }
    },
    clearPrice() {
      return this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedService.id).price;
    },
    errors: {
      get() {
        return this.$store.getters.getErrors;
      },
      set( errors ) {
        this.$store.commit('setErrors', errors);
      }
    },
    selectedStaff() {
        return this.$store.getters.getSelectedStaff;
    },
    selectedService () {
      return this.$store.getters.getSelectedService;
    },
    settings () {
      return this.$store.getters.getSettings;
    },
    staffPrice() {
      return this.getStaffPrice(this.selectedStaff, this.selectedService, this.settings);
    },
    wpTimeFormat() {
      return this.getWPSettingsTimeFormat();
    },
  },
  created() {
  },
  methods: {
    deleteError( errorIndex ) {
      var errors = Object.assign({}, this.errors);
      delete errors[errorIndex];

      this.errors = errors;
    }
  },
}