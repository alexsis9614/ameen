export default {
  name: 'navigation',
  template: `
    <div v-if="!['category', 'service'].includes(currentStep.key  )" class="mobile">
      <div :class="['calendar-footer', {'hidden': showDateTime}]">
        <!-- RESULT PART, AFTER APPOINTMENT CREATED -->
        <div class="navigation" v-if="currentStep.key == 'result'">
          <div class="left">
            <button class="light" @click="$emit('newBooking')">
              <i class="revert-icon"></i> {{ translations.new_booking }}
            </button>
          </div>
          <div class="right">
            <div :class="['custom-select', { 'open': isOpenAddToCalendar } ]" @click="isOpenAddToCalendar = !isOpenAddToCalendar;" >
              <div class="value">
                <i class="calendar-icon"></i>
                {{ translations.add_to_calendar }}
              </div>
              <div :class="['custom-options', { 'open': isOpenAddToCalendar } ]">
              <span class="custom-option" @click="addToCalendar('google')">
                {{ translations.google_calendar }}
              </span>
                <span class="custom-option" @click="addToCalendar('ical')">
                {{ translations.i_cal }}
              </span>
              </div>
            </div>
          </div>
        </div>
        <!-- RESULT PART, AFTER APPOINTMENT CREATED| END -->
        <div v-else>
          <button @click="$emit('nextStep')" :class="{'disabled': !isNextEnabled( )}">
            {{ translations.continue }}<i class="right-icon"></i>
          </button>
        </div>
      </div>
    </div>
    `,
  components: {
  },
  props: {
    navigation: {
      type: Array,
      required: true,
      default: {}
    },
  },
  data: () => ({
    isOpenAddToCalendar: false,
    disabled: false,
    translations: bookit_window.translations
  }),
  computed: {
    appointment: {
      get() {
        return this.$store.getters.getAppointment;
      },
      set( appointment ) {
        this.$store.commit('setAppointment', appointment);
      }
    },
    currentStep() {
      var currentStepKey = this.$store.getters.getCurrentStepKey;
      return this.navigation.find(step => step.key === currentStepKey);
    },
    currentStepIndex() {
      return this.navigation.findIndex(step => step.key === this.currentStep.key);
    },
    errors: {
      get() {
        return this.$store.getters.getErrors;
      },
      set( errors ) {
        this.$store.commit('setErrors', errors);
      }
    },
    selectedService () {
      return this.$store.getters.getSelectedService;
    },
    showDateTime() {
      return this.$store.getters.getShowDateTime;
    },
  },
  created() {},
  methods: {
    addToCalendar( calendar ) {
      let link;
      if ( calendar == 'google' ) {
        this.generateGoogleCalendarLink();
      }
      if ( calendar == 'ical' ) {
        this.generateICalLink();
      }

    },
    isNextEnabled () {
      if ( this.navigation.hasOwnProperty(this.currentStepIndex + 1) ) {
        return this.isArrayItemsInArray( this.navigation[this.currentStepIndex + 1].requiredFields, Object.keys( this.appointment ));
      }
      return false;
    },
    /**
     * Search arraySeek items in arrayWhere
     * @param arraySeek
     * @param arrayWhere
     * @returns {boolean}
     */
    isArrayItemsInArray( arraySeek, arrayWhere ) {
      for (var i = 0; i < arraySeek.length; i ++ ) {
        if ( !arrayWhere.includes(arraySeek[i]) ) {
          return false;
        }
      }
      return true;
    },
    generateGoogleCalendarLink() {
      let url = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
      url += '&dates=' + this.moment.unix(this.appointment.start_time).format('YYYYMMDDTHHmmssZ')  + '/' + this.moment.unix(this.appointment.end_time).format('YYYYMMDDTHHmmssZ');
      url += '&text=' + encodeURIComponent('Appointment - ' + this.selectedService.title);
      url += '&sf=true&output=xml';
      window.open(url,'_blank');
    },
    generateICalLink() {
      let ics =
          "BEGIN:VCALENDAR\n" +
          "CALSCALE:GREGORIAN\n" +
          "METHOD:PUBLISH\n" +
          "PRODID:-//bookit plugin//EN\n" +
          "VERSION:2.0\n" +
          "BEGIN:VEVENT\n" +
          "UID:bookit-" + this.appointment.start_time + "\n" +
          "DTSTART:" +
          this.moment.unix(this.appointment.start_time).format('YYYYMMDDTHHmmss') + "Z\n" +
          "DTEND:" +
          this.moment.unix(this.appointment.end_time).format('YYYYMMDDTHHmmss') +"Z\n" +
          "SUMMARY: Appointment - " + this.selectedService.title + "\n" +
          "END:VEVENT\n" +
          "END:VCALENDAR";
        window.open( "data:text/calendar;charset=utf8," + escape(ics));
    },
    printWindow() {

      let html = document.querySelector('html');
      let body = document.querySelector('body');

      let originalHtml = {};
      let originalBody = {};
      const printCss = {'height': '100%', 'overflow': 'hidden'};
      for (const [style, value] of Object.entries(printCss)) {
        originalHtml[`${style}`] = html.style.getPropertyValue(`${style}`);
        originalBody[`${style}`] = html.style.getPropertyValue(`${style}`);
      }

      var printBlock    = document.getElementById('confirmation');
      let stylesHtml = '';
      const WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
        <head>
          ${stylesHtml}
        </head>
        <body>
          ${printBlock.innerHTML}
        </body>
      </html>`);
      WinPrint.focus();
      WinPrint.print();

      for (const [style, value] of Object.entries(printCss)) {
        html.style.setProperty(`${style}`, originalHtml[`${style}`]);
        body.style.setProperty(`${style}`, originalBody[`${style}`]);
      }
      WinPrint.close();
    }
  }
}