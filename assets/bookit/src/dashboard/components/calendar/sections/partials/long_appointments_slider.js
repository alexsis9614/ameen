import appointment_popup from '@dashboard-calendar/sections/appointment_popup';
import appointment_detail from '@dashboard-calendar/sections/appointment_detail';

import { Hooper, Slide } from 'hooper';
import 'hooper/dist/hooper.css';

export default {
  name: "appointments_slider",
  template: `
    <div class="list-slider">
      <div class="slider-control">
        <div class="prev" @click.prevent="slidePrev">
          <i class="left-icon"></i>
        </div>
        <div class="slider-title">{{ appointments.length }} Appointments</div>
        <div class="next" @click.prevent="slideNext">
          <i class="right-icon"></i>
        </div>
      </div>
      <hooper :mouseDrag="false" :touchDrag="false" :itemsToShow="slideItemToShow" :itemsToSlide="1" ref="carousel"  :wheelControl="false" :trimWhiteSpace="true"  >
        <slide v-for="appointment in appointments" :key="appointment.id"  >
          <div class="list">
            <div @mouseover="showDuration( appointment );" @mouseleave="hideDuration(appointment);" :class="['appointment-info', appointment.status, {active: appointment.popup}, 'appointment_' + appointment.id, { detailed: calendar.isDetailedView }]">
              <div class="appointment" @click="showPopup(appointment)">
                <img v-if="appointment.icon_url" :src="appointment.icon_url" class="service-icon" :alt="appointment.service">
                <div class="info">
                  <div class="time"> {{ appointment.start }} - {{ appointment.end }}</div>
                  <div class="service">{{ appointment.service }}</div>
                </div>
              </div>
              <appointment_detail :appointment="appointment"  v-if="calendar.isDetailedView"></appointment_detail>
            </div>
          </div>
        </slide>
      </hooper>
    <appointment_popup :marginTop="popupMarginTop"  :appointment="slideAppointment" v-if="isShowPopup[sliderDate] && !calendar.isDetailedView"></appointment_popup>
    <div  class="appointment-duration" ref="appointmentDuration"></div>
  </div>
  `,
  components: {
    appointment_popup,
    appointment_detail,
    Hooper,
    Slide,
  },
  data: () => ({
    showAppointmentDuration: false,
    carouselData: 0,
    slideAppointment: {},
    translations: bookit_window.translations,
    slideItemToShow: 1.5,
    hoverPendingBackgroundColor: '#FFE980',
    hoverBackgroundColor: '#b4d3d3',
    popupMarginTop: '',
    sliderDate: '',
  }),
  props: {
    appointments: {
      type: Array,
      required: true
    },
  },
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
    isShowPopup () {
      return this.$store.getters.getShowLongAppointmentPopup;
    },
  },
    created() {
    this.sliderDate = this.moment.unix(this.appointments[0].start_time).format('DD_MM_YYYY');
    this.$store.commit('setShowLongAppointmentPopup', {'day_key': this.sliderDate, 'status': false});
  },
  watch: {
    carouselData () {
      this.$refs.carousel.slideTo(this.carouselData);
    },
  },
  methods: {
    showPopup(appointment) {
      // not show popup for half visible appointments
      var appointmentElement  = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];
      if ( appointmentElement.offsetParent.offsetParent.getBoundingClientRect().right < appointmentElement.getBoundingClientRect().right
          || appointmentElement.offsetParent.offsetParent.getBoundingClientRect().left > appointmentElement.getBoundingClientRect().left ) {
        return;
      }
      appointment.popup = !appointment.popup;
      this.hideDuration(appointment);

      this.$store.commit('setShowLongAppointmentPopup', {'day_key': this.sliderDate, 'status': !this.isShowPopup[this.sliderDate]});
      this.slideAppointment = appointment;

      var appointmentElement  = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];

      this.popupMarginTop = parseInt(appointmentElement.getBoundingClientRect().bottom - appointmentElement.closest('.appointments').getBoundingClientRect().top) - 5;
    },
    showDuration( appointment ) {
      if (this.showAppointmentDuration || this.isInHourAppointment(appointment) || appointment.popup || this.isShowPopup[this.sliderDate]  ) {
        return;
      }
      var appointmentElement  = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];

      var height = this.getAppointmentHourDifferenceInHeight(appointment);
      var left   = (appointmentElement.getBoundingClientRect().left - this.$refs.appointmentDuration.getBoundingClientRect().left);
      var top    = (appointmentElement.getBoundingClientRect().top - this.$refs.appointmentDuration.getBoundingClientRect().top) + appointmentElement.getBoundingClientRect().height;
      var width  = appointmentElement.getBoundingClientRect().width;
      var background = this.hoverBackgroundColor;

      if ( appointmentElement.classList.contains('pending') ) {
        background = this.hoverPendingBackgroundColor;
        this.$refs.appointmentDuration.classList.add('pending');
      }

      if ( appointmentElement.offsetParent.offsetParent.getBoundingClientRect().right < appointmentElement.getBoundingClientRect().right ) {
        width = parseInt(getComputedStyle(appointmentElement).perspectiveOrigin.split(' ')[0], 10);
      }
      if ( appointmentElement.offsetParent.offsetParent.getBoundingClientRect().left > appointmentElement.getBoundingClientRect().left ) {
        left  = 0;
        width = parseInt(getComputedStyle(appointmentElement).perspectiveOrigin.split(' ')[0], 10) + 3; // 3px border width
        this.$refs.appointmentDuration.style.borderWidth = '0px';
      }

      this.$refs.appointmentDuration.style.background = background;
      this.$refs.appointmentDuration.style.width      = [width, 'px'].join('');
      this.$refs.appointmentDuration.style.top        = [top, 'px'].join('');
      this.$refs.appointmentDuration.style.left       = [left, 'px'].join('');
      this.$refs.appointmentDuration.style.height     = [height, 'px'].join('');

      this.showAppointmentDuration = true;
    },

    hideDuration( appointment ) {
      this.$refs.appointmentDuration.style.top          = 0;
      this.$refs.appointmentDuration.style.left         = 0;
      this.$refs.appointmentDuration.style.width        = 0;
      this.$refs.appointmentDuration.style.height       = 0;
      this.$refs.appointmentDuration.style.borderWidth  = '3px';
      this.$refs.appointmentDuration.classList.remove('pending');
      this.showAppointmentDuration = false;
    },
    slidePrev() {
      this.$refs.carousel.slidePrev();
    },
    slideNext() {
      this.$refs.carousel.slideNext();
    },
    updateCarousel(payload) {
      this.myCarouselData = payload.currentSlide;
    },
  }
}