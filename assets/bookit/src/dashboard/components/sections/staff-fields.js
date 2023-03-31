import StaffService from '@dashboard-sections/staff-service';
import StaffWorkingHours from '@dashboard-sections/staff-working-hours';

export default {
  template: `
    <div class="bookit-modal-body position-relative no-side-margin">
      <div v-if="loading" class="loader">
        <div class="loading"><div v-for="n in 9"></div></div>
      </div>
      <div :class="['accordion', {active: accordion.details}]">
        <a class="accordion-title" @click="switchAccordion('details', accordion.details)">{{ translations.details }} <span class="float-right accordion-icon"></span></a>
        <div v-show="accordion.details" class="accordion-body">
          <div class="bookit-row">
            <div class="form-group col-3">
              <label for="full_name">{{ translations.full_name }}</label>
              <input type="text" id="full_name" v-model="row.full_name" :class="{error:errors.full_name}" />
              <span class="error-tip" v-if="errors.full_name">{{ errors.full_name }}</span>
            </div>
            <div class="form-group col-3">
              <label for="email">{{ translations.email }}</label>
              <input type="text" id="email" v-model="row.email" :class="{error:errors.email}">
              <span class="error-tip" v-if="errors.email">{{ errors.email }}</span>
            </div>
            <div class="form-group col-3">
              <label for="phone">{{ translations.phone }}</label>
              <input type="text" id="phone" v-model="row.phone" :class="{error:errors.phone}">
              <span class="error-tip" v-if="errors.phone">{{ errors.phone }}</span>
            </div>
          </div>

          <div class="bookit-row">

            <div class="form-group" v-if="!createNewWPUser">
              <label for="wp-user">{{ translations.wp_user }}</label>
              <div class="staff-wp-user">
                <select v-model="row.wp_user_id" :placeholder="translations.wp_user" >
                  <option :value="null">--{{ translations.wp_user }}--</option>
                  <option v-for="wp_user in wp_users" :value="wp_user.ID">
                    {{ wp_user.display_name }} ( {{ wp_user.user_email }} )
                  </option>
                </select>
                <button type="button" class="add-wp-user-btn bookit-button" @click="showWPUserForm()">
                  <i class="add-icon"></i> {{ translations.new }} {{ translations.wp_user }}
                </button>
              </div>
            </div>
            <!-- Create new wordpress user form-->
            <div v-else>
              <div class="bookit-row">
                <div class="form-group col-3">
                  <label for="email">{{ translations.wp_user }} {{ translations.email }}</label>
                  <input :placeholder="translations.email" v-on:input="clearWpUserError($event)" type="email" name="wp_email" v-model="wp_user.email" :class="{error:errors.wp_email}" >
                  <span class="error-tip" v-if="errors.wp_email">{{ errors.wp_email }}</span>
                </div>
                <div class="form-group col-3">
                  <label for="password">{{ translations.password }}</label>
                  <input :placeholder="translations.password_placeholder" v-on:input="clearWpUserError($event)"  name="password" type="password" v-model="wp_user.password" :class="{error:errors.password}">
                  <span class="error-tip" v-if="errors.password">{{ errors.password }}</span>
                </div>
                <div class="form-group col-3">
                  <label for="password_confirmation">{{ translations.password_confirmation }}</label>
                  <input :placeholder="translations.repeat_password_placeholder" v-on:input="clearWpUserError($event)" name="password_confirmation" type="password" v-model="wp_user.password_confirmation" :class="{error:errors.password_confirmation}">
                  <span class="error-tip" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
                </div>
              </div>
              <div class="bookit-row">
                <div :class="['form-group', 'col-3', 'create-wp-user-action']">
                  <button type="button" class="add-wp-user-btn" @click="createWPUser();">
                    <i class="add-icon"></i> {{ translations.add }}
                  </button>
                  <button type="button" class="cancel-btn" @click="createNewWPUser = !createNewWPUser;">
                    {{ translations.cancel }}
                  </button>
                </div>
              </div>
            </div>
            <!-- Create new wordpress user form | end -->
            
          </div>
        </div>
      </div>
      
      <div :class="['accordion', {active: accordion.services}]">
        <a class="accordion-title" @click="switchAccordion('services', accordion.services)">{{ translations.services }} <span class="float-right accordion-icon"></span></a>
        <div v-show="accordion.services" class="accordion-body">
          <div class="form-group no-margin" v-for="service in services" :key="service.id">
            <staff-service
              :service="getService(service)"
              :isChecked="isServiceActive(service.id)"
              @emitStaffServices="updateStaffServices"
              :key="service.id"
            ></staff-service>
          </div>
        </div>
      </div>
      
      <div :class="['accordion', {active: accordion.working_hours}]">
        <a class="accordion-title" @click="switchAccordion('working_hours', accordion.working_hours)">
          {{ translations.working_hours }} <span class="float-right accordion-icon"></span>
        </a>
        <div class="accordion-tip error" v-if="errors.working_hours" >
          {{ errors.working_hours }}
        </div>
        <div class="accordion-tip info" v-if="showEditWHInfo" @click="updateShowEditWhInfo(false)">
          {{ translations.confirm_edit_staff_wh }}
        </div>
        <div v-show="accordion.working_hours" class="accordion-body">
          <div class="form-group no-margin small-bottom-margin" v-for="index in 7" :key="index">
            <staff-working-hours
            :working_hour="getWorkingHour(index)"
            @updateShowEditWhInfo="updateShowEditWhInfo"
            ></staff-working-hours>
          </div>
          <hr>
          <div class="form-group no-margin small-bottom-margin">
            <div class="bookit-row">
              <div class="col-4">
                <label class="text-capitalize staff-services-label working-hours-label">{{ translations.lunch_break }}</label>
              </div>
              <div>
                <select @change="handleChangeBreakFrom($event)" class="width-auto">
                  <option :value="null" :selected="staff_break.break_from == null">{{ translations.no_break }}</option>
                  <option v-for="time in getTimeList()" :value="time.value" :selected="staff_break.break_from == time.value">
                    {{ time.label }}
                  </option>
                </select>
                <span v-show="staff_break.break_from">
                  &nbsp; <b>to</b> &nbsp;
                  <select @change="handleChangeBreakTo($event)" class="width-auto">
                    <option v-for="time in getTimeList(staff_break.toHour, staff_break.toMinutes)" :value="time.value" :selected="staff_break.break_to == time.value">
                      {{ time.label }}
                    </option>
                  </select>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="Object.keys(googleCalendarAddon).length > 0 && googleCalendarAddon.data.isSettingsSet && row.id" :class="['accordion google-calendar-settings', {active: accordion.integration}]">
        <a class="accordion-title" @click="switchAccordion('integration', accordion.integration)">Google Calendar <span class="float-right accordion-icon"></span></a>
        <div v-show="accordion.integration" class="accordion-body">
          <div class="bookit-row">
            <div class="form-group">
              <div class="gc-data" v-if="row.gc_token.email && row.gc_token.refresh_token">
                <label class="gc-label" for="connected_google_mail">{{ translations.connected }}: {{ row.gc_token.email }}</label>
                
                <div>
                  <a :href="row.gc_token.auth_url" target="_blank" class="bookit-button">{{ translations.re_connect }}</a>
                  <a  target="_blank" @click="disconnectStaffFromGoogleCalendar()" class="bookit-button error">{{ translations.disconnect }}</a>
                </div>
              </div>
              <div class="gc-data" v-else>
                <a :href="row.gc_token.auth_url" target="_blank" class="bookit-button">{{ translations.connect }}</a>
              </div>
              <span class="error-tip" v-if="errors.google_calendar">{{ errors.google_calendar }}</span>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  `,
  components: {
    'staff-service': StaffService,
    'staff-working-hours': StaffWorkingHours
  },
  data: () => ({
    accordion: {
      details: true,
      services: false,
      working_hours: false,
      integration: false,
    },
    createNewWPUser: false,
    wp_user: {},
    showEditWHInfo:false,
    staff_break: {
      break_from: null,
      break_to: null,
      toHour: 0,
      toMinutes: 0
    },
    translations: bookit_window.translations,
  }),
  props: {
    row: {
      type: Object,
      required: true
    },
    loading:{
      type: Boolean,
      required:false
    }
  },
  computed: {
    services() {
      return this.$store.getters.getServices;
    },
    googleCalendarAddon() {
      var addons = this.$store.getters.getAddons;
      if ( addons.some((st) => { return st.name === 'google-calendar'; }) ) {
        return addons.find((addon) => addon.name === 'google-calendar');
      }
      return {};
    },
    errors() {
      return this.$store.getters.getErrors;
    },
    wp_users() {
      return this.$store.getters.getWpUsers;
    }
  },
  created() {
    if ( Object.keys(this.row).length === 0 ) {
      this.row.staff_services = [];
      this.row.events = [];
      this.row.working_hours  = [];
      this.row.gc_token       = {};

      this.$store.commit('setEditRow', this.row);
    } else {
      const has_break = this.row.working_hours.find(x => x.break_from != null);
      if ( has_break !== undefined ) {
        this.staff_break.break_from = has_break.break_from;
        this.staff_break.break_to   = has_break.break_to;
        this.staff_break.toHour     = this.moment(has_break.break_from, 'h:mm a').format('H');
        this.staff_break.toMinutes  = this.moment(has_break.break_to, 'h:mm a').format('m');
      }
    }
  },
  methods: {
    /** wp user functions **/
    showWPUserForm() {
      this.createNewWPUser = !this.createNewWPUser;

      if ( (typeof this.row.wp_user_id === "undefined") || (this.row.wp_user_id && this.row.wp_user_id.length === 0 )
          || this.row.wp_user_id == null ) {
        this.wp_user.email   = this.row.email;
      }
    },
    clearWpUserError( event ) {
      var errors = this.errors;
      if ( errors.hasOwnProperty(event.target.name) ) {
        delete errors[event.target.name];
        this.$store.commit('setErrors', errors);
      }
    },
    validateWPUser() {
      let errors = {};

      if ( ! this.checkEmail( this.wp_user.email ) ) {
        errors.wp_email = bookit_window.translations.invalid_email;
      }
      if ( ! this.wp_user.password || this.wp_user.password.length === 0 ) {
        errors.password = bookit_window.translations.required_field;
      }
      if ( this.wp_user.password !== this.wp_user.password_confirmation ) {
        errors.password_confirmation = bookit_window.translations.confirmation_mismatched;
      }
      return errors;
    },
    createWPUser() {
      this.$store.commit('setErrors', {});
      var errors = this.validateWPUser();

      if (Object.keys(errors).length > 0){
        this.$store.commit('setErrors', errors);
        return;
      }

      this.wp_user.nonce     = bookit_window.nonces.bookit_save_item;
      this.wp_user.object    = 'wp_user';
      this.wp_user.full_name = (typeof this.row.full_name !== "undefined") ? this.row.full_name : '';

      this.$emit('setLoading', true);

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_create_wordpress_user`, this.generateFormData(this.wp_user), this.getPostHeaders()).then((res) => {
        let response = res.data;

        if (response.data.errors && Object.keys(response.data.errors).length > 0){
          this.$store.commit('setErrors', response.data.errors);
        }

        if ( response.success ) {
          if ( Object.keys(response.data.wp_user).length > 0 ) {
            this.$store.dispatch('addWPUser', response.data.wp_user);
            this.row.wp_user_id = response.data.wp_user.ID;
            this.createNewWPUser  = false;
          }
        }
        this.$emit('setLoading', false);
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    /** wp user functions | end **/

    /** google calendar **/
    disconnectStaffFromGoogleCalendar() {
      this.$store.commit('setErrors', {});

      let data = { };
      data.nonce = bookit_window.nonces.bookit_save_item;
      data.id    = this.row.id;

      this.$emit('setLoading', true);

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_disconnect_google_calendar`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          if ( Object.keys(response.data.staff.gc_token).length > 0 ) {
            this.row.gc_token = response.data.staff.gc_token;
            this.$store.commit('setEditRow', this.row);
          }
        }
        this.$emit('setLoading', false);
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    switchAccordion( key, value ) {
      Object.keys(this.accordion).forEach(i => this.accordion[i] = false);
      this.accordion[key] = !value;
    },
    isServiceActive( service_id ) {
      return ( this.getStaffService( service_id ) !== undefined );
    },
    getService( service ) {
      let staff_service = this.getStaffService( service.id );
      return ( staff_service !== undefined ) ? staff_service : service;
    },
    getStaffService( service_id ) {
      if ( this.row.staff_services !== undefined && this.row.staff_services.length !== 0 ) {
        return this.row.staff_services.find(x => x.id == service_id);
      }
      return undefined;
    },
    getWorkingHour( weekday ) {
      let staff_working_hour = this.getStaffWorkingHour( weekday );
      if ( staff_working_hour === undefined ) {
        staff_working_hour      = this.getDefaultWorkingHour( weekday );
        this.row.working_hours  = [...this.row.working_hours, staff_working_hour];
      }
      return staff_working_hour;
    },
    getStaffWorkingHour( weekday ) {
      if ( this.row.working_hours !== undefined && this.row.working_hours.length !== 0 ) {
        return this.row.working_hours.find(x => x.weekday == weekday);
      }
      return undefined;
    },
    getDefaultWorkingHour( weekday ) {
      if ( weekday === 6 || weekday === 7 ) {
        return { weekday: weekday, start_time: null, end_time: null };
      }
      return { weekday: weekday, start_time: '09:00:00', end_time: '18:00:00' };
    },
    updateStaffServices( add_edit, service ) {
      const staff_service = this.getStaffService(service.id);
      if ( staff_service !== undefined ) {
        let staff_services = [...this.row.staff_services];
        staff_services.splice( staff_services.indexOf( staff_service ), 1 );
        this.row.staff_services = staff_services;
      }
      if ( add_edit ) {
        this.row.staff_services = [...this.row.staff_services, service];
      }
    },
    handleChangeBreakFrom( event ) {
      const value = event.target.value;
      if ( value === '' ) {
        this.staff_break.break_from = null;
        this.staff_break.break_to   = null;
      } else {
        this.staff_break.break_from = value;
      }
      this.row.working_hours.forEach(item => {
        item.break_from = this.staff_break.break_from;
        item.break_to = this.staff_break.break_to;
      });
    },
    handleChangeBreakTo( event ) {
      this.row.working_hours.forEach(item => item.break_to = event.target.value);
    },
    updateShowEditWhInfo( value ) {
      this.showEditWHInfo = value;
    },
  }
}