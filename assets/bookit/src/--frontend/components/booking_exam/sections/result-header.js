export default {
  name: 'navigation',
  template: `
    <div class="result-confirmation">
      <div class="success-icon"></div>
      <p class="success-title">{{ translations.success_booking }}</p>
      <p>{{ translations.booking_email_sent }}</p>
    </div>`,
  components: {
  },
  props: {},
  data: () => ({
    translations: bookit_window.translations
  }),
  computed: {},
  created() {
  },
  methods: {
  },
}