import Tabs from '@dashboard-partials/tabs';
import Tab from '@dashboard-partials/tab';
import ColorPicker from '@dashboard-partials/color-picker';
import documentation from '@dashboard-partials/documentation';
import email_templates from '@dashboard-sections/settings/email-templates';
import payments from '@dashboard-sections/settings/payments';
import currency from '@dashboard-sections/settings/currency';
import shortcode from '@dashboard-sections/settings/shortcode';
import ImportExport from '@dashboard-sections/settings-import-export.js';
import googleCalendar from '@dashboard-addons/google-calendar';

export default {
  template: `
    <form @submit.prevent="save" class="bookit-wrapper bookit-settings">
      <documentation></documentation>
      
      <tabs>
        <tab :name="translations.general" :is_main_tab="true" :selected="true">
          <div class="setting-row">
            <p class="title">{{ translations.main_settings }}</p>
            <div class="form-group small">
              <label>{{ translations.booking_type }}</label>
              <select v-model="settings_object.booking_type">
                <option value="registered">{{ translations.booking_type_registered }}</option>
                <option value="guest">{{ translations.booking_type_guest }}</option>
              </select>
            </div>
            <div class="form-group small">
              <label> {{ translations.time_slot_duration_title }}</label>
              <select v-model="settings_object.time_slot_duration">
                <option v-for="(value, title) in time_slot_options" :value="value">
                  {{ translations[title] }}
                </option>
              </select>
            </div>
          </div>

          <div class="setting-row">
            <p class="title">{{ translations.email_settings }}</p>
            <div class="form-group small">
              <label>{{ translations.sender_name }}</label>
              <input type="text" :placeholder="translations.sender_name" v-model="settings_object.sender_name" class="form-control">
            </div>
            <div class="form-group small">
              <label> {{ translations.sender_email }}</label>
              <input type="email" :placeholder="translations.sender_email" v-model="settings_object.sender_email" class="form-control">
            </div>
          </div>
          <div class="setting-row column">
            <p class="title" v-html="translations.theme_and_style"></p>
            <div class="form-group medium">
              <label class="full">{{ translations.calendar_theme }}</label>
              <div class="image-radio" @click="settings_object.calendar_view = 'default'">
                <div class="image default"></div>
                <div class="field">
                  <input type="radio" class="" v-model="settings_object.calendar_view" value="default">
                  <span>{{ translations.calendar_view_default }}</span>
                </div>
              </div>
              <div class="image-radio" @click="settings_object.calendar_view = 'step_by_step'">
                <div class="image step"></div>
                <div class="field">
                  <input type="radio" class="" v-model="settings_object.calendar_view" value="step_by_step">
                  <span>{{ translations.calendar_view_step_by_step }}</span>
                </div>
              </div>
            </div>
            <div class="form-group medium mt-10 no-margin">
              <div class="switcher">
                <div class="bookit-switch">
                  <input type="checkbox" v-model="settings_object.hide_header_titles">
                  <label></label>
                </div>
                {{ translations.hide_calendar_header_titles }}
              </div>
            </div>
            <div class="form-group small no-margin mt-10">
              <div class="switcher">
                <div class="bookit-switch">
                  <input type="checkbox" v-model="settings_object.custom_colors_enabled">
                  <label></label>
                </div>
                {{ translations.use_custom_colors }}
              </div>
            </div>
            <div class="form-group no-margin mt-10">
              <div class="switcher">
                <div class="bookit-switch">
                  <input type="checkbox" v-model="settings_object.hide_from_for_equal_service_price">
                  <label></label>
                </div>
                {{ translations.hide_from_for_service_price }}
              </div>
            </div>
            
            <div class="form-group large no-margin mt-10">
              <div class="custom-colors">
                <color-picker :label="translations.base_color" :settings_object="settings_object" object_key="base_color" default_value="#006666"></color-picker>
                <color-picker :label="translations.base_bg_color" :settings_object="settings_object" object_key="base_bg_color" default_value="#f0f8f8"></color-picker>
                <color-picker :label="translations.highligth_color" :settings_object="settings_object" object_key="highlight_color" default_value="#ffd400"></color-picker>
                <color-picker :label="translations.white_color" :settings_object="settings_object" object_key="white_color" default_value="#ffffff"></color-picker>
                <color-picker :label="translations.dark_color" :settings_object="settings_object" object_key="dark_color" default_value="#272727"></color-picker>
              </div>
            </div>
          </div>
          <div class="setting-row">
            <p class="title">{{ translations.setup_settings }}</p>
            <div class="form-group small mt-10 no-margin">
              <div class="help-label-block">
                <div class="switcher help-label">
                  <div class="bookit-switch">
                    <input type="checkbox" v-model="settings_object.clean_all_on_delete">
                    <label></label>
                  </div>
                  <span>{{ translations.clean_all_on_delete_title }}</span>
                </div>
                <span class="round-link-icon" @mouseover="showHelp.clean_all_on_delete = true" @mouseleave="showHelp.clean_all_on_delete = false">!</span>
                <div class="help" v-if="showHelp.clean_all_on_delete">
                  <div class="help-tip">
                    {{ translations.clean_all_on_delete_help }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button type="submit" class="bookit-button">{{ translations.save }} {{ translations.settings }}</button>
        </tab>
        
        <tab :name="translations.currency" :is_main_tab="true">
          <currency :settings_object="settings_object" :currencies="currencies"></currency>
          <button type="submit" class="bookit-button">{{ translations.save }} {{ translations.settings }}</button>
        </tab>
        
        <tab :name="translations.payments" :is_main_tab="true">
          
          <payments :settings_object="settings_object" :paymentAddons="tabAddons.payments" :woocommerce_products="woocommerce_products" :woocommerce_enabled="woocommerce_enabled" :pro_disabled="pro_disabled" :pro_installed="pro_installed"></payments>
          
          <button type="submit" class="bookit-button">{{ translations.save }} {{ translations.settings }}</button>
        </tab>
        
        <tab :name="translations.email_templates" :is_main_tab="true" div_class="no-padding">
          <email_templates :settings_object="settings_object"></email_templates>
          <button type="submit" class="bookit-button ml">{{ translations.save }} {{ translations.settings }}</button>
        </tab>
        
        <tab :name="translations.shortcode_generator" :is_main_tab="true">
          <shortcode :settings_object="settings_object"></shortcode>
        </tab>
        
        <tab :name="translations.import_export" :is_main_tab="true">
          <ImportExport></ImportExport>
        </tab>
        
        <tab :div_class="!addon.data.installed ? 'not-installed': ''" :disabled="!addon.data.installed" :is_main_tab="true" :name="addon.data.title" v-if="addon.data.tab == 'self'" v-for="addon in addons" :key="addon.name">
          <component :is="addon.name" :addon="addon.data" :freemius="addon.freemius"></component>
        </tab>
        
      </tabs>
    </form>
  `,
  components: {
    tabs: Tabs,
    tab: Tab,
    'color-picker': ColorPicker,
    googleCalendar,
    documentation,
    email_templates,
    payments,
    currency,
    ImportExport,
    shortcode
  },
  data: () => ({
    translations: bookit_window.translations,
    settings_object: {},
    showHelp: { 'clean_all_on_delete': false},
    /** addons with settings data not in separate tab */
  }),
  props: {
    addons:{
      type: Array,
      required: true
    },
    categories: {
      type: Array,
      required: false,
      default: []
    },
    currencies: {
      type: Array,
      required: true,
      default: []
    },
    pro_disabled: {//todo remove
      type: Boolean,
      required: true
    },
    pro_installed: {//todo remove
      type: Boolean,
      required: true
    },
    services: {
      type: Array,
      required: false,
      default: []
    },
    settings: {
      type: Object,
      required: true
    },
    staff: {
      type: Array,
      required: false,
      default: []
    },
    time_slot_options: {
      type: Object,
      required: true,
      default: {}
    },
    calendar_view_options: {
      type: Array,
      required: true,
      default: {}
    },
    woocommerce_products: {
      type: Array,
      required: false,
      default: []
    },
    woocommerce_enabled: {
      type: Boolean,
      required: true,
    }
  },
  computed: {
    tabAddons () {
      var addons = {};
      this.addons.forEach(addon => {
        if( addon.data.tab != 'self' ){
          if (typeof addons[addon.data.tab] == "undefined" || !(addons[addon.data.tab] instanceof Array)) {
            addons[addon.data.tab] = [];
          }
          addons[addon.data.tab].push(addon);
        }
      })
      return addons;
    }
  },
  created() {
    this.settings_object = this.setCorrectBooleanInObject({...this.settings});
    this.$store.commit('setCategories', this.categories);
    this.$store.commit('setServices', this.services);
    this.$store.commit('setStaff', this.staff);
  },
  methods: {
    save() {
      this.showEditAddForm        = false;
      this.settings_object.nonce  = bookit_window.nonces.bookit_save_settings;

      /** check is correct currency value **/
      var currency_exist = this.currencies.filter(item => item.value.toLowerCase().indexOf(this.settings_object.currency.toLowerCase()) > -1);
      if ( currency_exist.length <= 0 ) {
        this.$store.commit('setErrors', {'currency': this.translations.currency_error});
        return;
      }

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_save_settings`, this.generateFormData(this.settings_object), this.getPostHeaders()).then((res) => {
        let response = res.data;

        if (response.data.errors && Object.keys(response.data.errors).length > 0){
          this.$store.commit('setErrors', response.data.errors);
        }
        this.$toasted.show(response.data.message, {
          type: (response.success) ? 'success' : 'error'
        });
      });
    },
    appendTo(settings, object, value) {
      this.settings_object.emails[settings][object] += value;
    }
  }
}