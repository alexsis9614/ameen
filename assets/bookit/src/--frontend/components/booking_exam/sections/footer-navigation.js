export default {
  name: 'navigation',
  template: `
    <div class='footer' v-if="currentStepKey == 'result'">
      <div class="left">
        <button class="light" @click="$emit('newBooking')">
          <i class="revert-icon"></i> {{ translations.new_booking }}
        </button>
      </div>
      <div class="right">
        <!-- RESULT PART, AFTER APPOINTMENT CREATED -->
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
        <button class="ml-20" @click="printWindow">
          <i class="print-icon"></i>{{ translations.print }}
        </button>
        <!-- RESULT PART, AFTER APPOINTMENT CREATED| END -->
      </div>
    </div>`,
  components: {
  },
  props: {
    currentStepKey: {
      type: String,
      required: true,
      default: 'category'
    },
    attributes: {
      type: Object,
      required: false
    },
  },
  data: () => ({
    isOpenAddToCalendar: false,
    translations: bookit_window.translations
  }),
  computed: {
    allServices() {
      return this.$store.getters.getServices;
    },
    appointment: {
      get() {
        return this.$store.getters.getAppointment;
      },
      set( appointment ) {
        this.$store.commit('setAppointment', appointment);
      }
    },
    categories() {
      return this.$store.getters.getCategories;
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
  },
  created() {
    this.isOpenAddToCalendar = false;
  },
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
    closeAddToCalendarDropDown() {
      window.addEventListener('click', (e) => {
        if ( e.target.classList.contains('custom-select') || e.target.classList.contains('value')
            || this.hasParentClass(e.target, ['custom-select', 'custom-options'])) {
          return;
        }
        this.isOpenAddToCalendar = false;
      })
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
          this.moment.unix(this.appointment.start_time).format('YYYYMMDDTHHmmss') + "\n" +
          "DTEND:" +
          this.moment.unix(this.appointment.end_time).format('YYYYMMDDTHHmmss') +"\n" +
          "SUMMARY: Appointment - " + this.selectedService.title + "\n" +
          "END:VEVENT\n" +
          "END:VCALENDAR";
        window.open( "data:text/calendar;charset=utf8," + escape(ics));
    },
    printWindow() {
      var printBlock    = document.getElementById('confirmation').cloneNode(true);
      var info = printBlock.getElementsByClassName('appointment-info')[0];
      info.style.border = '1px dotted black';
      info.style.padding = '20px';

      var infoElements = printBlock.getElementsByClassName("info");
      for (var i = 0; i < infoElements.length; i++) {
        infoElements.item(i).style.paddingTop = '10px';
        infoElements[i].getElementsByClassName('title')[0].style.fontWeight = 'bolder';
      }

      var total = printBlock.getElementsByClassName('total')[0];
      total.style.textAlign = 'center';
      total.style.paddingTop = '20px';
      total.querySelector('span').style.fontWeight = 'bolder';

      let stylesHtml = '<style>p{text-align: center;}</style>';
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
      WinPrint.close();
    },
  },
  watch: {
    currentStepKey() {
      if ( !this.isMobile() && this.currentStepKey == 'result' ) {
        document.addEventListener('click', this.closeAddToCalendarDropDown);
      }
    },
  }
}