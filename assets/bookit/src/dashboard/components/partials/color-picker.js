import { Chrome } from 'vue-color';

export default {
  template: `
    <div :class="['color-picker', {disabled: !settings_object.custom_colors_enabled}]">
      <label>{{ label }}</label>
      <div class="input-group" ref="color_picker">
        <input
          class="form-control color-picker-container"
          :style="{'background-color': settings_object.custom_colors[object_key]}"
          @click="settings_object.custom_colors_enabled ? togglePicker() : {}" readonly="readonly" />
        
        <span class="color-value">{{ settings_object.custom_colors[object_key] }}</span>
        <div class="input-group-append">
          <span v-if="show_color_picker" class="cancel-button" @click="settings_object.custom_colors_enabled ? cancel() : {}">
            {{ translations.cancel }}
          </span>
          <span v-else class="cancel-button" @click="settings_object.custom_colors_enabled ? setDefault() : {}">
            {{ translations.reset }}
          </span>
        </div>
        <chrome-picker v-if="show_color_picker" :value="settings_object.custom_colors[object_key]" @input="updateColorValue($event)"></chrome-picker>
      </div>
    </div>
  `,
  components: {
    'chrome-picker': Chrome
  },
  data: () => ({
    old_value: '',
    show_color_picker: false,
    translations: bookit_window.translations,
  }),
  props: {
    label: {
      type: String,
      required: true
    },
    settings_object: {
      type: Object,
      required: true
    },
    object_key: {
      type: String,
      required: true
    },
    default_value: {
      type: String,
      required: true
    }
  },
  mounted() {
    this.old_value = this.settings_object.custom_colors[this.object_key];
  },
  methods: {
    updateColorValue(colors) {
      if ( colors.rgba.a === 1 ) {
        this.settings_object.custom_colors[this.object_key] = colors.hex;
      } else {
        this.settings_object.custom_colors[this.object_key] = `rgba(${colors.rgba.r}, ${colors.rgba.g}, ${colors.rgba.b}, ${colors.rgba.a})`;
      }
    },
    togglePicker() {
      document.addEventListener('click', this.documentClick);
      this.show_color_picker = !this.show_color_picker;
    },
    documentClick(e) {
      const el = this.$refs.color_picker,
        target = e.target;
      if ( el !== target && !el.contains(target) ) {
        this.show_color_picker = false;
      }
    },
    cancel() {
      this.show_color_picker = false;
      this.settings_object.custom_colors[this.object_key] = this.old_value;
    },
    setDefault() {
      this.settings_object.custom_colors[this.object_key] = this.default_value;
    }
  }
}