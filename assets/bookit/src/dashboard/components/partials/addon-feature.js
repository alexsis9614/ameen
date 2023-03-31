import customSelect from '@dashboard-partials/custom-select';
export default {
  template: `
    <div class="addon-feature">
      <span class="addon-icon">
        <i :class="addonSlug"></i>
      </span>
      <h2 class="title">{{ freemius.title }}</h2>
      <div class="price">
        <p v-if="freemius.plan" :class="[ selectedPlan.licenses, 'active' ]">
            <span class="plan-price">$ {{ selectedPlan.annual_price }}</span>
          <span class="plan-period">/{{ translations.per_year }}</span>
        </p>
      </div>
      <p class="info">{{ freemius.descriptions }} <a target="_blank" href="#">{{ translations.learn_more }}</a></p>

      <div class="developer-info" v-if="freemius.latest">
          <span class="version-label">{{ translations.version }}</span>
          <span>{{ freemius.latest.version }}
            <a href="https://docs.stylemixthemes.com/bookit-calendar/changelog/" target="_blank">{{ translations.view_changelog }}</a>
          </span>
      </div>
      
      <div class="action">
        <customSelect v-on:selectCallback="selectCallback" :options="planOptions" buttonClass="buy" :buttonText="translations.buy"></customSelect>
      </div>
    </div>`,
  data: () => ({
    translations: bookit_window.translations,
    selectedPlan: {},
  }),
  components: {
    customSelect,
  },
  props: {
    addon: {
      type: Object,
      required: true
    },
    addonLink: {
      type: String,
      required: true
    },
    addonSlug: {
      type: String,
      required: true
    },
    freemius: {
      type: Object,
      required: true
    }
  },
  created() {
    this.selectedPlan = this.freemius.plan[Object.keys(this.freemius.plan)[0]];
  },
  computed: {
    title() {
      return this.translations.addon_feature.replace('%s', this.addon.title);
    },
    planOptions() {
      let options = [];
      Object.keys(this.freemius.plan).map((key) => {
        options.push({
          'id': this.freemius.plan[key].id,
          'value': this.freemius.plan[key].licenses,
          'text': this.freemius.plan[key].data.text,
          'url': this.freemius.plan[key].url
        })
      });
      return options;
    },
  },
  methods: {
    selectCallback( selectedOption ) {
      var selectedPlanKey = Object.keys(this.freemius.plan).filter((planKey) => {
        return this.freemius.plan[planKey].id == selectedOption.id;
      });
      this.selectedPlan = this.freemius.plan[selectedPlanKey];
    },
  }
}