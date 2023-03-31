
export default {
  template: `
    <div class="form-group no-margin mt-20" v-if="settings_object.emails[template].enabled">
      <p class="label">{{ title }}</p>
      <small>
        <input type="text" class="small" value="[appointment_id]" @click="appendTo(template, 'subject', $event.target.value)" size="15" readonly>
        <input type="text" class="small" value="[service_title]" @click="appendTo(template, 'subject', $event.target.value)" size="12" readonly>
        <input type="text" class="small" value="[customer_name]" @click="appendTo(template, 'subject', $event.target.value)" size="15" readonly>
      </small>
      <input type="text" v-model="settings_object.emails[template].subject" :disabled="!settings_object.emails[template].enabled">
    </div>
  `,
  props: {
    settings_object: {
      type: Object,
      required: true
    },
    template: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    }
  },
  methods: {
    appendTo(settings, object, value) {
      this.settings_object.emails[settings][object] += value;
    }
  }
}