import booking_exam from '@views/booking_exam/index'

export default {
  template: `
    <div :class="['bookit-app-content', 'booking_exam']" ref="bookit-app-content">
      <booking_exam :navigation="navigation" :attributes="attributes"></booking_exam>
    </div>
  `,
  components: {
    booking_exam,
  },
  props: {
    language: String,
    attributes: {
      type: Object,
      required: true,
      default: {}
    },
    navigation: {
      type: Array,
      required: true,
      default: {}
    },
    categories: {
      type: Array,
      required: true,
      default: [],
    },
    services: {
      type: Array,
      required: true,
      default: [],
    },
    staff: {
      type: Array,
      required: true,
      default: [],
    },
    settings: {
      type: Object,
      required: true,
    },
    theme: {
      type: String,
      required: false
    },
    time_format: {
      type: String
    },
    time_slot_list: {
      type: Array,
      required: true
    },
    user: Object
  },
  created () {
    /** set data to store **/
    this.$store.commit('setCategories', this.categories);
    this.$store.commit('setServices', this.services);
    this.$store.commit('setTimeSlotList', this.time_slot_list);
    this.$store.commit('setTimeFormat', this.time_format);

    this.staff.map((staff) => {
      staff.staff_services  = JSON.parse(staff.staff_services) || [];
      staff.working_hours   = JSON.parse( JSON.stringify( JSON.parse(staff.working_hours) ).replace(/"NULL"/gi, null) ) || [];
    });
    this.$store.commit('setStaff', this.staff);
    Object.keys(this.settings).forEach((key) => {
      if ( this.settings[key] === 'true' ) {
        this.settings[key] = true;
      } else if ( this.settings[key] === 'false' ) {
        this.settings[key] = false;
      }
    });
    this.$store.commit('setSettings', this.settings);
    this.$store.commit('setUser', this.user);
    this.$store.commit('setCurrentLanguage', this.language);
    this.moment.updateLocale(this.language, {
      week : {
        dow : 0
      }
    });
  },
  mounted() {
    var mainBlockObj = this.$refs['bookit-app-content'].getBoundingClientRect();
    this.$store.commit('setParentBlockWidth', mainBlockObj.width);
  }
}