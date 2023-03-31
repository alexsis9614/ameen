import ServiceFields from '@dashboard-sections/service-fields';
import StaffFields from '@dashboard-sections/staff-fields';
import CustomerFields from '@dashboard-sections/customer-fields';
import AppointmentFields from '@dashboard-sections/appointment/edit';

export default {
  template: `
  <transition name="bookit-modal">
    <div class="bookit-modal-mask">
      <div class="bookit-modal-wrapper" @click="close($event)">
        <div :class="['bookit-modal-container', type]" @click.stop>
              <form @submit.prevent="save(row)">
            <div class="bookit-modal-header">
              <h3>{{ editForm ? translations.edit : translations.add }} {{ type }} #{{ row.id }}</h3>
              <a href="#" @click="close($event)" class="close"><span class="close-icon"></span></a>
            </div>
            <component :is="type" :row="row" :loading="loading" v-on:setLoading="setLoading"></component>
            <div class="bookit-modal-footer">
              <button type="submit" class="bookit-button" :disabled="loading? true: false">
                {{ editForm ? translations.save : translations.add }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
  `,
  components: {
    'customer': CustomerFields,
    'service': ServiceFields,
    'staff': StaffFields,
    'appointment': AppointmentFields,
  },
  data: () => ({
    translations: bookit_window.translations,
    editForm: false,
    loading: false,
  }),
  props: {
    type: {
      type: String,
      required: true
    },
  },
  computed: {
    row: {
      get() {
        return Object.assign({}, this.$store.getters.getEditRow);
      },
      set( row ) {
        this.$store.commit('setEditRow', row);
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
    this.editForm = Object.keys(this.row).length;
  },
  methods: {
    setLoading( value ) {
      this.loading = value;
    },
    save( row ) {
      row.nonce = bookit_window.nonces.bookit_save_item;
      this.$store.commit('setErrors', {});
      this.loading = true;
      this.axios.post(`${bookit_window.ajax_url}?action=bookit_save_${this.type}`, this.generateFormData(row), this.getPostHeaders()).then((res) => {
        let response = res.data;

        if (response.data.errors && Object.keys(response.data.errors).length > 0){
            this.$store.commit('setErrors', response.data.errors);
        }

        if ( response.success ) {
          this.clearModalFormRow();

          if ( ! row.id ) {
            row.id = response.data.id;

            if ( this.type == 'staff' && response.data.staff && response.data.staff.gc_token != null && Object.keys(response.data.staff.gc_token).length > 0) {
              row.gc_token = response.data.staff.gc_token;
            }
            this.$store.commit('unshiftRows', row);
          } else {
            this.$store.commit('setEditedRow', row);
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
      this.row = {};
    },
  }
}