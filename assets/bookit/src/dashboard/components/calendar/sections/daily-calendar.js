import appointment_popup from '@dashboard-calendar/sections/appointment_popup';
import appointment_detail from '@dashboard-calendar/sections/appointment_detail';
import appointments_slider from '@dashboard-calendar/sections/partials/long_appointments_slider';

import { Hooper, Slide } from 'hooper';
import 'hooper/dist/hooper.css';

export default {
  name: "daily_calendar",
  template: `
    <div class="daily-calendar">
      <div class="calendar-time-side">
        <div class="time-header"><span class="title">time</span></div>
        <div class="times">
            <span :class="['hour', { 'current-hour': isCurrentDayHour(day_hour.date)}]" v-for="day_hour in hours()" :key="day_hour.date.format('H')" 
                  :style="getStyle(day_hour, isCurrentDayHour(day_hour.date))">
                <span :class="[day_hour.date.format('H')]">
                  {{ day_hour.hourTitle }}
                </span> 
            </span>
        </div>
      </div>
      <div class="calendar-data">
        <div  v-if="calendar.loading" class="loader">
          <div class="loading"><div v-for="n in 9"></div></div>
        </div>

          <div class="daily-slider">
            <div v-if="showPrevious" class="staff-slider-control prev" @click.prevent="slidePrev">
              <i class="left-icon"></i>
            </div>
            
            <hooper :mouseDrag="false" :touchDrag="false" @before_slide="setControls" :itemsToShow="slideItemToShow" :itemsToSlide="1" ref="carousel" :wheelControl="false" :trimWhiteSpace="true" >
              <slide v-for="employee in staff" :key="employee.id">
                <div class="employee">
                  <span>{{ employee.full_name }}</span>
                </div>
                
                <div :id="decodeString([day_hour.date.format('X'), employee.id].join('_'))" :style="getStyle(day_hour, isCurrentDayHour(day_hour.date))" :class="getAppointmentsClass(day_hour, employee)" v-for="day_hour in hours()">
                  <div class="current-hour-border" v-if="isCurrentDayHour(day_hour.date)"></div>
                  <div :class="['list', {'current-hour' :isCurrentDayHour(day_hour.date)}]" v-for="appointment in parsedAppointments[day_hour.date.format('H')+'_'+employee.id]">
                    <div @mouseleave="hideDuration(appointment);" @mouseover="showDuration(appointment);" 
                         :class="['appointment-info', appointment.status, { 'long-appointment': !isInHourAppointment(appointment) }, {active: appointment.popup}, 'appointment_' + appointment.id, { detailed: calendar.isDetailedView }]">
                      <div class="appointment" @click="hideDuration(appointment);appointment.popup = !appointment.popup;">
                        <img v-if="appointment.icon_url" :src="appointment.icon_url" class="service-icon" :alt="appointment.service">
                        <div class="info">
                          <div class="time"> {{ appointment.start }} - {{ appointment.end }}</div>
                          <div class="service">{{ appointment.service }}</div>
                        </div>
                      </div>
                      <appointment_detail :appointment="appointment"  v-if="calendar.isDetailedView"></appointment_detail>
                    </div>
                    <appointment_popup :appointment="appointment"  v-if="appointment.popup && !calendar.isDetailedView"></appointment_popup>
                  </div>
                </div>
              </slide>
<!--              pseudo end element for correct slide view-->
              <slide><div class="employee"></div></slide>
            </hooper>
            <div v-if="showNext" class="staff-slider-control next" @click.prevent="slideNext">
              <i class="right-icon"></i>
            </div>
          </div>
      </div>
    </div>
  `,
  components: {
    appointment_popup,
    appointment_detail,
    appointments_slider,
    Hooper,
    Slide,
  },
  data: () => ({
    carouselData: 0,
    showPrevious: false,
    showNext: true,
    translations: bookit_window.translations,
    timeRowStyle: {},
    emptyAppointmentHeight: 40,
    appointmentHeight: 70,
    appointmentDetailedHeight: 325,
    borderColor: '#dddddd',
    slideItemToShow: 7.2,
    parsedAppointments: {},
  }),
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
    appointments () {
      return this.$store.getters.getAppointments[this.calendar.curAppointmentsDate.format('D_M')];
    },
    staff() {
      return this.$store.getters.getStaff;
    },
  },
  created() {
    if ( this.staff.length <= 7 ){
      this.slideItemToShow = this.staff.length;
      this.showNext = false;
    }

    this.setTimeRowDefaultStyle();
  },
  watch: {
    carouselData () {
      this.$refs.carousel.slideTo(this.carouselData);
    },
    appointments () {
      this.setTimeRowDefaultStyle();
      this.parsedAppointments = this.getDayAppointments();
    },
    parsedAppointments () {
      this.setAppointmentRowNewStyle();
    },
    'calendar.isDetailedView' () {
      this.setTimeRowDefaultStyle();
      this.setAppointmentRowNewStyle();
    },

  },
  methods: {
    hideDuration( appointment ) {
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.height = 'auto';
    },
    showDuration( appointment ) {
      if (this.calendar.isDetailedView || this.isInHourAppointment(appointment)
          || this.$el.getElementsByClassName('appointment-popup').length > 0){
        return;
      }
      var height = this.getLongAppointmentHeight(appointment);
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.height = height;
    },
    getLongAppointmentHeight( appointment ) {
      if (this.calendar.isDetailedView){
        return 'auto';
      }

      var end         = this.moment.unix(appointment.end_time);
      var hourElement = this.$el.getElementsByClassName(end.format('H'))[0];
      var hourOffset  = this.getOffset(hourElement);

      var appointmentElement = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];
      var appointmentOffset  = this.getOffset(appointmentElement);

      if (!hourElement || !appointmentElement) {
        return;
      }

      var hoursHeight   = parseInt(hourOffset.top - appointmentOffset.top);
      var minutesHeight = 0;
      if (end.format('m') > 0 ){
        var perHourHeight = hoursHeight/end.format('H');
        minutesHeight     = (end.format('mm')*perHourHeight)/60;
      }
      return [(hoursHeight + minutesHeight), 'px'].join('');
    },
    getAppointmentsClass(day_hour, employee) {
      return [ 'appointments',
        day_hour.date.format('H'),
        this.isNotWorkingHour(day_hour.date.format('H'), employee.working_hours) ? 'inactive' : 'available',
      ];
    },
    getStyle(day_hour, isCurrentHour) {
      var height = this.timeRowStyle[day_hour.date.format('H')].height.replace(/\D/g,'');
      if (isCurrentHour){
        height = parseInt(height) + 10;
      }

      return {
        'height': [height, 'px'].join(''),
        'border-top': this.timeRowStyle[day_hour.date.format('H')].border_top
      };
    },
    isNotWorkingHour( hour, working_hours ) {
      var workingHours = working_hours.find( wh => wh.weekday === this.calendar.curAppointmentsDate.isoWeekday() );
      if (!workingHours){
        return true;
      }
      if (workingHours && this.moment(hour,'H').isBetween(this.moment(workingHours.start_time,'HH:mm:ss'), this.moment(workingHours.end_time,'HH:mm:ss'), null, '[]') == false){
        return true;
      }
      return false;
    },

    /** append default styles for hour rows **/
    setTimeRowDefaultStyle() {
      let defaultStyle = {};
      for(let hour = 1; hour < 24; hour++) {
        defaultStyle[hour] = {};
        defaultStyle[hour]['height'] = [this.emptyAppointmentHeight, 'px'].join('');
        defaultStyle[hour]['border_top'] = 'none';
      }
      this.timeRowStyle = Object.assign({}, defaultStyle);
    },

    /** date hours **/
    hours() {
      let hours = [];
      for(let hour = 1; hour < 24; hour++) {
        var date = this.calendar.curAppointmentsDate.clone().hour(hour);
        hours.push({
          'hourTitle': date.format('h:mm a'),
          'date': date
        });
      }
      return hours;
    },
    getDayAppointments() {
      let dayAppointments = this.appointments;
      let result = {};

      if (!dayAppointments){
        return result;
      }

      for (var i = 0; i < dayAppointments.length; i++) {
        var appointmentKey = [this.moment.unix(dayAppointments[i].start_time).format('H'), dayAppointments[i].staff_id].join('_');
        if (result.hasOwnProperty(appointmentKey) === false) {
          result[appointmentKey] = [];
        }
        result[appointmentKey].push(dayAppointments[i]);
      }

      return result;
    },

    /** row style data based on appointments **/
    setAppointmentRowNewStyle() {
      let newStyles = this.timeRowStyle;

      for (const [key, appointmentData] of Object.entries(this.parsedAppointments)) {
        var hour_employee = key.split('_');

        if ( newStyles.hasOwnProperty(hour_employee[0]) ) {
          var exist_max_height_for_hour = newStyles[hour_employee[0]].height.replace(/\D/g,'');
        }

        if (this.$el.getElementsByClassName('appointment-info').length > 0 && this.$el.getElementsByClassName('appointment-info')[0].clientHeight > 70 ){
          this.appointmentHeight, this.appointmentDetailedHeight =
              this.$el.getElementsByClassName('appointment-info')[0].clientHeight + ( 5 * parseInt(appointmentData.length));
        }
        var newHeight = (parseInt(this.appointmentHeight) * parseInt(appointmentData.length));

        if (this.calendar.isDetailedView == true){
          newHeight = (parseInt(this.appointmentDetailedHeight) * parseInt(appointmentData.length));
        }

        // set max height value
        if (exist_max_height_for_hour > newHeight ) {
          newHeight = newStyles[hour_employee[0]].height.replace(/\D/g,'');
        }

        newStyles[hour_employee[0]] = {
          'height': [newHeight, 'px'].join(''),
          'border_top': ['1px solid ', this.borderColor].join(''),
        };
      }
      this.timeRowStyle = Object.assign({}, newStyles);
    },
    setControls() {
      this.showPrevious = false;
      this.showNext = true;

      if (this.$refs.carousel.currentSlide > 0) {
        this.showPrevious = true;
      }

      if ( ( (this.$refs.carousel.$children.length - 1) == (parseInt(this.$refs.carousel.currentSlide) + parseInt(this.slideItemToShow))
          && this.$refs.carousel.currentSlide != 0 )
          || this.$refs.carousel.$children.length <= this.slideItemToShow ) {
        this.showNext = false;
      }
    },
    slidePrev() {
      this.$refs.carousel.slidePrev();
      this.setControls();
    },
    slideNext() {
      this.$refs.carousel.slideNext();
      this.setControls();
    },
    updateCarousel(payload) {
      this.myCarouselData = payload.currentSlide;
    },
  }
}