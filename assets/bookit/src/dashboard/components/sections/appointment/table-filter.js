import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

export default {
  template: `
    <div class="appointment-filters">
      <div class="filter-field">
        <input type="text" class="bookit-filter-input" v-model="filter.search" :placeholder="translations.search" @keydown="$event.stopImmediatePropagation()">
      </div>
      <div class="filter-field col-2 date-range">
        <VueCtkDateTimePicker :no-value-to-custom-elem="true"  :firstDayOfWeek=1 id="date" v-model="filter.dates" color="#006666" label="Select date" range formatted="DD.MM.YYYY" outputFormat="YYYY-MM-DD HH:mm"/>
      </div>
    </div>
  `,
  components: {
    VueCtkDateTimePicker,
  },
  data: () => ({
    translations: bookit_window.translations,
  }),
  computed:{
    filter:{
      get(){
        return this.$store.getters.getFilterAppointments;
      },
      set( value ) {
        this.$store.commit('setFilterAppointments', value);
      }
    },
  },
  created() {},
  methods: {}
}
