export default {
  template: `
    <div>
      <div :class="['custom-select', { 'open': isOptionOpen } ]" @click="isOptionOpen = !isOptionOpen;" >
        <div class="value">
          {{ selectedOption.text }}
        </div>
        <div :class="['custom-options', { 'open': isOptionOpen } ]">
            <span v-for="option in options" class="custom-option" :data-value="option.value" @click="selectOption( $event, option )">
              {{ option.text }}
            </span>
        </div>
      </div>
      <a target="_blank" :class="buttonClass" :href="selectedOption.url">
        {{ buttonText }}
      </a>
    </div>
  `,
  data: () => ({
    translations: bookit_window.translations,
    isOptionOpen: false,
    selectedOption: {},
  }),
  props: {
    options: {
      type: Array,
      required: true
    },
    selectedValue: {
      type: String,
      required: false,
    },
    buttonClass:{
      type: String,
      required: true,
    },
    buttonText:{
      type: String,
      required: true,
    }
  },
  created() {
    this.selectedOption = this.options[Object.keys(this.options)[0]];
  },
  methods: {
    selectOption( event, option ) {
      this.selectedOption = option;
      this.$emit('selectCallback', option);
    },
  }
}