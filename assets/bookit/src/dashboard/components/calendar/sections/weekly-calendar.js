import appointment_popup from '@dashboard-calendar/sections/appointment_popup';
import appointment_detail from '@dashboard-calendar/sections/appointment_detail';
import appointments_slider from '@dashboard-calendar/sections/partials/long_appointments_slider';

export default {
  name: "weekly_calendar",
  template: `
    <div class="weekly-calendar">
      <div class="calendar-header">
        <div class="week-days">
          <span>TIME</span>
          <span v-for="(day, dayIndex) in calendarDays()" :key="dayIndex" :class="[(isEqualDate(day.date, moment().startOf('day')))? 'active':'']">
          {{ day.date.format('dddd DD') }}
          </span>
        </div>
      </div>
    
      <div class="calendar-body">
        <div  v-if="calendar.loading" class="loader">
          <div class="loading"><div v-for="n in 9"></div></div>
        </div>
        <div class="dates" v-for="(day_hour, day_hour_index) in hours()">
          <div class="week" >
            <div class="time-row">
              <span :class="['hour', day_hour.date.format('H'), { 'current-hour': isCurrentHour(day_hour.hour)}]">
                  <span>
                    {{ day_hour.hourTitle }}
                  </span>
                </span>
            </div>
            
            <div :class="['date', [!day.currentMonth? 'inactive': 'available', (isEqualDate(day.date, moment().startOf('day')))? 'selected-day':'']]"
                 v-for="day in calendarDays()" :key="day.date.dayOfYear()">
              
              <div :class="['appointments', day_hour.hour, { exist: isNeedBorder(day_hour.hour)}, { 'current-hour': isCurrentHour(day_hour.hour)}]" :id="decodeString(day.date.clone().set({hour:day_hour.hour}).format('X'))">
                <appointments_slider v-if="isLongAppointments(parsedAppointments, day.date.clone().hour(day_hour.hour))"  v-on:isInHourAppointment="isInHourAppointment" :appointments="parsedAppointments[day.date.clone().hour(day_hour.hour).format('D_H')]"></appointments_slider>

                <div :style="{'height': getHeight(appointment)}" v-else v-for="appointment in parsedAppointments[day.date.clone().hour(day_hour.hour).format('D_H')]" :class="getAppointmentsClass(parsedAppointments, day.date.clone().hour(day_hour.hour))">
                  <div @mouseleave="hideDuration(appointment);" @mouseover="showDuration(appointment);" 
                       :class="['appointment-info', appointment.status, 'appointment_' + appointment.id, {active: appointment.popup}, { detailed: calendar.isDetailedView }]">
                    <div class="appointment" @click="showPopup(appointment)">
                      <img v-if="appointment.icon_url" :src="appointment.icon_url" class="service-icon" :alt="appointment.service">
                      <div class="info">
                        <div class="time"> {{ appointment.start }} - {{ appointment.end }}</div>
                        <div class="service">{{ appointment.service }}</div>
                      </div>
                    </div>
                    <appointment_detail :appointment="appointment"  v-if="calendar.isDetailedView && day.currentMonth"></appointment_detail>
                  </div>
                  <appointment_popup :appointment="appointment"  v-if="appointment.popup && !calendar.isDetailedView && day.currentMonth"></appointment_popup>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  components: {
    appointment_popup,
    appointment_detail,
    appointments_slider,
  },
  data: () => ({
    translations: bookit_window.translations,
    parsedAppointments: {},
    appointmentHeight: 60,
    appointmentOneLineHeight: 17,
  }),
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
    appointments () {
      return this.$store.getters.getAppointments;
    },
  },
  watch: {
    appointments () {
      this.parsedAppointments = this.getWeekDayAppointments();
    },
  },
  methods: {
    showPopup(appointment) {
      if ( this.calendar.isDetailedView ){
        return;
      }
      this.hideDuration(appointment);
      appointment.popup = !appointment.popup;
    },
    /** get height for long appointments if service name is long **/
    getHeight(appointment) {
      if ( this.calendar.isDetailedView ){
        return ;
      }

      var height = this.appointmentHeight;
      if ( appointment.service.length > 25 ) {
        height = parseInt(appointment.service.length/25) * this.appointmentOneLineHeight;
        height = height + this.appointmentHeight;
      }

      return [(height), 'px'].join('');
    },
    getAppointmentsClass(appointments, date) {
      return [ 'list',
        { 'long-appointment': this.isLongAppointment(appointments, date.format('D_H')) && !this.calendar.isDetailedView }
      ];
    },
    hideDuration( appointment ) {
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.height = 'auto';
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.zIndex = 'auto';
    },
    showDuration( appointment ) {
      if (this.calendar.isDetailedView || this.isInHourAppointment(appointment)
          || this.$el.getElementsByClassName('appointment-popup').length > 0){
        return;
      }

      var height = this.getLongAppointmentHeight(appointment);
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.height = height;
      this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0].style.zIndex = 2;
    },
    getLongAppointmentHeight( appointment ) {
      var appointmentElement = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];
      var heightDifference = this.getAppointmentHourDifferenceInHeight(appointment);

      return [(appointmentElement.getBoundingClientRect().height + heightDifference), 'px'].join('');
    },
    isLongAppointments( appointments, date ) {
      return appointments.hasOwnProperty(date.format('D_H'))
          && !appointments[date.format('D_H')].is_all_in_hour
          && appointments[date.format('D_H')].length > 1
          && !this.calendar.isDetailedView;
    },
    isLongAppointment( appointments, key ) {
      return appointments.hasOwnProperty(key) && !appointments[key].is_all_in_hour && appointments[key].length == 1
          && !this.calendar.isDetailedView;
    },
    /**
     * Check just hour
     * @param row_hour int
     * @returns {boolean}
     */
    isCurrentHour(row_hour) {
      let now = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      if ( now.hour() == row_hour ){
        return true;
      }
      return false;
    },
    hours() {
      let hours = [];
      for(let hour = 1; hour < 24; hour++) {
        hours.push({
          'hourTitle': this.moment({ hour }).format('h:mm a'),
          'hour': hour,
          'date': this.moment({ hour })
        });
      }
      return hours;
    },
    calendarDays() {
      let startDate  = this.calendar.curAppointmentsDate.clone().startOf('week');
      let weekArray = [], item, tempItem;
      for ( let i = 0; i < 7; i++ ) {
        item = startDate.clone();
        item.set('date', startDate.date() + i);
        tempItem = {
          date: item,
          currentMonth: ( this.calendar.curAppointmentsDate.month() === item.month() ),
        };
        weekArray.push( tempItem );
      }
      return weekArray;
    },
    checkIsDateHourAppointment(appointment, date) {
      return this.moment.unix(appointment.start_time).isBetween(date, date.add(1, 'hours'), null, '[)');
    },
    isNeedBorder( dayHour ) {
      var exist = Object
          .keys(this.parsedAppointments)
          .filter(function (key) {
            return key.split('_')[1] == dayHour;
          });
      return exist.length > 0 ? true: false;
    },
    getWeekDayAppointments() {
      let result = {};
      for (const [key, appointmentData] of Object.entries(this.appointments)) {
        for (var i = 0; i < appointmentData.length; i++) {
          var appointmentKey = this.moment.unix(appointmentData[i].start_time).format('D_H');
          if (result.hasOwnProperty(appointmentKey) === false) {
            result[appointmentKey] = [];
            result[appointmentKey]['is_all_in_hour'] = this.isInHourAppointment(appointmentData[i]);
          }
          // check next element if old value was in hour length
          if ( result[appointmentKey]['is_all_in_hour'] ){
            result[appointmentKey]['is_all_in_hour'] = this.isInHourAppointment(appointmentData[i]);
          }
          result[appointmentKey].push(appointmentData[i]);
        }
      }
      return result;
    },
  }
}