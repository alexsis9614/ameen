  export default {
  name: 'stepService',
  template: `
    <ul class="step-service">
    <li @click="selectService( service )" v-for="service in services" :key="service.id" :class="{'active': service.id == appointment.service_id}">
        <div class="service-icon" v-if="service.icon_url">
          <img v-if="service.icon_url" :src="service.icon_url" class="service-icon" :alt="service.title">
        </div>
        
        <div class="info">
          <span class="title">{{ service.title }}</span>
        </div>
        <span class="selected-icon" v-if="service.id == appointment.service_id"></span>
      </li>
    </ul>`,
  components: {
  },
  data: () => ({
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
    navigation: {
      get() {
        return this.$store.getters.getStepNavigation;
      },
      set( navigation ) {
        this.$store.commit('setStepNavigation', navigation);
      }
    },
    selectedService: {
      get() {
        return this.$store.getters.getSelectedService;
      },
      set( service ) {
        this.$store.commit('setSelectedService', service);
      }
    },
    selectedCategory() {
      return this.$store.getters.getSelectedCategory;
    },
    selectedStaff: {
      get() {
        return this.$store.getters.getSelectedStaff;
      },
      set( staff ) {
        this.$store.commit('setSelectedStaff', staff);
      }
    },
    services() {
      let services = this.$store.getters.getServices;
      return services.filter( item => ( parseInt(item.category_id) === parseInt(this.selectedCategory) || ( !item.category_id && this.selectedCategory === false ) ));
    },
    settings () {
      return this.$store.getters.getSettings;
    },
  },
  created() {
  },
  methods: {
    getStaffPricesForService( service ) {
      let staff     = [...this.$store.getters.getStaff];
      staff         = staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == service.id )
      });

      var prices = [];
      if ( staff.length > 0 ) {
        staff.forEach((staff) => {
          staff.staff_services.filter( staff_service => {
            if ( staff_service.id == service.id ) {
              prices.push(staff_service.price);
            }
          });
        })
      }
      return prices;
    },

    isServicePriceEqualForAllStaff ( service ) {
      /** check if setting not exist and disabled **/
      if ( !this.settings.hasOwnProperty('hide_from_for_equal_service_price')
          || this.settings.hide_from_for_equal_service_price === false ){
        return false;
      }

      /** if enabled , check is equal price **/
      let prices = this.getStaffPricesForService(service);
      return prices.every( (val, i, arr) => val === prices[0] );
    },

    selectService( service ) {
      this.selectedService = service;
      this.$store.commit('setSelectedService', this.selectedService);

      // var paymentStep = this.navigation.find(step => step.key === 'payment');
      // paymentStep.class = '';

      if ( this.appointment.service_id != this.selectedService.id ){
        var appointment = {};
        appointment.category_id = this.appointment.category_id;
        appointment.service_id  = this.selectedService.id;
        this.appointment = appointment;

        this.selectedStaff = {};
      }
      /** go to next step **/
      this.$store.commit('setCurrentStepKey', 'dateTime');
    },
  },
}