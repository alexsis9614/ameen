import appointment_data from '@dashboard-calendar/sections/partials/appointment_data';
export default {
  template: `
    <div>
      <span class="popup-arrow"></span>
      <div class="appointment-popup" >
        <div v-if="loading" class="loader">
          <div class="loading"><div v-for="n in 9"></div></div>
        </div>
        <appointment_data :appointment="appointment" v-on:setLoader="setLoader" ></appointment_data>
      </div>
    </div>
  `,
  name: "appointment_popup",
  data: () => ({
    zIndex: 1,
    loading: false,
  }),
  components: {
    appointment_data,
  },
  props: {
    appointment: {
      required: true,
    },
    marginTop: {
      required: false,
    },
  },
  computed: {},
  created() {
    var popups = document.getElementsByClassName('appointment-popup');
    this.zIndex = ( popups.length + 1 ) +1;
  },
  mounted() {
    this.setPosition();
  },
  methods: {
    setLoader( value ) {
      this.loading = value;
    },
    setPosition() {
      var popupObj    = this.$el.getElementsByClassName('appointment-popup')[0];
      var popupArrow  = this.$el.getElementsByClassName('popup-arrow')[0];

      popupObj.style    = {};
      popupArrow.style  = {};

      popupObj.style.zIndex  = this.zIndex;

      /** set correct position for long appointments slider **/
      if (this.marginTop !== undefined){
        popupObj.style.marginTop  = [this.marginTop, 'px'].join('');
        popupArrow.style.top      = [( this.marginTop - 6 ), 'px'].join('');
      }
      popupArrow.style.marginTop    = [(parseInt(popupArrow.getBoundingClientRect().bottom - popupObj.getBoundingClientRect().top) * -1), 'px'].join('');

      /** set correct position for right popup, 20 px is padding  **/
      if ( window.innerWidth < ( popupObj.getBoundingClientRect().right + 30 ) ) {
        var rightPostion      = parseInt( popupObj.getBoundingClientRect().right - window.innerWidth - 20);
        popupObj.style.marginLeft  = [rightPostion, 'px'].join('');
      }

      /** set correct position for down popup **/
      if ( this.$parent.$el.getElementsByClassName('calendar-body').length > 0
          && this.$parent.$el.getElementsByClassName('calendar-body')[0].getBoundingClientRect().bottom < popupObj.getBoundingClientRect().bottom){

        var appointmentElement = this.$parent.$el.getElementsByClassName(['appointment', this.appointment.id].join('_'))[0];

        var topPosition = parseInt( popupObj.getBoundingClientRect().bottom - window.outerHeight )
            + parseInt(window.outerHeight - appointmentElement.getBoundingClientRect().bottom)
            + appointmentElement.getBoundingClientRect().height;

        popupObj.style.marginTop      = [(topPosition * -1), 'px'].join('');
        popupArrow.style.borderTop    = '8px solid white';
        popupArrow.style.borderBottom = 'none';
        popupArrow.style.marginTop    = [(parseInt(appointmentElement.getBoundingClientRect().height + 7) * -1), 'px'].join('');
      }

    },
  }
}