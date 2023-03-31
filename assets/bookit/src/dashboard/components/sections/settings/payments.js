import paypal from '@dashboard-addons/payments/paypal';
import stripe from '@dashboard-addons/payments/stripe';
import woocommerce from '@dashboard-addons/payments/woocommerce';
import addon_feature from '@dashboard-partials/addon-feature';

import temp_pro from '@dashboard-addons/payments/temp-pro';

export default {
  template: `
    <div class="payments-tabs">
      <div class="setting-row pt-10">
        <div class="form-group small no-margin">
          <span class="label">{{ translations.pay_locally }}</span>
        </div>
        <div class="form-group small no-margin">
          <div class="switcher">
            <div class="bookit-switch">
              <input type="checkbox" v-model="settings_object.payments.locally.enabled">
              <label></label>
            </div>
          </div>
          <span class="label for-switcher" v-html=" settings_object.payments.locally.enabled ? translations.enabled : translations.disabled"></span>
        </div>
      </div>

      
      <!-- IF HAVE BOOKIT PRO -- this part is temp-->
<!--      <temp_pro v-if="pro_installed && !pro_disabled" :settings_object="settings_object" :woocommerce_products="woocommerce_products" :woocommerce_enabled="woocommerce_enabled" :pro_disabled="pro_disabled" :pro_installed="pro_installed"></temp_pro>-->
      <!-- IF HAVE PAYMENT ADDON ( WAS BOOKIT PRO )-->
      
      <!-- Load bookit payments addon data -->
      <div :class="{'no-addon':!paymentAddons[0].data.isCanUse }">
        <component v-for="payment in paymentAddons[0].data.settings.payments" :key="payment.name" :is="payment.name" :addon="paymentAddons[0].data" :payment="payment" :settings_object="settings_object"></component>
      </div>
      
      <!-- IF ADDON NOT INSTALLED -->
      <div class='' v-if="showNotInstalledAddon()">
        <addon_feature :freemius="paymentAddons[0].freemius" :addon="paymentAddons[0]" addonSlug="payments" :addonLink="paymentAddons[0].data.link"></addon_feature>
      </div>
      <!-- IF ADDON NOT INSTALLED END -->

      <!-- IF ADDON INSTALLED BUT NO LICENSE-->
      <div class='no-addon' v-if="showActivationLink()">
        <div class="addon-feature activation">
          <span class="addon-icon">
            <i :class="paymentAddons[0].name"></i>
          </span>
          <h2 class="title">{{ paymentAddons[0].data.title }}</h2>
          <p class="activation-link" v-html="paymentAddons[0].data.activationLink"></p>
        </div>
      </div>
      <!-- IF ADDON INSALLED BUT NO LICENSE END-->
    </div>
  `,
  components: {
    paypal,
    stripe,
    woocommerce,
    addon_feature,
    temp_pro // temp part , remove later
  },
  data: () => ({
    translations: bookit_window.translations,
  }),
  props: {
    paymentAddons: {
      type: Array,
      required: false,
    },
    settings_object: {
      type: Object,
      required: true
    },
    pro_installed: {
      type: Boolean,
      required: true
    },
    pro_disabled: {
      type: Boolean,
      required: true
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
  created() {},
  methods: {
    showActivationLink() {
      return ( this.paymentAddons[0].data.installed && !this.paymentAddons[0].data.isCanUse );
    },
    showNotInstalledAddon() {
      return !this.paymentAddons[0].data.installed && !this.pro_installed;
    },
  }
}