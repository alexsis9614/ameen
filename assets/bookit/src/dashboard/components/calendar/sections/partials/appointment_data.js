
export default {
  template: `
    <div class="appointment-data">
      <div class="appointment-detail-item">
        <div><i class="icon user"></i> {{ appointment.customer_name }}</div> 
        <div v-if="appointment.customer_phone">{{ appointment.customer_phone }}</div> 
        <div class="email">{{ appointment.customer_email }}</div>
      </div>
      <div class="appointment-detail-item">
        <div><i class="icon staff"></i>{{ appointment.staff_name }}</div>
      </div>
      <div class="appointment-detail-item">
        <div><i class="icon price"></i>{{ appointment.price_row }}
          <button class="payment-type" v-if="translations[appointment.payment_method] !== undefined">
            {{ translations[appointment.payment_method] }}
          </button>
          <button class="payment-type" v-else>
            {{ appointment.payment_method }}
          </button>
        </div>
      </div>
      <div class="appointment-detail-item"><div><i class="icon id"></i>{{ appointment.id }}</div></div>
      <div class="appointment-detail-item"><div><b>{{ translations.status }}:&nbsp; </b> {{ translations[appointment.status] }}</div></div>
      
      <div class="appointment-detail-btn" v-if="appointment.start_time > moment().unix()">
          <button type="button" v-if="appointment.status != 'approved'" class="approve-btn" @click="change_status($event, appointment, 'approved')">
            <i class="btn-icon"></i> {{ translations.approve }}
          </button>
          <button v-if="appointment.status == 'pending'" type="button" class="reject-btn" @click="change_status($event, appointment, 'cancelled')">
            <i class="btn-icon"></i>{{ translations.reject }}
          </button>
          <button v-if="appointment.status == 'approved'" type="button" class="approved-btn">
            <i class="btn-icon"></i>{{ translations.approved }}
          </button>
          <button v-if="appointment.status == 'approved'" type="button" class="cancel-btn" @click="change_status($event, appointment, 'cancelled')">
              <i class="btn-icon"></i>{{ translations.cancel }}
            </button>
      </div>
      <div class="appointment-actions">
        <button @click="showEditForm($event, appointment)" v-if="!isPast(appointment) && appointment.status != 'cancelled'" class="edit-btn"></button>
        <button class="delete-btn" @click="showDeleteForm($event, appointment)"></button>
      </div>
    </div>
  `,
  name: "appointment_detail",
  data: () => ({
    translations: bookit_window.translations,
    popupLoading: false,
  }),
  props: {
    appointment: {
      required: true,
    },
  },
  methods: {
    isPast( appointment ) {
      var now = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      var start  = this.moment.unix(appointment.start_time);
      if ( now.isSameOrAfter(start) ) {
        return true
      }
      return false
    },
    showEditForm( event, row ) {
      event.preventDefault();
      this.$store.commit('setActionType', 'edit');
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowEditAddForm', true);
    },
    showDeleteForm( event, row ) {
      event.preventDefault();
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowDeleteForm', true);
    },

    async change_status( event, row, status ) {
      event.preventDefault();
      this.$emit('setLoader', true);
      await this.axios.get(`${bookit_window.ajax_url}?action=bookit_appointment_status&nonce=${bookit_window.nonces.bookit_appointment_status}&id=${row.id}&status=${status}`).then((res) => {
        let response = res.data;
        if ( response.success ) {
          row.status = status;
          this.$store.commit('setEditedRow', row);
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
      this.$emit('setLoader', false);
    }
  }
}