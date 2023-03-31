import addon_feature from '@dashboard-partials/addon-feature';

export default {
    name: 'paypal',
    template: `
      <div>
        <div class="setting-row pt-30">
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <span class="label">PayPal</span>
          </div>
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <div class="switcher">
              <div class="bookit-switch">
                <input type="checkbox" v-model="settings_object.payments.paypal.enabled" :disabled="pro_disabled">
                <label></label>
              </div>
            </div>
          </div>
          
          
<!--          <div v-if="!pro_installed" class="form-group col-2">-->
<!--            <addon_feature :addonName="bookitPaymentAddon.title" :addonLink="bookitPaymentAddon.link"></addon_feature>-->
<!--          </div>-->
          
          <div v-if="!pro_disabled" :class="['form-group col-4', {disabled: pro_disabled}]">
            <template v-if="settings_object.payments.paypal.enabled">
              <label>PayPal IPN Setup</label>
              <div>You need to use this URL for your <a href="https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNSetup/" target="_blank">IPN Listener Settings</a>:</div>
              <div class="mt-small"><code>{{ ipn_url }}</code></div>
              <br>
              <label>PayPal Email</label>
              <input type="email" v-model="settings_object.payments.paypal.email" required>
              <br><br>
              <label>PayPal Mode</label>
              <select v-model="settings_object.payments.paypal.mode" required>
                <option value="live">Live</option>
                <option value="sandbox">Sandbox</option>
              </select>
            </template>
          </div>
      </div>
          
        <div class="setting-row pt-30">
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <span class="label">Stripe</span>
          </div>
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <div class="switcher">
              <div class="bookit-switch">
                <input type="checkbox" v-model="settings_object.payments.stripe.enabled" :disabled="pro_disabled">
                <label></label>
              </div>
            </div>
          </div>
<!--          <div v-if="!pro_installed" class="form-group col-2">-->
<!--            <addon_feature :addonName="bookitPaymentAddon.title" :addonLink="bookitPaymentAddon.link"></addon_feature>-->
<!--          </div>-->
          <div v-if="!pro_disabled" :class="['form-group col-4', {disabled: pro_disabled}]">
                <template v-if="settings_object.payments.stripe.enabled">
                  <label>Stripe Publish Key</label>
                  <input type="text" v-model="settings_object.payments.stripe.publish_key" required>
                  <br><br>
                  <label>Stripe Secret Key</label>
                  <input type="text" v-model="settings_object.payments.stripe.secret_key" required>
                </template>
              </div>
        </div>
      
        <div class="setting-row pt-30">
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <span class="label">WooCommerce</span>
          </div>
          <div :class="['form-group small no-margin', {disabled: pro_disabled}]">
            <div class="switcher">
              <div class="bookit-switch">
                <input type="checkbox" @change="checkWoocommerce($event)" v-model="settings_object.payments.woocommerce.enabled" :disabled="pro_disabled">
                <label></label>
              </div>
            </div>
          </div>
<!--          <div v-if="!pro_installed" class="form-group col-2">-->
<!--            <addon_feature :addonName="bookitPaymentAddon.title" :addonLink="bookitPaymentAddon.link"></addon_feature>-->
<!--          </div>-->
          <div v-if="!pro_disabled" :class="['form-group col-4', {disabled: pro_disabled}]">
                <template v-if="settings_object.payments.woocommerce.enabled">
                  <label>WooCommerce Product</label>
                  <select v-model="settings_object.payments.woocommerce.product_id" required>
                    <option v-for="product in woocommerce_products" :value="product.id">{{ product.title }}</option>
                  </select>
                </template>
          </div>
        </div>
      
      </div>
    `,
    components: {
        addon_feature,
    },
    data: () => ({
        translations: bookit_window.translations,
        show_woocommerce_alert: false,
        ipn_url: `${bookit_window.site_url}/?stm_bookit_check_ipn=1`,
        bookitPaymentAddon: {'title': 'Bookit Payments', 'link': 'https://stylemixthemes.com/wordpress-appointment-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020'},
    }),

    props: {
        settings_object: {
            type: Object,
            required: true
        },
        pro_disabled: {
            type: Boolean,
            required: true
        },
        pro_installed: {
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
    methods: {
        checkWoocommerce(event) {

            if ( this.woocommerce_enabled == false && event.target.checked) {
                this.show_woocommerce_alert = true;
            }else {
                this.show_woocommerce_alert = false;
            }

        },
    }
}