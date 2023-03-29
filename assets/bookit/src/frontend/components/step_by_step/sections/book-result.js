export default {
  name: 'stepConfirmation',
  template: `
    <div class="confirmation result" id="confirmation" ref="confirmation">
      <p v-if="appointment.redirect_url.length > 0" class="redirect-url">
        {{ translations.you_will_be_redirected }} <span class="payment-method"> {{ appointment.payment_method }} </span> {{ translations.in }}&nbsp; {{ countDown }} {{ translations.seconds }}...
      </p>
      <p class="date" >
        {{ moment.unix(appointment.date_timestamp).format('DD MMMM YYYY') }}
      </p>
      <p class="time">{{ moment.unix(appointment.start_time).format( wpTimeFormat ) }} — {{ moment.unix(appointment.end_time).format( wpTimeFormat ) }}</p>
  
      <div class="appointment-info">
        <div class="appointment-detail">
          <div class="detail-icon">
            <span class="icon service"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.service }}:</span>
            <span class="value">{{ selectedService.title }}</span>
          </div>
        </div>
        <div class="appointment-detail">
          <div class="detail-icon">
            <span class="icon user"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.employee }}::</span>
            <span class="value">{{ selectedStaff.full_name }}</span>
          </div>
        </div>
        <div class="appointment-detail">
          <div class="detail-icon">
            <span class="icon user"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.client_name }}:</span>
            <span class="value">{{ appointment.full_name }}</span>
          </div>
        </div>
  
        <div class="appointment-detail" v-if="parseFloat(appointment.price) > 0">
          <div class="detail-icon">
            <span class="icon price"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.price }}:</span>
            <span class="value">{{ staffPrice }}</span>
          </div>
        </div>
        <div class="appointment-detail" v-if="parseFloat(appointment.price) > 0">
          <div class="detail-icon">
            <span class="icon payment"><i></i></span>
          </div>
          <div class="info">
            <span class="title">{{ translations.payment_method }}:</span>
            <span class="value">{{ appointment.payment_method }}</span>
          </div>
        </div>
      </div>
      <div class="total no-margin">
        <label>{{ translations.total }}:</label>
        <span>{{ staffPrice }}</span>
      </div>
<!--      <div class="download-pdf" v-if="isMobile()" @click="printWindow()">-->
<!--        <i class="download-icon"></i>{{ translations.print}}-->
<!--      </div>-->
    </div>
  `,
  components: {
  },
  data: () => ({
    countDown: 5,
    selectedPayment: 0,
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
    selectedStaff() {
      return this.$store.getters.getSelectedStaff;
    },
    selectedService () {
      return this.$store.getters.getSelectedService;
    },
    settings () {
      return this.$store.getters.getSettings;
    },
    staffPrice() {
      return this.getStaffPrice(this.selectedStaff, this.selectedService, this.settings);
    },
    wpTimeFormat() {
      return this.getWPSettingsTimeFormat();
    },
  },
  created() {
    if ( this.appointment.hasOwnProperty('redirect_url') && ( this.appointment.redirect_url != undefined && this.appointment.redirect_url.length > 0 ) ) {
      this.countDownTimer();
    }
  },
  methods: {
    countDownTimer() {
      if ( this.countDown > 0) {
        setTimeout(() => {
          this.countDown -= 1;
          this.countDownTimer();
        }, 1000);
      } else {
        this.countDown    = 5;
        window.location.href = this.appointment.redirect_url;

        var appointment          = Object.assign({}, this.appointment);
        appointment.redirect_url = '';
        this.appointment  = appointment;
      }
    },
    printWindow() {
      var printBlock    = document.getElementById('confirmation').cloneNode(true);
      printBlock.removeChild(printBlock.getElementsByClassName("download-pdf")[0]);
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
    generatePdf(){
      window.html2canvas = html2canvas;
      var doc = new jsPDF('p', 'pt', 'A4');
      doc.html(this.$refs.confirmation,{
        callback: function (pdf) {
          pdf.save('confirmation.pdf');
        }}
      );
    }
  },
}