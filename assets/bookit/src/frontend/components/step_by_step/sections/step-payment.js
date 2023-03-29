export default {
  name: 'stepPayment',
  template: `
    <div class="payment-methods">
    <div v-if="errors.hasOwnProperty('payment_method')" class="errors">
      <div class="error">
        <span>{{ errors.payment_method }}</span> <i @click="deleteError('payment_method')" class="close-icon"></i>
      </div>
    </div>
      <ul class="step-payment full">
        <li @click="selectPayment( key )" v-for="(item, key) in payment_methods" :key="key" :class="{'active': key == appointment.payment_method}">
          
          <img v-if="isCustomIcon( item ) " :src="item.custom_icon" class="custom-icon">
          <div :class="['icon', key]" v-else ></div>
          
          <span class="title" v-if="item.hasOwnProperty('custom_title') && item.custom_title.length > 1">{{ item.custom_title }}</span>
          <span class="title" v-else>{{ translations[key] }}</span>
          
          <span class="selected-icon" v-if="key == appointment.payment_method"></span>

          <span class="is-pro" v-if="['paypal', 'stripe', 'woocommerce'].includes(key)">
              <span class="pro-tooltip">
                 pro
                 <span  class="pro-tooltiptext">Feature Available <br> in Pro Version</span>
              </span>
          </span>
          
        </li>
      </ul>
    </div>
    `,
  components: {
  },
  data: () => ({
    translations: bookit_window.translations,
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
    categories() {
      return this.$store.getters.getCategories;
    },
    errors: {
      get() {
        return this.$store.getters.getErrors;
      },
      set( errors ) {
        this.$store.commit('setErrors', errors);
      }
    },
    payment_methods () {
      let enabled_payments = {...this.settings.payments};
      Object.keys(enabled_payments).forEach((key) => {
        if ( ( !this.settings.payment_active && !this.settings.pro_active && key !== 'locally' ) || enabled_payments[key].enabled === undefined || enabled_payments[key].enabled === false ) {
          delete enabled_payments[key];
        }
      });
      if ( Object.keys(enabled_payments).length > 0 ) {
        this.payment_method = Object.keys(enabled_payments)[0];
      }
      return enabled_payments;
    },
    settings () {
      return this.$store.getters.getSettings;
    },
  },
  created() {},
  methods: {
    deleteError( errorIndex ) {
      var errors = Object.assign({}, this.errors);
      delete errors[errorIndex];

      this.errors = errors;
    },
    isCustomIcon ( paymentItem ) {
      if ( paymentItem.hasOwnProperty('custom_icon') && paymentItem.custom_icon !== null && paymentItem.custom_icon.length > 0) {
        return true;
      }
      return false;
    },
    selectPayment ( paymentKey ) {
      var appointment = Object.assign({}, this.appointment);
      appointment.payment_method = paymentKey;
      this.appointment           = appointment;

      this.deleteError('payment_method');

      /** go to next step **/
      this.$store.commit('setCurrentStepKey', 'confirmation');
    },
  },
}