export default {
  name: 'stepDetails',
  template: `
    <div class="details">
        <div v-if="isMobile()" >
          <div class="row">
            <div class="detail-form">
              <label for="name">{{ translations.name_label }}</label>
              <input name="full_name" @change="changeData($event)" :class="{'error': errors.full_name}" type="text" v-model="appointment.full_name" :placeholder="translations.name_placeholder" />
              <span class="error-tip" v-if="errors.full_name">{{ errors.full_name }}</span>
            </div>
          </div>
          <div class="row">
            <div class="detail-form">
              <label for="name">{{ translations.phone }}</label>
              <input name="phone" @change="changeData($event)" :class="{'error': errors.phone}" v-model="appointment.phone" type="text" :placeholder="translations.phone" />
              <span class="error-tip" v-if="errors.phone">{{ errors.phone }}</span>
            </div>
          </div>
          <div class="row">
            <div class="detail-form">
              <label for="name">{{ translations.email }}</label>
              <input name="email" @change="changeData($event)" :class="{'error': errors.email}" v-model="appointment.email" type="email" :placeholder="translations.email" />
              <span class="error-tip" v-if="errors.email">{{ errors.email }}</span>
            </div>
          </div>
        </div>
        <div v-else class="row">
          <div class="detail-form col-4">
            <label for="name">{{ translations.name_label }}</label>
            <input name="full_name" @change="changeData($event)" :class="{'error': errors.full_name}" type="text" v-model="appointment.full_name" :placeholder="translations.name_placeholder" />
            <span class="error-tip" v-if="errors.full_name">{{ errors.full_name }}</span>
          </div>
          <div class="detail-form col-4">
            <label for="name">{{ translations.phone }}</label>
            <input name="phone" @change="changeData($event)" :class="{'error': errors.phone}" v-model="appointment.phone" type="text" :placeholder="translations.phone" />
            <span class="error-tip" v-if="errors.phone">{{ errors.phone }}</span>
          </div>
          <div class="detail-form col-4">
            <label for="name">{{ translations.email }}</label>
            <input name="email" @change="changeData($event)" :class="{'error': errors.email}" v-model="appointment.email" type="email" :placeholder="translations.email" />
            <span class="error-tip" v-if="errors.email">{{ errors.email }}</span>
          </div>
        </div>
      
      <div v-if="isMobile() && settings.booking_type == 'registered' && !this.appointment.user_id">
        <div class="row">
          <div class="detail-form">
            <label for="name">{{ translations.password }}</label>
            <input name="password" @change="changeData($event)" :class="{'error': errors.password}" v-model="appointment.password" type="password" :placeholder="translations.password" />
            <span class="error-tip" v-if="errors.password">{{ errors.password }}</span>
          </div>
        </div>
        <div class="row">
          <div class="detail-form">
            <label for="name">{{ translations.password_confirmation }}</label>
            <input name="password_confirmation" @change="changeData($event)" :class="{'error': errors.password_confirmation}" v-model="appointment.password_confirmation" type="password" :placeholder="translations.password_confirmation" />
            <span class="error-tip" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
          </div>
        </div>
      </div>
      <div v-else-if="settings.booking_type == 'registered' && !this.appointment.user_id" class="row">
        <div class="detail-form col-4">
          <label for="name">{{ translations.password }}</label>
          <input name="password" @change="changeData($event)" :class="{'error': errors.password}" v-model="appointment.password" type="password" :placeholder="translations.password" />
          <span class="error-tip" v-if="errors.password">{{ errors.password }}</span>
        </div>
        <div class="detail-form col-4">
          <label for="name">{{ translations.password_confirmation }}</label>
          <input name="password_confirmation" @change="changeData($event)" :class="{'error': errors.password_confirmation}" v-model="appointment.password_confirmation" type="password" :placeholder="translations.password_confirmation" />
          <span class="error-tip" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
        </div>
      </div>
      
      
      <div class="row">
        <div class="detail-form">
          <label for="name">{{ translations.service_note }}</label>
          <textarea name="messages" @change="changeData($event)" :placeholder="translations.service_note_placeholder" v-model="appointment.comment"></textarea>
        </div>
      </div>
    </div>
    `,
  components: {
  },
  data: () => ({
    translations: bookit_window.translations,
    user_id: null,
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
    errors() {
      return this.$store.getters.getErrors;
    },
    settings () {
      return this.$store.getters.getSettings;
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
  created() {
    /** If user logged in set default data **/

    if ( this.user && this.user != null && (this.user.hasOwnProperty('ID') && this.user.ID !== undefined ) ) {

      var appointment = Object.assign({}, this.appointment);

      appointment.user_id   = this.user.ID;
      appointment.full_name = ( !this.appointment.hasOwnProperty('full_name') ) ? this.user.display_name: appointment.full_name;
      appointment.email     = ( !this.appointment.hasOwnProperty('full_name') ) ? this.user.user_email: appointment.email;

      if ( this.user.hasOwnProperty('customer')  && this.user.customer !== null ) {
        if ( !this.appointment.hasOwnProperty('full_name') ) {
          appointment.full_name = ( this.user.customer.hasOwnProperty('full_name') && this.user.customer.full_name !== undefined ) ? this.user.customer.full_name : appointment.full_name;
        }

        if ( !this.appointment.hasOwnProperty('email') ) {
          appointment.email = ( this.user.customer.hasOwnProperty('email') && this.user.customer.email !== undefined ) ? this.user.customer.email : appointment.email;
        }

        if ( !this.appointment.hasOwnProperty('phone') ) {
          appointment.phone     = ( this.user.customer.phone ) ? this.user.customer.phone : null;
        }
      }

      if ( this.user != null && this.user.hasOwnProperty('nonce') ) {
        appointment.nonce     = (this.user.nonce && this.user.nonce !== null && this.user.nonce !== undefined) ? this.user.nonce :  bookit_window.nonces.bookit_book_appointment;
      }

      this.appointment  = appointment;
    }
  },
  methods: {
    changeData( event ) {
      delete this.errors[event.target.name];
      this.$store.commit('setErrors', this.errors);

      var appointment = Object.assign({}, this.appointment);
      appointment[event.target.name] = event.target.value;
      this.appointment               = appointment;
    }
  },
}