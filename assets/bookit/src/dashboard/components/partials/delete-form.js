export default {
  template: `
  <transition name="bookit-modal">
    <div class="bookit-modal-mask">
      <div class="bookit-modal-wrapper" @click="close($event)">
        <div class="bookit-modal-container delete" @click.stop>
          <form @submit.prevent="deleteAppointment(row)">
            <div class="bookit-modal-header">
              <h3>{{ translations.delete }} {{ type }} <span class="text-bold">#{{ row.id }}</span></h3>
              <p class="modal-info" v-html="confirmDeleteTitle"></p>
              <a href="#" @click="close($event)" class="close"><span class="close-icon"></span></a>
            </div>
            <div class="bookit-modal-body">
              <p class="modal-info">
                <b>{{ confirmDeleteInfo }}</b>
              </p>
            </div>
            <div class="bookit-modal-footer">
              <div class="button-actions">
                <button v-if="this.isHaveAppointment && type!='customer'" @click="editAppointments()" type="button" class="bookit-button light">
                  {{ translations.edit_appointments }}
                </button>

                <button v-if="type == 'staff'" @click="editServices()" type="button" class="bookit-button light">
                  {{ translations.edit }} {{ translations.services }}
                </button>
                
                <button @click="deleteAll($event, row)" type="button" class="bookit-button error" >
                  {{ translations.delete }} {{ translations.all }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
  `,
  data: () => ({
    translations: bookit_window.translations,
    calendarUrl: bookit_window.calendar_url,
    isHaveAppointment: false,
    confirmDeleteTitle: '',
    confirmDeleteInfo: '',
    calendarFilterList: ['status', 'staff', 'service', 'autorefresh'],
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
  mounted() {
    this.isHaveAppointment = this.row.assosiatedTotal.appointments > 0;

    /** set total connected values to title **/
    if ( Object.keys(this.row.assosiatedTotal).length > 0) {
      this.confirmDeleteTitle = this.translations[['confirm', this.type,'delete', 'title'].join('_')];
      for (var total_key in this.row.assosiatedTotal) {
        this.confirmDeleteTitle = this.confirmDeleteTitle.replace(
            '{' + total_key + '}', '<b>'+this.row.assosiatedTotal[total_key] + '</b>');
      }
    }
    this.confirmDeleteInfo  = this.translations[['confirm', this.type,'delete'].join('_')];

  },
  methods: {
    editServices() {
      window.location = bookit_window.services_url;
    },

    /** redirect to calendar page with chosen service/staff, current date and type month **/
    editAppointments() {
      this.setCookie('bookit_calendar_type', 'month');
      this.setCookie('bookit_current_appointment_date', this.moment().startOf('day'));

      // clean old filter data
      this.calendarFilterList.forEach( (filter) => {
        this.deleteCookie(['bookit_filter', filter].join('_'));
      });

      var filter     = this.row.id;
      var filterName = this.type;
      if ( this.type == 'category' ) {
        filter     = this.row.assosiatedTotal.service_ids;//.split(",");
        filterName = 'service';
      }
      this.setCookie(['bookit_filter', filterName].join('_'), filter);
      window.location = this.calendarUrl;
    },

    deleteAll( event, row ) {
      event.preventDefault();

      this.axios.get(`${bookit_window.ajax_url}?action=bookit_delete_${this.type}&id=${row.id}&nonce=${bookit_window.nonces.bookit_delete_item}`).then((res) => {
        let response = res.data;
        if ( response.success ) {
          if ( this.type == 'category' ) {
            this.$store.dispatch('delete_category', row);
          }else{
            this.$store.dispatch('delete_item', row);
          }
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
      this.clearModalFormRow();
    },

    close( event ) {
      event.preventDefault();
      this.clearModalFormRow();
    },
    clearModalFormRow(){
      this.$store.commit('setErrors', {});
      this.showDeleteForm = false;
      if ( this.type == 'category' ) {
        this.$emit('setShowCategoryDeleteForm', false);
      }
      this.row = {};
    },
  }
}