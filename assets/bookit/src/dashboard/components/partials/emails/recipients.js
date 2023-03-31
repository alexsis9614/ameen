
export default {
  template: `
    <div class="form-group no-margin" v-if="settings_object.emails[template].enabled">
      <p class="label">{{ title }}</p>
      <small>
        <input type="text" class="small" value="[admin]" @click="appendTo(template, 'to', $event.target.value)" size="6" readonly>
        <input type="text" class="small" value="[staff]" @click="appendTo(template, 'to', $event.target.value)" size="5" readonly>
      </small>
      <input type="text" v-model="settings_object.emails[template].to" :disabled="!settings_object.emails[template].enabled">
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
      if ( !this.settings_object.emails[settings][object].includes(value) ) {
        this.settings_object.emails[settings][object] += value;
      }
    }
  }
}