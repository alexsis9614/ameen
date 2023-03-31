import feedback from '@dashboard-partials/feedback'

export default {
  template: `
  <div>
    <a v-if="!has_feedback" href="#" class="bookit-documentation feedback" @click="showFeedbackForm($event)">
      <span class="doc-icon feedback"></span> {{ translations.feedback }}
    </a>
    <a :href="link" class="bookit-documentation" target="_blank">
      <span class="doc-icon doc"></span> {{ translations.documentation }}
    </a>
    <feedback v-if="show_feedback" v-on:setHasFeedback="setHasFeedback"></feedback>
  </div>
  `,
  components: {
    feedback,
  },
  data: () => ({
    link: 'https://docs.stylemixthemes.com/bookit-calendar/',
    has_feedback: bookit_window.has_feedback,
    translations: bookit_window.translations,
  }),
  computed: {
    show_feedback: {
      get() {
        return this.$store.getters.getShowFeedbackForm;
      },
      set(value) {
        this.$store.commit('setShowFeedbackForm', value);
      }
    },
  },
  methods: {
    showFeedbackForm( event ) {
      event.preventDefault();
      this.show_feedback = true;
    },
    setHasFeedback( value ) {
      this.has_feedback = value;
    }
  }
}