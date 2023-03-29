import Default from '@views/default/index'
import step_by_step from '@views/step_by_step/index'
import booking_exam from '@views/booking_exam/index'

export default {
  template: `
    <div :class="['bookit-app-content', view_type]" ref="bookit-app-content">
      <component :is="view_type" :navigation="navigation" :attributes="attributes"></component>
    </div>
  `,
  components: {
    Default,
    step_by_step,
    booking_exam,
  },
  computed: {
    view_type() {
      if ( this.isMobile() ) {
        return 'step_by_step';
      }
      /** theme - shortcode setting - first priority **/
      if ( this.theme && this.theme.length > 0 ){
        return this.theme;
      }
      if ( Object.keys(this.settings.calendar_view).length > 0) {
        return this.settings.calendar_view;
      }
      return 'default';
    },
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