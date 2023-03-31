export default {
  name: 'navigation',
  template: `
    <div class="calendar-step-nav">
      <div @click="selectStep( stepData )" v-for="(stepData, stepIndex) in navigation" :key="stepIndex" :class="['calendar-step-item', {'active': currentStepKey == stepData.key}, {'disabled': isDisabled || !isStepEnabled( stepData )}]">
        <div class="step">
          <span v-if="( currentStepIndex > stepIndex || (stepData.hasOwnProperty('class') && stepData.class == 'skip') ) && !isSmallParent" class="selected-icon"></span>
          <span v-else class="step-num">{{stepIndex + 1}}</span>
          <span class="step-title" v-if="( isSmallParent && currentStepKey == stepData.key ) || !isSmallParent">{{ stepData.menu }}</span>
        </div>
      </div>
    </div>`,
  components: {
  },
  data: () => ({
    translations: bookit_window.translations
  }),
  props: {
    isDisabled: {
      type: Boolean,
      required: false,
      default: false
    },
    isSmallParent:{
      type: Boolean,
      default: false
    }
  },
  computed: {
    appointment() {
      return this.$store.getters.getAppointment;
    },
    currentStepKey() {
      return this.$store.getters.getCurrentStepKey;
    },
    currentStepIndex() {
      return this.navigation.findIndex(step => step.key === this.currentStepKey);
    },
    errors: {
      get() {
        return this.$store.getters.getErrors;
      },
      set( errors ) {
        this.$store.commit('setErrors', errors);
      }
    },
    navigation() {
      return this.$store.getters.getStepNavigation;
    },
    settings () {
      return this.$store.getters.getSettings;
    },
  },
  created() {
  },
  methods: {
    isStepEnabled ( step ) {
      if ( step.hasOwnProperty('class') && step.class == 'skip' ) {
        return false;
      }
      return this.isArrayItemsInArray( step.requiredFields, Object.keys( this.appointment ));
    },
    selectStep( step ) {
      if ( !this.isStepEnabled(step) || this.isDisabled ) {
        return;
      }

      this.$store.commit('setErrors', {});
      var currentStep = this.navigation.find(step => step.key === this.currentStepKey);
      if ( currentStep.validation ) {
        if ( this.currentStepKey == 'detailsForm' || this.currentStepKey == 'confirmation') {
          var errors  = this.formValidation( this.appointment, this.settings );
          this.errors = errors;
        }
      }

      var choosenStepIndex = this.navigation.findIndex(st => st.key === step.key);
      if ( ( this.isStepEnabled( step ) && ( Object.keys(this.errors).length === 0 ) ) || this.currentStepIndex > choosenStepIndex) {
        this.$store.commit('setCurrentStepKey', step.key);
      }
    },
  },
}