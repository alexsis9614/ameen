import add from '@dashboard-sections/appointment/add';
import edit from '@dashboard-sections/appointment/edit';

export default {
  template: `
  <transition name="bookit-modal">
    <div class="bookit-modal-mask">
      <div class="bookit-modal-wrapper" @click="close($event)">
        <div class='bookit-modal-container appointment' @click.stop>
          
          <form @submit.prevent="save(appointment)">
            <div class="bookit-modal-header">
              <h3>{{ modalTitle }} <b v-if="appointment.id"> #{{ appointment.id }}</b></h3>
              <span class="error" v-if="notAvailable">{{ translations.not_available }}</span>
              <a href="#" @click="close($event)" class="close"><span class="close-icon"></span></a>
            </div>
            <component :is="modalType" 
                       :paymentMethods="paymentMethods"
                       :appointmentStatuses="appointmentStatuses"
                       :services="services"
                       :timeSlotList="timeSlotList"
                       :staff="staff"
                       :settings="settings"
                       :appointment="appointment"
                       :disabledTimeSlots="disabledTimeSlots"
                       :loading="loading"
                       :viewType="type"
                       v-on:disableTimeSlots="disableTimeSlots"
                       v-on:setNotAvailable="setNotAvailable"
                       v-on:setLoading="setLoading" 
                       v-on:changeStatus="change_status">
            </component>
            <div class="bookit-modal-footer">
              <div v-if="type == 'add' || type == 'edit' || (type == 'appointmentView' && editView)">
                <button type="submit" class="bookit-button">
                  {{ translations.save }}
                </button>
              </div>
              <div class="view-actions" v-else-if="type == 'appointmentView' && !editView">
                <button type="button" v-if="appointment.status != 'approved'" :disabled="disabled" class="approve-btn" @click="change_status(appointment, 'approved')">
                  <i class="btn-icon"></i>{{ translations.approve }}
                </button>
                <button type="button" v-if="appointment.status == 'pending'" :disabled="disabled" class="reject-btn" @click="change_status(appointment, 'cancelled')">
                  <i class="btn-icon"></i>{{ translations.reject }}
                </button>
                <button type="button" v-if="appointment.status == 'approved'" :disabled="disabled" class="cancel-btn" @click="change_status(appointment, 'cancelled')">
                  <i class="btn-icon"></i>{{ translations.cancel }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
  `,
  components: {
    add, edit,
  },
  data: () => ({
    disabled: false,
    disabledTimeSlots: [],
    loading: true,
    notAvailable: false,
    translations: bookit_window.translations,
  }),
  props: {
    type: {
      type: String,
      required: true
    },
  },
  computed: {
    modalType() {
      return this.type == 'add' ? 'add': 'edit';
    },
    modalTitle() {
      var title = [this.translations.create, this.translations.appointment].join(' ');
      if ( this.type == 'edit' || (this.type == 'appointmentView' && this.editView)) {
        return [this.translations.edit, this.translations.appointment].join(' ');
      }
      if ( this.type == 'appointmentView' ) {
        return this.translations.appointment_info;
      }

      return title;
    },
    paymentMethods () {
      return this.$store.getters.getPaymentMethods;
    },
    appointmentStatuses () {
      return this.$store.getters.getAppointmentStatuses;
    },
    services () {
      let allServices = this.$store.getters.getServices;
      /** if new appointment from day type , show services just for choosen staff **/
      if ( this.appointment.hasOwnProperty('calendarType') && this.appointment.calendarType == 'day' ) {
        var selectedStaff   = this.staff.filter(staff => staff.id == this.appointment.staff_id)[0];
        var staffServiceIds = selectedStaff.staff_services.map(s => s.id);
        allServices = allServices.filter(service => staffServiceIds.includes(parseInt( service.id ) ));
      }
      return allServices;
    },
    staff () {
      return this.$store.getters.getStaff;
    },
    settings () {
      return this.$store.getters.getSettings;
    },
    timeSlotList () {
      return this.$store.getters.getTimeSlotList;
    },
    editView() {
      return this.$store.getters.getEditView;
    },
    appointment: {
      get() {
        return Object.assign({}, this.$store.getters.getEditRow);
      },
      set( appointment ) {
        this.$store.commit('setEditRow', appointment);
      }
    },
    showEditAddForm: {
      get() {
        return this.$store.getters.getShowEditAddForm;
      },
      set( value ) {
        this.$store.commit('setShowEditAddForm', value);
      }
    }
  },
  created() {
    this.disableTimeSlots();
  },

  methods: {
    disableTimeSlots() {
      this.loading = true;
      var date = this.moment.unix(this.appointment.date_timestamp).startOf('day');
      const data = {
        nonce: bookit_window.nonces.bookit_day_appointments,
        date_timestamp: date.unix()
      };

      if ( this.$children[0] instanceof Object && Object.keys(this.$children[0]).includes('selectedStaffID') ){
        data['staff_id'] = this.$children[0].selectedStaffID;
      }else if ( this.appointment.hasOwnProperty('staff_id') ){
        data['staff_id'] = this.appointment.staff_id;
      }

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_day_appointments`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          this.disabledTimeSlots = response.data;
          this.loading = false
        }
      });
    },
    setNotAvailable( value ) {
      this.notAvailable = value;
    },
    setLoading( value ) {
      this.loading = value;
    },
    save( appointment ) {
      this.$store.commit('setErrors', {});
      this.loading = true;
      var action = 'edit';
      if ( this.type == 'add' ){
        action = 'add';
      }

      appointment.nonce          = bookit_window.nonces['bookit_' + action + '_appointment'];
      appointment.clear_price    = appointment.price;
      appointment.date_timestamp = this.moment.unix(appointment.date_timestamp).startOf('day').unix();

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_${action}_appointment`, this.generateFormData(this.appointment), this.getPostHeaders()).then((res) => {
        let response = res.data;

        if (response.data.errors && Object.keys(response.data.errors).length > 0){
          this.$store.commit('setErrors', response.data.errors);
        }

        if ( response.success ) {
          this.clearModalFormRow();

          if (response.data.appointment && Object.keys(response.data.appointment).length > 0){
            appointment = response.data.appointment;
          }

          /** if create **/
          if ( this.type == 'add' ) {
            this.$store.commit('unshiftRows', appointment);
          } else {
            /** if update **/
            this.$store.commit('setEditedRow', appointment);
          }
          this.$store.commit('setIsObjectUpdated', true);
        }

        this.loading = false;
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    close( event ) {
      event.preventDefault();
      this.clearModalFormRow();
    },
    clearModalFormRow(){
      this.$store.commit('setErrors', {});
      this.showEditAddForm = false;
      this.appointment = {};
    },
    async change_status( row, status ) {
      this.disabled = true;
      await this.axios.get(`${bookit_window.ajax_url}?action=bookit_appointment_status&nonce=${bookit_window.nonces.bookit_appointment_status}&id=${row.id}&status=${status}`).then((res) => {
        let response = res.data;
        if ( response.success ) {
          row.status = status;
          this.appointment = row;
          this.$store.commit('setEditedRow', row);
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
      this.disabled = false;
    }
  }
}