export default {
    name: 'paypal',
    template: `
      <div>
        <div :class="['setting-row no-border pb-10 pt-30', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div :class="['form-group small no-margin',{disabled: ( !addon.installed || !addon.isCanUse ) }]">
          <span class="label">{{ payment.name }}</span>
        </div>
        <div :class="['form-group small no-margin', {disabled: ( !addon.installed || !addon.isCanUse ) }]">
          <div class="switcher">
            <div class="bookit-switch">
              <input type="checkbox" v-model="settings_object.payments.stripe.enabled" :disabled="!addon.installed">
              <label></label>
            </div>
          </div>
          <span class="label for-switcher" v-html=" settings_object.payments.stripe.enabled ? translations.enabled : translations.disabled"></span>
        </div>
        </div>
        <div v-if="settings_object.payments.stripe.enabled || ( !addon.installed || !addon.isCanUse ) " :class="['setting-row pt-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div class="form-group small">
            <label>{{ translations.stripe_publish_key }}</label>
            <input type="text" v-model="settings_object.payments.stripe.publish_key" required :disabled="!addon.installed">
          </div>
          <div class="form-group small">
            <label>{{ translations.stripe_secret_key }}</label>
            <input type="text" v-model="settings_object.payments.stripe.secret_key" required :disabled="!addon.installed">
          </div>
        </div>
      </div>
    `,
    data: () => ({
        translations: bookit_window.translations,
    }),
    created() {},
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
    methods: {}
}