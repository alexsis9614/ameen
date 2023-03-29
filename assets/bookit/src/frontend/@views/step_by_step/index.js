import category from '@components/step_by_step/sections/step-category'
import confirmation from '@components/step_by_step/sections/step-confirmation'
import dateTime from '@components/step_by_step/sections/step-datetime'
import detailsForm from '@components/step_by_step/sections/step-details'
import footerNavigation from '@components/step_by_step/sections/footer-navigation'
import mobileFooterNavigation from '@components/step_by_step/sections/mobile/footer-navigation'
import mobileNavigation from '@components/step_by_step/sections/mobile/navigation'
import navigation from '@components/step_by_step/sections/navigation'
import payment from '@components/step_by_step/sections/step-payment'
import result from '@components/step_by_step/sections/book-result'
import result_header from '@components/step_by_step/sections/result-header'
import service from '@components/step_by_step/sections/step-service'

export default {
    name: 'step_by_step',
    template: `
    <div :class="['step-by-step-view', {'small-block': ( (!isMobile() && !isTablet() ) && parseInt(minNormalWidth) > parseInt(parentBlockWidth) )}]">
        <!-- HEADER PART -->
        <div class="calendar-header-navbar">
          <div v-if="isMobile()" class="navbar">
            <mobileNavigation v-if="currentStepKey != 'result'" v-on:previousStep="previousStep" v-on:nextStep="nextStep" ></mobileNavigation>
            <result_header v-else-if="currentStepKey == 'result'"></result_header>
          </div>
          <div v-else >

            <div class="navbar" v-if="currentStepKey != 'result'">
              <div :class="['prev-step', {'disabled': isDisabled}]" :disabled="isDisabled" @click="previousStep" v-if="( currentStepIndex != 0 && currentStepKey != 'result' )">
                <i class="left-icon"></i>
              </div>
              <navigation :isSmallParent="( !isTablet() &&  parseInt(minNormalWidth) > parseInt(parentBlockWidth) )" :isDisabled="isDisabled"></navigation>
              <button v-if="!['result', 'category', 'service'].includes(currentStepKey) && !isMobile()" @click="nextStep" :class="['right', {'disabled': isDisabled}]" :disabled="isDisabled">
                {{ translations.continue }}<i class="right-icon"></i>
              </button>
            </div>
            
            <result_header v-else-if="currentStepKey == 'result'"></result_header>
          </div>
        </div>
        <!-- HEADER PART END -->
        
        <div :class="['calendar-content',{'no-border': showDateTime || currentStepKey == 'result'}, {'no-footer': currentStepKey != 'result'} ]">
          <div v-if="loading" class="loader">
            <div class="loading"><div v-for="n in 9"></div></div>
          </div>
          <div :class="['step-content', currentStepKey, {'no-padding': showDateTime}, {'pb-40': currentStepKey == 'confirmation' } ]">
            <component :attributes="attributes" v-on:setDisabledTimeSlots="setDisabledTimeSlots" :is="currentStepKey"></component>
            <div v-if="appointment.payment_method == 'stripe' && currentStepKey == 'confirmation'" class="stripe-card" ref="stripe_card"></div>
          </div>
        </div>

        <div v-if="isMobile()">
          <mobileFooterNavigation v-on:nextStep="nextStep" v-on:newBooking="newBooking" :navigation="navigation"></mobileFooterNavigation>
        </div>
        <div v-else>
          <div v-if="currentStepKey == 'result'" class="calendar-footer">
            <footerNavigation v-on:newBooking="newBooking" :attributes="attributes" :currentStepKey="currentStepKey"></footerNavigation>
          </div>
        </div>
    </div>
  `,
    components: {
      category,
      confirmation,
      footerNavigation,
      dateTime,
      detailsForm,
      mobileFooterNavigation,
      mobileNavigation,
      navigation,
      payment,
      result,
      result_header,
      service,
    },
    data: () => ({
      isDisabled: false,
      minNormalWidth: 850,
      stripe: {
        stripe: '',
        elements: '',
        card: '',
        client_secret: ''
      },
      translations: bookit_window.translations
    }),
    props: {
      attributes: {
        type: Object,
        required: false
      },
      navigation: {
            type: Array,
            required: true,
            default: {}
        },
    },
    created () {
      this.setCorrectStepByAttributes();

      /** set data to store **/
      this.$store.commit('setStepNavigation', this.stepNavigation);

        /** set today by default at first **/
      if ( !this.appointment.date_timestamp ) {
        var appointment            = Object.assign({}, this.appointment);
        appointment.date_timestamp = this.moment().startOf('day').unix();
        this.appointment           = appointment;
        this.setDisabledTimeSlots();
      }
    },
    computed: {
      allServices() {
        return this.$store.getters.getServices;
      },
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
      currentStepIndex() {
        return this.navigation.findIndex(step => step.key === this.currentStepKey);
      },
      currentStepKey() {
        return this.$store.getters.getCurrentStepKey;
      },
      currentStep() {
        return this.navigation.filter( step => {
            return step.key == this.currentStepKey
        })[0];
      },
      parentBlockWidth() {
        return this.$store.getters.getParentBlockWidth;
      },
      errors: {
        get() {
          return this.$store.getters.getErrors;
        },
        set( errors ) {
          this.$store.commit('setErrors', errors);
        }
      },
      loading: {
        get() {
          return this.$store.getters.getLoading;
        },
        set( appointment ) {
          this.$store.commit('setLoading', appointment);
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
        if ( this.selectedStaff && this.selectedService ) {
          return this.getStaffPrice(this.selectedStaff, this.selectedService, this.settings);
        }
      },
      showDateTime() {
        return this.$store.getters.getShowDateTime;
      },
      stepNavigation() {
        return this.navigation.filter( step => {
            return step.key != 'result'
        });
      },
      user:  {
        get() {
          return this.$store.getters.getUser;
        },
        set( user ) {
          this.$store.commit('setUser', user);
        }
      },
    },
    methods: {
      /** Remove category step if just 1 category exist or category id in shortcode
       * remove service step if just 1 service and 1 category exists | or service id in shortcode **/
      setCorrectStepByAttributes() {
        if ( this.categories.length <= 1 || ( this.attributes.hasOwnProperty('category_id') && this.attributes.category_id !== null ) ) {

          if ( this.categories.length <= 0 ){ this.categories.push({id: false})}
          var appointment = {'category_id': this.categories[0].id};
          var step        = 'service';

          this.$store.commit('setSelectedCategory', this.categories[0].id);

          var categoryServices = this.allServices.filter( item => ( parseInt(item.category_id) === parseInt(this.categories[0].id) ) || ( !item.category_id && this.categories[0].id === false ) );
          /** not show service step for single service exist or service attr exist **/
          if ( categoryServices.length <= 1 || ( this.attributes.hasOwnProperty('service_id') && this.attributes.service_id !== null ) ) {

            var service  = ( this.attributes.hasOwnProperty('service_id') && this.attributes.service_id !== null ) ? this.allServices.filter( item => parseInt(item.id) === parseInt(this.attributes.service_id) )[0]: categoryServices[0];
            this.$store.commit('setSelectedService', service);
            appointment.service_id = service.id;
            step                   = 'dateTime';
          }

          this.$store.commit('setAppointment', appointment);
          this.$store.commit('setCurrentStepKey', step);
        }
      },
      async bookNow() {
        this.loading    = true;
        this.isDisabled = true;
        let data = {
          nonce: (this.appointment.nonce !== null && this.appointment.nonce != undefined ) ? this.appointment.nonce : bookit_window.nonces.bookit_book_appointment,
          full_name: this.appointment.full_name,
          email: this.appointment.email,
          phone: ( this.appointment.phone !== undefined ) ? this.appointment.phone: '',
          password: this.appointment.password,
          password_confirmation: this.appointment.password_confirmation,
          staff_id: this.appointment.staff_id,
          comment: (typeof this.appointment.comment !== 'undefined') ? this.appointment.comment: '',
          service_id: this.appointment.service_id,
          price: this.staffPrice,
          clear_price: this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedService.id).price,
          user_id: this.appointment.user_id,
          date_timestamp: this.appointment.date_timestamp,
          start_time: this.appointment.start_time,
          end_time: this.appointment.end_time,
          payment_method: this. appointment.payment_method,
          token: '',
        };

        var errors = {};
        if ( this.appointment.payment_method === 'stripe' ) {
          await this.stripe.stripe.createPaymentMethod('card', this.stripe.card)
              .then(async (result) => {
                if ( result.error !== undefined && result.error.message !== undefined ) {
                  errors.stripe = result.error.message;
                } else {
                  let payment_data = {
                    nonce: bookit_window.nonces.bookit_book_appointment,
                    total: data.clear_price,
                    payment_method_id: result.paymentMethod.id
                  };

                  await this.axios.post(`${bookit_window.ajax_url}?action=bookit_stripe_intent_payment`, this.generateFormData(payment_data), this.getPostHeaders())
                      .then(async (ajax_res) => {
                        let response = ajax_res.data;
                        if ( response.success ) {
                          if ( response.data.requires_action ) {
                            // Card requires Auhentication
                            await this.handleStripeCard(response.data, data.clear_price);
                          } else {
                            // Order Complete
                            this.stripe.client_secret = response.data.client_secret;
                          }
                        } else {
                          errors.stripe   = response.data.message;
                          this.stripe.client_secret = false;
                        }
                      });

                  if ( this.stripe.client_secret ) {
                    await this.stripe.stripe.retrievePaymentIntent(this.stripe.client_secret)
                        .then((retrieve_result) => {
                          data.token = retrieve_result.paymentIntent.id
                        });
                  }
                }
              });
        }
        if ( Object.keys(errors).length > 0 ) {
          this.errors = errors;
          this.loading    = false;
          this.isDisabled = false;
          return false;
        }

        await this.axios.post(`${bookit_window.ajax_url}?action=bookit_book_appointment`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
          let response = res.data;

          if ( response.success ) {
            var appointment          = Object.assign({}, this.appointment);
            appointment.user_id      = response.data.customer.wp_user_id;
            appointment.nonce        = response.data.nonce;
            appointment.redirect_url = response.data.redirect_url;
            appointment.price        = response.data.appointment.price;

            this.appointment = appointment;

            if( (this.user === null || this.user === undefined) && this.settings.booking_type === 'registered' ){
              this.user = {
                ID:  response.data.customer.wp_user_id,
                display_name: response.data.customer.full_name,
                user_email: response.data.customer.email,
                nonce: response.data.nonce,
              };
            }

            this.$store.commit('setCurrentStepKey', 'result');
          } else if (response.data.errors && Object.keys(response.data.errors).length > 0){
            this.errors = response.data.errors;
          }
        });
        this.loading    = false;
        this.isDisabled = false;
      },
      async setDisabledTimeSlots() {
        const data = {
            nonce: bookit_window.nonces.bookit_day_appointments,
            date_timestamp: this.appointment.date_timestamp
        };
        if ( this.appointment.staff_id ){
          data['staff_id'] = this.appointment.staff_id;
        }
        await this.axios.post(`${bookit_window.ajax_url}?action=bookit_day_appointments`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
            let response = res.data;
            if ( response.success ) {
              this.$store.commit('setDisabledTimeSlots', response.data);
            }
        });
      },
      generateStripe() {
        this.$nextTick( () => {
          const stripe_id = this.payment_methods.stripe.publish_key;

          if ( ! stripe_id.length ) {
            var errors = {};
            errors.stripe = bookit_window.translations.something_went_wrong;
            this.errors   = errors;
            return false;
          }

          this.stripe.stripe    = Stripe(stripe_id);
          this.stripe.elements  = this.stripe.stripe.elements();
          this.stripe.card      = this.stripe.elements.create('card');

          this.stripe.card.mount(this.$refs.stripe_card);
        });
      },
      nextStep() {
        if ( this.isDisabled ) {
          return;
        }

        this.validation( true );

        if ( this.currentStepKey == 'confirmation') {
          this.bookNow();

        }else{
          /** go to next step **/
          var currentStepIndex = this.navigation.findIndex(step => step.key === this.currentStepKey);

          if (this.navigation.hasOwnProperty(currentStepIndex + 1)
              && this.isArrayItemsInArray(this.navigation[currentStepIndex + 1].requiredFields, Object.keys(this.appointment))
              && ( Object.keys(this.errors).length === 0 )
          ){
            var nextStep = this.navigation[currentStepIndex + 1];

            /** skip payment step if service price is zero **/
            if ( nextStep.key == 'payment' && parseFloat( this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedService.id).price ) == 0 ) {
              nextStep = this.navigation[this.navigation.findIndex(step => step.key === 'confirmation')];
            }
            this.$store.commit('setCurrentStepKey', nextStep.key);

          } else if (this.navigation.hasOwnProperty(currentStepIndex + 1) && this.currentStepKey != 'detailsForm' ) {

            var errors = {};
            this.navigation[currentStepIndex + 1].requiredFields.forEach( requiredField => {
              if ( this.translations.hasOwnProperty('error_' + requiredField) && !Object.keys(this.appointment).includes(requiredField)) {
                errors[requiredField] = this.translations['error_' + requiredField];
              }
            });
            this.errors = errors;
          }
        }
      },
      newBooking() {
        var appointment = {};
        if ( this.categories.length <= 1 || ( this.attributes.hasOwnProperty('category_id') && this.attributes.category_id !== null ) ) {

          if ( this.categories.length <= 0 ){ this.categories.push({id: false})}

          appointment = {'category_id': this.categories[0].id};
          var step        = 'service';

          this.$store.commit('setSelectedCategory', this.categories[0].id);

          var categoryServices = this.allServices.filter( item => ( parseInt(item.category_id) === parseInt(this.categories[0].id) ) || ( !item.category_id && this.categories[0].id === false ) );
          /** not show service step for single service exist or service attr exist **/
          if ( categoryServices.length <= 1 || ( this.attributes.hasOwnProperty('service_id') && this.attributes.service_id !== null ) ) {

            var service  = ( this.attributes.hasOwnProperty('service_id') && this.attributes.service_id !== null ) ? this.allServices.filter( item => parseInt(item.id) === parseInt(this.attributes.service_id) )[0]: categoryServices[0];
            this.$store.commit('setSelectedService', service);
            appointment.service_id = service.id;
            step                   = 'dateTime';
          }

          this.$store.commit('setAppointment', appointment);
          this.$store.commit('setCurrentStepKey', step);
        }else{
          this.$store.commit('setCurrentStepKey', 'category');
          this.$store.commit('setSelectedService', null);
        }

        this.appointment = appointment;
        this.$store.commit('setSelectedStaff', null)
      },
      previousStep() {
        if ( this.isDisabled ) {
          return;
        }

        this.validation();

        var currentStepIndex = this.navigation.findIndex(step => step.key === this.currentStepKey);
        if (this.navigation.hasOwnProperty(currentStepIndex - 1)
            && this.isArrayItemsInArray(this.navigation[currentStepIndex - 1].requiredFields, Object.keys(this.appointment) )
            && ( Object.keys(this.errors).length === 0 )
        ){

          var prevStep = this.navigation[currentStepIndex - 1];
          /** skip payment step if service price is zero **/
          if ( prevStep.key == 'payment' && parseFloat( this.selectedStaff.staff_services.find(staff_service => staff_service.id == this.selectedService.id).price ) == 0 ) {
            prevStep = this.navigation[this.navigation.findIndex(step => step.key === 'detailsForm')];
          }

          this.$store.commit('setCurrentStepKey', prevStep.key);
        }
      },
      validation( next = false ) {
        this.$store.commit('setErrors', {});
        var currentStep = this.navigation.find(step => step.key === this.currentStepKey);

        if ( currentStep.validation && next == true ){
          if ( this.currentStepKey == 'detailsForm' || this.currentStepKey == 'confirmation') {
            var errors = this.formValidation( this.appointment, this.settings );
            this.errors = errors;
          }
        }
      },
    },
    watch: {
      currentStepKey(value) {
        if ( value == 'confirmation' && this.appointment.payment_method == 'stripe' ) {
          this.generateStripe();
        }
      },
      selectedStaff () {
        var appointment = Object.assign({}, this.appointment);
        let staffPrice  = 0;
        if ( this.selectedStaff == null ) {
          var paymentStep   = this.stepNavigation.find(step => step.key === 'payment');
          paymentStep.class = '';
          this.$store.commit('setStepNavigation', this.stepNavigation);
        }
      },
    }
}