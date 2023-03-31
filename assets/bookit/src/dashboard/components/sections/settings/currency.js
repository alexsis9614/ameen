import autocomplete from '@dashboard-partials/autocomplete.js';

export default {
  template: `
    <div>
      <div class="setting-row no-border pt-10 pb-10">
        
        <autocomplete :error="errors.currency" v-on:setChoosenValue="setChoosenValue" :label="translations.currency" :current_value="settings_object.currency" :options="currencies"></autocomplete>
        <div class="form-group small">
          <label>{{ translations.currency_symbol }}</label>
          <input type="text" v-model="settings_object.currency_symbol">
        </div>
        <div class="form-group small">
          <label>{{ translations.currency_position }}</label>
          <select v-model="settings_object.currency_position">
            <option value="left">{{ translations.left }}</option>
            <option value="right">{{ translations.right }}</option>
          </select>
        </div>
      </div>
      <div class="setting-row no-border pt-10">
        <div class="form-group small">
          <label>{{ translations.thousands_separator }}</label>
          <input type="text" v-model="settings_object.thousands_separator">
        </div>
        <div class="form-group small">
          <label>{{ translations.decimal_separator }}</label>
          <input type="text" v-model="settings_object.decimals_separator">
        </div>
        <div class="form-group small">
          <label>{{ translations.number_of_decimals }}</label>
          <input type="text" v-model="settings_object.decimals_number">
        </div>
      </div>
    </div>
  `,
  components: {
    autocomplete
  },
  data: () => ({
    translations: bookit_window.translations,
  }),
  computed: {
    errors() {
      return this.$store.getters.getErrors;
    },
  },
  props: {
    settings_object: {
      type: Object,
      required: true
    },
    currencies: {
      type: Array,
      required: true
    }
  },
  methods: {
    setChoosenValue(currency_alias, symbol) {
      this.settings_object.currency = currency_alias;

      if ( symbol ){
        this.settings_object.currency_symbol = symbol;
      }

    }
  }
}