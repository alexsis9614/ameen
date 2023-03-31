export default {
  template: `
  <transition name="bookit-modal">
    <div class="bookit-modal-mask">
      <div class="bookit-modal-wrapper" @click="close($event)">
        <div class="bookit-modal-container" @click.stop>
          <form @submit.prevent="deleteAppointment(row)">
            <div class="bookit-modal-header delete">
              <h3>{{ translations.delete }} {{ type }}</h3>
              <div class="bookit-row">
                <label>{{ translations.del_appointment_info}} <span class="text-bold">#{{ row.id }}</span></label>
              </div>
              <a href="#" @click="close($event)" class="close"><span class="close-icon"></span></a>
            </div>
            <div class="bookit-modal-body margin">
              <div class="send-notification-title">{{ translations.send_notification_to }}</div>
              <div class="bookit-row">
                <div class="form-group">
                  <label class="send-notification-label">
                    <input type="checkbox" v-model="send_notification.staff" @change="handleChangeSendNotification($event)" />
                    <span><b>{{ translations.employee }}: </b>{{ row.staff_name }}</span> 
                  </label>
                  <label class="send-notification-label">
                    <input type="checkbox" v-model="send_notification.customer" @change="handleChangeSendNotification($event)" />
                    <span><b>{{ translations.customer }}:</b> {{ row.customer_name }}</span> 
                  </label>
                </div>
              </div>
              <div v-if="show_reason" class="bookit-row">
                <div class="form-group">
                  <label for="title">{{ translations.cancel_reason_title }}</label>
                  <input type="text" :placeholder="translations.reason_placeholder" v-model="reason" />
                </div>
              </div>
            </div>
            <div class="bookit-modal-footer">
              <button type="submit" class="bookit-button error">
                {{ translations.delete }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
  `,
  data: () => ({
    translations: bookit_window.translations,
    deleteForm: false,
    send_notification: {  },
    show_reason: false,
    reason: '',
  }),
  props: {
    type: {
      type: String,
      required: true
    },
  },
  computed: {
    showDeleteForm: {
      get() {
        return this.$store.getters.getShowDeleteForm;
      },
      set( value ) {
        this.$store.commit('setShowDeleteForm', value);
      }
    },
    row: {
      get() {
        return Object.assign({}, this.$store.getters.getEditRow);
      },
      set( row ) {
        this.$store.commit('setEditRow', row);
      }
    },
  },
  created() {
    this.deleteForm = Object.keys(this.row).length;
  },
  methods: {
    handleChangeSendNotification: function(e) {
      this.show_reason = false;
      Object.keys(this.send_notification).forEach((key) => {
        if ( this.send_notification[key]) {
          this.show_reason = true;
        }
      });

    },
    // TODO need to remove row from all tabs
    deleteAppointment( row ) {
      row.nonce = bookit_window.nonces.bookit_delete_item;
      row.send_notification = this.send_notification;
      row.reason = this.reason;

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_delete_${this.type}`, this.generateFormData(row), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          this.$store.dispatch('delete_appointment', row);
          this.$store.commit('setIsObjectUpdated', true);
          this.clearModalFormRow();
        }
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
      this.showDeleteForm = false;
      this.row = {};
    },
  }
}