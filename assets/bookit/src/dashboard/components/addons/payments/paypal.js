export default {
    name: 'paypal',
    template: `
      <div>
        <div :class="['setting-row no-border pt-30 pb-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div :class="['form-group small no-margin', {disabled: ( !addon.installed || !addon.isCanUse ) }]">
            <span class="label">{{ paymentName }}</span>
          </div>
          <div :class="['form-group small no-margin', {disabled: ( !addon.installed || !addon.isCanUse ) }]">
            <div class="switcher">
              <div class="bookit-switch">
                <input type="checkbox" v-model="settings_object.payments.paypal.enabled" :disabled="!addon.installed">
                <label></label>
              </div>
              <span class="label for-switcher" v-html=" settings_object.payments.paypal.enabled ? translations.enabled : translations.disabled"></span>
            </div>
          </div>
        </div>
        
        <div :class="['setting-row no-border pt-10 pb-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div :class="['form-group medium', { disabled: ( !addon.installed || !addon.isCanUse ) }]">
            <div class="copy-block" v-if="settings_object.payments.paypal.enabled || ( !addon.installed || !addon.isCanUse ) ">
                <label>{{ translations.paypal_ipn_setup }}</label>
                <div>
                  {{ translations.paypal_ipn_pre_link }} 
                  <a ref="ipnLink" href="https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNSetup/" target="_blank">
                    {{ translations.paypal_ipn_link_txt }}
                  </a>
                </div>
                <div class="code">
                  <code>{{ ipn_url }}</code>
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
        </div>
        
        <div v-if="settings_object.payments.paypal.enabled || ( !addon.installed || !addon.isCanUse ) " :class="['setting-row pt-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div class="form-group small">
            <label>{{ translations.paypal_email }}</label>
            <input type="email" v-model="settings_object.payments.paypal.email" required :disabled="!addon.installed">
          </div>
          <div class="form-group small">
            <label>{{ translations.paypal_mode }}</label>
            <select v-model="settings_object.payments.paypal.mode" required :disabled="!addon.installed">
              <option value="live">{{ translations.paypal_mode_live }}</option>
              <option value="sandbox">{{ translations.paypal_mode_sandbox }}</option>
            </select>
          </div>
        </div>
      </div>
  `,
    data: () => ({
        linkCopy: false,
        translations: bookit_window.translations,
        ipn_url: `${bookit_window.site_url}/?stm_bookit_check_ipn=1`,
    }),
    created() {},
    computed: {
        paymentName() {
            return this.payment.name.replace('pal', function(part, index){
                var partName = part.split('');
                partName[0] = partName[0].toUpperCase();
                partName = partName.join('');
                return index == 0 ? part.toLowerCase() : partName;
            });
        },
    },
    props: {
        settings_object: {
            type: Object,
            required: true
        },
        payment: {
            type: Object,
            required: true
        },
        addon: {
            type: Object,
            required: true
        },
    },
    methods: {
      copyURL() {
        var input = document.body.appendChild(document.createElement("input"));
        input.value = this.$refs.ipnLink.href;;
        input.select();
        document.execCommand('copy');
        input.parentNode.removeChild(input);

        this.linkCopy = true;
          setTimeout(() => {
              this.linkCopy = false;
          }, 1000);
      }
    }
}