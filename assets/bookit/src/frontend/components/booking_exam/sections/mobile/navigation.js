export default {
  name: 'navigation',
  template: `
    <div :class="['calendar-step-nav', {'hidden': showDateTime}]">
      <div class="prev-step" @click="$emit('previousStep')" v-if="( currentStepIndex != 0 && currentStep.key != 'result' )">
        <i class="left-icon"></i>
      </div>
      <div :class="['calendar-step-item',{'first': currentStepIndex == 0}]">
        <span class="step-num">{{ currentStepIndex + 1}}</span>
        {{ currentStep.menu }}
      </div>
      <div :class="['next-step', {'disabled': !isNextEnabled( )}]" @click="$emit('nextStep');" v-if="currentStep.key != 'result' || currentStep.key != 'confirmation'">
        <i class="right-icon"></i>
      </div>
    </div>`,
  components: {
  },
  data: () => ({}),
  computed: {
    appointment() {
      return this.$store.getters.getAppointment;
    },
    currentStep() {
      var currentStepKey = this.$store.getters.getCurrentStepKey;
      return this.navigation.find(step => step.key === currentStepKey);
    },
    currentStepIndex() {
      return this.navigation.findIndex(step => step.key === this.currentStep.key);
    },
    navigation() {
      return this.$store.getters.getStepNavigation;
    },
    showDateTime() {
      return this.$store.getters.getShowDateTime;
    },
  },
  created() {
  },
  methods: {
    isNextEnabled () {
      if ( this.navigation.hasOwnProperty(this.currentStepIndex + 1) ) {
        return this.isArrayItemsInArray( this.navigation[this.currentStepIndex + 1].requiredFields, Object.keys( this.appointment ));
      }
      return false;
    },
  },
}