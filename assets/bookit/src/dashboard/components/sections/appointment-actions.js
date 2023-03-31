
export default {
  template: `
    <div :class="['actions', 'appointment', {disabled}]">
      <template v-if="row.start_time <= moment().unix()">
        <div class="past">
          <span class="past-title">{{ translations.appointment_past }}</span>
          <button class="bookit-button delete-btn" @click="showDeleteForm($event, row)" ></button>
        </div>
      </template>
      
      <template v-else>
        <button v-if="row.status != 'approved'" :disabled="disabled" class="bookit-button" @click="change_status(row, 'approved')">
          <span class="check-icon"></span> {{ translations.approve }}
        </button>
        <button v-if="row.status == 'pending'" :disabled="disabled" class="bookit-button button-light" @click="change_status(row, 'cancelled')">
          <span class="reject-icon"></span> {{ translations.reject }}
        </button>
        <button v-if="row.status == 'approved'" :disabled="disabled" class="bookit-button button-light" @click="change_status(row, 'cancelled')">
          <span class="reject-icon"></span> {{ translations.cancel }}
        </button>
        
        <button class="bookit-button view-btn" @click="showView( $event, row )" :disabled="disabled" ></button>
        <button class="bookit-button edit-btn" v-if="row.status != 'cancelled'" :disabled="disabled" @click="showEditForm($event, row)" ></button>
        <button class="bookit-button delete-btn" :disabled="disabled" @click="showDeleteForm($event, row)" ></button>
      </template>
    </div>
  `,
  data: () => ({
    translations: bookit_window.translations,
    disabled: false
  }),
  props: {
    row: {
      type: Object,
      required: true
    }
  },
  methods: {
    showView( event, row ) {
      event.preventDefault();

      /** set data from notes to row **/
      if ( row.notes.hasOwnProperty('comment') ) {
        row.comment = row.notes.comment;
      }
      if ( row.notes.hasOwnProperty('email') ) {
        row.customer_email = row.notes.email;
      }
      if ( row.notes.hasOwnProperty('phone') ) {
        row.customer_phone = row.notes.phone;
      }

      this.$store.commit('setActionType', 'appointmentView');
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowEditAddForm', true);
    },

    showEditForm( event, row ) {
      event.preventDefault();

      /** set data from notes to row **/
      if ( row.notes.hasOwnProperty('comment') ) {
        row.comment = row.notes.comment;
      }
      if ( row.notes.hasOwnProperty('email') ) {
        row.customer_email = row.notes.email;
      }
      if ( row.notes.hasOwnProperty('phone') ) {
        row.customer_phone = row.notes.phone;
      }

      this.$store.commit('setActionType', 'edit');
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowEditAddForm', true);
    },

    showDeleteForm( event, row ) {
      event.preventDefault();
      this.$store.commit('setEditRow', row);
      this.$store.commit('setShowDeleteForm', true);
    },

    async change_status( row, status ) {
      this.disabled = true;
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
      this.disabled = false;
    }
  }
}