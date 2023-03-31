export default {
  template: `
    <div class="">
      <div class="setting-row no-border pt-30 pb-10">
        <div class="form-group small">
          <label>{{ translations.category }}</label>
          <select v-model="shortcode.category_id">
            <option :value="undefined">-- {{ translations.all }} {{ translations.categories }} --</option>
            <option v-for="category in categories" :value="category.id">{{ category.name }}</option>
          </select>
        </div>
        <div class="form-group small">
          <label>{{ translations.service }}</label>
          <select v-model="shortcode.service_id">
            <option :value="undefined">-- {{ translations.filter_by_service }} --</option>
            <option v-for="service in services" :value="service.id">{{ service.title }}</option>
          </select>
        </div>
        <div class="form-group small">
          <label>{{ translations.staff }}</label>
          <select v-model="shortcode.staff_id">
            <option :value="undefined">-- {{ translations.filter_by_staff }} --</option>
            <option v-for="item in availableStaff" :value="item.id">{{ item.full_name }}</option>
          </select>
        </div>
        <div class="form-group small">
          <label>{{ translations.calendar_theme }}</label>
          <select v-model="shortcode.calendar_theme">
            <option :value="undefined">-- {{ translations.theme }} --</option>
            <option value="default">{{ translations.calendar_view_default }}</option>
            <option value="step_by_step">{{ translations.calendar_view_step_by_step }}</option>
            <option value="booking_exam">{{ translations.calendar_view_booking_exam }}</option>
          </select>
        </div>
      </div>
      <div class="setting-row pt-10 pb-10 no-border">
        <div class="code">
          <code>{{ generated_shortcode }}</code>
          <button class="button-copy" type="button" @click="copyURL()">
            <i class="copy-icon"></i>{{ translations.copy }}
          </button>
          <div class="help" v-if="linkCopy">
            <div class="help-tip" v-html="translations.url_copied">
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  components: {

  },
  data: () => ({
    linkCopy: false,
    shortcode: {},
    translations: bookit_window.translations,
  }),
  props: {
    settings_object: {
      type: Object,
      required: true
    },
  },
  computed: {
    availableStaff () {
      let availableStaff = [...this.staff];
      if ( this.shortcode.hasOwnProperty('service_id') && this.shortcode.service_id != null ) {
        availableStaff = this.staff.filter( staff => {
          return staff.staff_services.some( staff_service => staff_service.id == this.shortcode.service_id )
        });
      }
      return availableStaff;
    },
    categories () {
      return this.$store.getters.getCategories;
    },
    generated_shortcode () {
      const category  = ( this.shortcode.category_id !== undefined )? ` category="${this.shortcode.category_id}"` : ``;
      const service   = ( this.shortcode.service_id !== undefined )? ` service="${this.shortcode.service_id}"` : ``;
      const staff     = ( this.shortcode.staff_id !== undefined )? ` staff="${this.shortcode.staff_id}"` : ``;
      const theme     = ( this.shortcode.calendar_theme !== undefined )? ` theme="${this.shortcode.calendar_theme}"` : ``;
      return `[bookit${category}${service}${staff}${theme}]`;
    },
    services () {
      let services = this.$store.getters.getServices;
      if ( this.shortcode.hasOwnProperty('category_id') && this.shortcode.category_id != null ) {
        services = services.filter( item => parseInt(item.category_id) === parseInt(this.shortcode.category_id) );
      }
      return services;
    },
    staff () {
      let staff = this.$store.getters.getStaff;
      staff.map((staff) => {
        staff.staff_services  = JSON.parse(staff.staff_services) || [];
        staff.working_hours   = JSON.parse( JSON.stringify( JSON.parse(staff.working_hours) ).replace(/"NULL"/gi, null) ) || [];
      });
      return staff;
    },
  },
  created() {},
  methods: {
    copyURL() {
      var input = document.body.appendChild(document.createElement("input"));
      input.value = this.generated_shortcode;
      input.select();
      document.execCommand('copy');
      input.parentNode.removeChild(input);

      this.linkCopy = true;
      setTimeout(() => {
        this.linkCopy = false;
      }, 1000);
    },
  },
  watch: {
    'shortcode.category_id'() {
      if ( this.shortcode.hasOwnProperty('service_id') && this.shortcode.service_id !== undefined ) {
        this.shortcode.service_id = null;
        this.shortcode.staff_id   = null;
      }
    },
    'shortcode.service_id'() {
      if ( this.shortcode.hasOwnProperty('staff_id') && this.shortcode.staff_id !== undefined ) {
        if ( this.shortcode.hasOwnProperty('service_id') && this.shortcode.service_id != null ) {
          var isStaffCorrect = this.availableStaff.some( staff => {
            if ( this.shortcode.staff_id == staff.id ) {
              return staff.staff_services.some( staff_service => staff_service.id == this.shortcode.service_id );
            }
          });
          if ( !isStaffCorrect ){
            this.shortcode.staff_id   = null;
          }
        }
      }
    },
  }
}