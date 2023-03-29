export default {
  template: `
  <transition name="bookit-modal">
    <div class="bookit-modal-mask">
      <div class="bookit-modal-wrapper" @click="close($event)">
          <transition v-if="!success" name="slide">
            <div class="bookit-modal-container" @click.stop>
              <div v-if="loading" class="loader">
                <div class="loading"><div v-for="n in 9"></div></div>
              </div>
              <form @submit.prevent="bookNow">
                <div class="bookit-modal-header">
                  <h3>{{ translations.booking_details }}</h3>
                  <div class="appointment-info bookit-row">
                    <div class="col-2">
                      <div class="text-bold">{{ service.title }} - {{ getStaffPrice(staff, service, settings) }}</div>
                      <div class="text-uppercase">{{ translations.employee }}: {{ staff.full_name }}</div>
                    </div>
                    <div class="col-2 text-right">
                      <div class="text-uppercase">{{ calendarAppointmentsDate | moment(settings.date_format) }}</div>
                      <div class="text-bold">{{ time.format(settings.time_format) }} / {{ calendarAppointmentsDate | moment('dddd') }}</div>
                    </div>
                  </div>
                </div>
      
                <div class="bookit-modal-body">
                  <div class="bookit-row">
                    <div class="form-group">
                      <span class="error-message" v-if="errors.full_name">{{ errors.full_name }}</span>
                      <input type="text" id="bookit_full_name" v-model="full_name" :placeholder="translations.full_name">
                      <label for="bookit_full_name">{{ translations.full_name }}</label>
                      <div v-if="errors.full_name" class="validation-icon has-error-icon"></div>
                      <div v-if="!errors.full_name" class="validation-icon has-success-icon"></div>
                    </div>
                  </div>
                  <div class="bookit-row">
                    <div class="form-group col-2">
                      <span class="error-message" v-if="errors.email">{{ errors.email }}</span>
                      <input type="email" id="bookit_email" v-model="email" :placeholder="translations.email">
                      <label for="bookit_email">{{ translations.email }}</label>
                      <div v-if="errors.email" class="validation-icon has-error-icon"></div>
                      <div v-if="!errors.email" class="validation-icon has-success-icon"></div>
                    </div>
                  </div>
                  <div class="bookit-row">
                    <div class="form-group col-2">
                      <span class="error-message" v-if="errors.phone">{{ errors.phone }}</span>
                      <input type="text" id="bookit_phone" v-model="phone" :placeholder="translations.phone">
                      <label for="bookit_phone">{{ translations.phone }}</label>
                    </div>
                  </div>
                  <div v-if="settings.booking_type == 'registered' && !user_id" class="bookit-row">
                    <div class="form-group col-2">
                      <span class="error-message" v-if="errors.password">{{ errors.password }}</span>
                      <input type="password" id="bookit_password" v-model="password" :placeholder="translations.password">
                      <label for="bookit_password">{{ translations.password }}</label>
                      <div v-if="errors.password" class="validation-icon has-error-icon"></div>
                      <div v-if="!errors.password" class="validation-icon has-success-icon"></div>
                    </div>
                    <div class="form-group col-2">
                      <span class="error-message" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
                      <input type="password" id="bookit_password_confirmation" v-model="password_confirmation" :placeholder="translations.password_confirmation">
                      <label for="bookit_password_confirmation">{{ translations.password_confirmation }}</label>
                      <div v-if="errors.password" class="validation-icon has-error-icon"></div>
                      <div v-if="!errors.password" class="validation-icon has-success-icon"></div>
                    </div>
                  </div>
                  <div v-if="errors.message !== undefined" class="bookit-row">
                    <div class="bookit-alert bookit-alert-danger">
                      <div>{{ errors.message }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="bookit-modal-footer bookit-row">
                  <div class="col-2-3" v-if="getStaffClearPrice(staff, service) > 0">
                    <div v-for="(item, key) in payment_methods" class="payment-method">
                      <input type="radio" :id="key" class="display-inline-block" v-model="payment_method" :value="key">
                      <label :for="key" class="display-inline-block">{{ translations[key] }}</label>
                      
                      <span class="is-pro" v-if="key === 'paypal'">
                          <span class="pro-tooltip">
                             pro
                             <span style="visibility: hidden;" class="pro-tooltiptext">Feature Available <br> in Pro Version</span>
                          </span>
                      </span>
                      
                      <span class="is-pro" v-if="key === 'stripe'">
                          <span class="pro-tooltip">
                             pro
                             <span style="visibility: hidden;" class="pro-tooltiptext">Feature Available <br> in Pro Version</span>
                          </span>
                      </span>
                      
                    </div>
                    <div v-if="payment_method === 'stripe'" class="payment-method">
                      <div ref="stripe_card"></div>
                    </div>
                  </div>
                  <div class="col-3 text-right">
                    <button type="submit" class="modal-default-button">{{ translations.book_now }}</button>
                  </div>
                </div>
              </form>
              <a href="#" class="close-button" @click="close($event)"></a>
            </div>
          </transition>
          <transition v-else name="slide">
            <div class="bookit-modal-container" @click.stop>
              <div class="bookit-modal-body text-center appointment-confirmation">
                <div class="success-icon"></div>
                <h3 class="success-title">{{ translations.success_booking }}</h3>
                <h3 class="print-only">{{ translations.reservation_confirmation }}</h3>
                <p>{{ translations.booking_email_sent }}</p>
                
                <div v-if="redirect_url.length > 0" class="appointment-info redirect-answer">
                  <div class="text-bold">{{ translations.you_will_be_redirected }} <span class="text-capitalize payment-text">{{ payment_method }}</span> {{ translations.in }} {{ countDown }} {{ translations.seconds }}...</div>
                </div>
                
                <div class="appointment-details text-left">
                  <div class="bookit-row">
                    <div class="col-2">
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.service }}:</div>
                        <div class="info">{{ service.title }}</div>
                      </div>
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.employee }}:</div>
                        <div class="info">{{ staff.full_name }}</div>
                      </div>
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.price }}:</div>
                        <div class="info">{{ getStaffPrice(staff, service, settings) }}</div>
                      </div>
                    </div>
                    <div class="col-2">
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.client_name }}:</div>
                        <div class="info">{{ full_name }}</div>
                      </div>
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.reservation_time }}:</div>
                        <div class="info">{{ moment.unix(appointment.start_time) | moment(settings.time_format) }} / {{ moment.unix(appointment.start_time) | moment('dddd') }}</div>
                      </div>
                      <div class="info-block">
                        <div class="label text-uppercase">{{ translations.reservation_date }}:</div>
                        <div class="info">{{ moment.unix(appointment.start_time) | moment(settings.date_format) }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="action">
                  <button class="print-window" @click="printWindow">{{ translations.print_confirmation }}</button>
                  <div :class="['custom-select', { 'open': isOpenAddToCalendar } ]" @click="isOpenAddToCalendar = !isOpenAddToCalendar;" >
                    <div class="value">
                      <i class="calendar-icon"></i>
                      {{ translations.add_to_calendar }}
                    </div>
                    <div :class="['custom-options', { 'open': isOpenAddToCalendar } ]">
                  <span class="custom-option" @click="addToCalendar('google')">
                    {{ translations.google_calendar }}
                  </span>
                      <span class="custom-option" @click="addToCalendar('ical')">
                    {{ translations.i_cal }}
                  </span>
                    </div>
                  </div>
                </div>
              </div>
              <a href="#" class="close-button" @click="close($event)"></a>
            </div>
          </transition>
      </div>
    </div>
  </transition>
  `,
  data: () => ({
    appointment: {},
    success: false,
    inputErrors: ['phone', 'email', 'full_name', 'password','password_confirmation'],
    errors: {},
    loading: false,
    user_id: null,
    full_name: null,
    email: null,
    phone: null,
    password: null,
    password_confirmation: null,
    payment_method: null,
    stripe: {
      stripe: '',
      elements: '',
      card: '',
      client_secret: ''
    },
    countDown: 5,
    redirect_url: '',
    translations: bookit_window.translations,
    nonce: null,
    isOpenAddToCalendar: false,
  }),
  computed: {
    settings () {
      return this.$store.getters.getSettings;
    },
    service () {
      return this.$store.getters.getSelectedService;
    },
    staff () {
      return this.$store.getters.getSelectedStaff;
    },
    user:  {
      get() {
        return this.$store.getters.getUser;
      },
      set( user ) {
        this.$store.commit('setUser', user);
      }
    },
    time () {
      let time_moment = this.moment(this.$store.getters.getSelectedTime.value, 'HH:mm:ss');
      return this.moment(this.calendarAppointmentsDate).startOf('day').set({ hour: time_moment.hour(), minute: time_moment.minutes() });
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
    calendarAppointmentsDate: {
      get() {
        return this.$store.getters.getCalendarAppointmentsDate;
      },
      set( date ) {
        this.$store.commit('setCalendarAppointmentsDate', date);
      }
    },
    calendarWeek: {
      get() {
        return this.$store.getters.getCalendarWeek;
      },
      set( weekIndex ) {
        this.$store.commit('setCalendarWeek', weekIndex);
      }
    },
    disabledTimeSlots: {
      get() {
        return this.$store.getters.getDisabledTimeSlots;
      },
      set( timeSlots ) {
        this.$store.commit('setDisabledTimeSlots', timeSlots);
      }
    }
  },
  created() {
    if ( this.user && this.user.ID !== undefined ) {
      this.user_id    = this.user.ID;
      this.full_name  = ( this.user.customer && this.user.customer.full_name !== undefined ) ? this.user.customer.full_name : this.user.display_name;
      this.email      = this.user.user_email;
      this.phone      = ( this.user.customer && this.user.customer.phone !== undefined ) ? this.user.customer.phone : null;
      this.nonce      = (this.user.nonce && this.user.nonce !== null && this.user.nonce !== undefined) ? this.user.nonce :  bookit_window.nonces.bookit_book_appointment;
    }
  },
  methods: {
    /** Add to calendar methods **/
    addToCalendar( calendar ) {
      let link;
      if ( calendar == 'google' ) {
        this.generateGoogleCalendarLink();
      }
      if ( calendar == 'ical' ) {
        this.generateICalLink();
      }

    },
    closeAddToCalendarDropDown() {
      window.addEventListener('click', (e) => {
        if ( e.target.classList.contains('custom-select') || e.target.classList.contains('value')
            || this.hasParentClass(e.target, ['custom-select', 'custom-options'])) {
          return;
        }
        this.isOpenAddToCalendar = false;
      })
    },
    generateGoogleCalendarLink() {
      let url = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
      url += '&dates=' + this.moment.unix(this.appointment.start_time).format('YYYYMMDDTHHmmssZ')  + '/' + this.moment.unix(this.appointment.end_time).format('YYYYMMDDTHHmmssZ');
      url += '&text=' + encodeURIComponent('Appointment - ' + this.service.title);
      url += '&sf=true&output=xml';
      window.open(url,'_blank');
    },
    generateICalLink() {
      let ics =
          "BEGIN:VCALENDAR\n" +
          "CALSCALE:GREGORIAN\n" +
          "METHOD:PUBLISH\n" +
          "PRODID:-//bookit plugin//EN\n" +
          "VERSION:2.0\n" +
          "BEGIN:VEVENT\n" +
          "UID:bookit-" + this.appointment.start_time + "\n" +
          "DTSTART:" +
          this.moment.unix(this.appointment.start_time).format('YYYYMMDDTHHmmss') + "\n" +
          "DTEND:" +
          this.moment.unix(this.appointment.end_time).format('YYYYMMDDTHHmmss') +"\n" +
          "SUMMARY: Appointment - " + this.service.title + "\n" +
          "END:VEVENT\n" +
          "END:VCALENDAR";
      window.open( "data:text/calendar;charset=utf8," + escape(ics));
    },
    /** Add to calendar methods | End **/

    async bookNow() {
      this.loading  = true;
      this.errors   = {};
      if ( this.validation() ) {

        let data = {
          nonce: (this.nonce !== null) ? this.nonce : bookit_window.nonces.bookit_book_appointment,
          full_name: this.full_name,
          email: this.email,
          phone: this.phone,
          password: this.password,
          password_confirmation: this.password_confirmation,
          staff_id: this.staff.id,
          service_id: this.service.id,
          price: this.getStaffPrice(this.staff, this.service, this.settings),
          clear_price: this.staff.staff_services.find(staff_service => staff_service.id == this.service.id).price,
          user_id: this.user_id,
          date_timestamp: this.calendarAppointmentsDate.unix(),
          start_time: this.time.unix(),
          end_time: this.time.clone().add(this.service.duration, 'seconds').unix(),
          payment_method: this.payment_method,
          token: '',
        };

        if ( this.payment_method === 'stripe' ) {
          await this.stripe.stripe.createPaymentMethod('card', this.stripe.card)
            .then(async (result) => {
              if ( result.error !== undefined && result.error.message !== undefined ) {
                this.errors = { message: result.error.message };
                this.showError();
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
                      this.errors   = { message: response.data.message };
                      this.showError();
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

        // Validate Once Again
        if ( !this.validation() ) {
          return;
        }

        await this.axios.post(`${bookit_window.ajax_url}?action=bookit_book_appointment`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
            let response = res.data;

            if (response.data.errors && Object.keys(response.data.errors).length > 0){
              this.errors = response.data.errors;
            }
            
            if ( response.success ) {
              this.disabledTimeSlots        = [...this.disabledTimeSlots, response.data.appointment];
              this.appointment              = response.data.appointment;
              this.user_id                  = response.data.customer.wp_user_id;
              this.nonce                    = response.data.nonce;
              this.success                  = true;
              this.calendarAppointmentsDate = null;
              this.calendarWeek             = null;

              if(this.user === null || this.user === undefined){
                this.user                   = {
                  ID:  response.data.customer.wp_user_id,
                  display_name: response.data.customer.full_name,
                  user_email: response.data.customer.email,
                  password: this.password,
                  password_confirmation: this.password_confirmation,
                  nonce: response.data.nonce,
                };
              }

              if ( response.data.redirect_url.length > 0 ) {
                this.redirect_url = response.data.redirect_url;
                this.countDownTimer();
              }
              
            } else {
                this.showError();
            }
          });
      }
      this.loading = false;
    },
    showError() {
      let errors = [];

      /** not show input errors **/
      Object.keys(this.errors).reduce((object, key) => {
        if ( this.inputErrors.includes(key) == false ) {
          errors.push(this.errors[key])
        }
      }, {});

      if ( errors.length > 0 ) {
        this.$toasted.show(
            errors.join('<br/>'),
            { type: 'error', duration: 5000 }
        );
        this.$store.commit('setShowBookingForm', false);
      }
    },
    generateStripe() {
      this.$nextTick( () => {
        const stripe_id = this.payment_methods.stripe.publish_key;
        if ( ! stripe_id.length ) {
          this.errors = { message: bookit_window.translations.something_went_wrong };
          this.showError();
          return false;
        }

        this.stripe.stripe    = Stripe(stripe_id);
        this.stripe.elements  = this.stripe.stripe.elements();
        this.stripe.card      = this.stripe.elements.create('card');

        this.stripe.card.mount(this.$refs.stripe_card);
      });
    },
    async handleStripeCard(data, clear_price) {
      await this.stripe.stripe.handleCardAction(data.client_secret)
        .then(async (card_action_result) => {
          if ( card_action_result.error ) {
            this.errors   = { message: bookit_window.translations.not_authenticated_card };
            this.showError();
          } else if ( card_action_result.paymentIntent.status === 'requires_confirmation' ) {
            let payment_data = {
              nonce: bookit_window.nonces.bookit_book_appointment,
              total: clear_price,
              payment_intent_id: data.payment_intent_id
            };

            // Retrieve Payment
            await this.axios.post(`${bookit_window.ajax_url}?action=bookit_stripe_intent_payment`, this.generateFormData(payment_data), this.getPostHeaders())
              .then((intent_response) => {
                let intent = intent_response.data;
                if ( intent.success ) {
                  // Order Complete
                  this.stripe.client_secret = intent.data.client_secret;
                } else {
                  this.errors = { message: intent.data.message };
                  this.showError();
                }
              });
          }
        });
    },
    validation() {
      this.errors = {};
      if ( ! this.full_name || this.full_name.length === 0 ) {
        this.errors.full_name = bookit_window.translations.required_field;
      }

      if ( ! this.validEmail( this.email ) ) {
        this.errors.email = bookit_window.translations.invalid_email;
      }

      if ( this.settings.booking_type === 'registered' && ! this.user_id && ( ! this.password || this.password.length === 0 || this.password !== this.password_confirmation ) ) {
        this.errors.password_confirmation = bookit_window.translations.confirmation_mismatched;
      }

      return ( Object.keys(this.errors).length === 0 );
    },
    validEmail( email ) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },
    countDownTimer() {
      if ( this.countDown > 0) {
        setTimeout(() => {
          this.countDown -= 1;
          this.countDownTimer();
        }, 1000);
      } else {
        this.countDown    = 5;
        window.location.href = this.redirect_url;
        this.redirect_url = '';
      }
    },
    printWindow() {
      let html = document.querySelector('html');
      let body = document.querySelector('body');
      let originalHtml = {};
      let originalBody = {};
      const printCss = {'height': '100%', 'overflow': 'hidden'};
      for (const [style, value] of Object.entries(printCss)) {
        originalHtml[`${style}`] = html.style.getPropertyValue(`${style}`);
        originalBody[`${style}`] = html.style.getPropertyValue(`${style}`);
        html.style.setProperty(`${style}`, `${value}`);
        body.style.setProperty(`${style}`, `${value}`);
      }
      window.print();
      for (const [style, value] of Object.entries(printCss)) {
        html.style.setProperty(`${style}`, originalHtml[`${style}`]);
        body.style.setProperty(`${style}`, originalBody[`${style}`]);
      }
    },
    close( event ) {
      event.preventDefault();
      this.$store.commit('setShowBookingForm', false);
    }
  },
  watch: {
    payment_method: function (value) {
      if ( value === 'stripe' ) {
        this.generateStripe();
      }
    }
  }
}