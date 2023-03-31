
export default {
  template: `
    <div :class="['form-group', {'no-margin': !settings_object.emails[template].enabled}]">
      <div class="switcher">
        <div class="bookit-switch">
          <input type="checkbox" v-model="settings_object.emails[template].enabled">
          <label></label>
        </div>
        {{ title }}
      </div>
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