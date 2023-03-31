import appointment_popup from '@dashboard-calendar/sections/appointment_popup';
import appointment_detail from '@dashboard-calendar/sections/appointment_detail';

export default {
  name: "monthly_calendar",
  template: `
      <div class="monthly-calendar">
        <div class="calendar-header">
          <div class="week-days">
              <span v-for="(dayName, dayIndex) in weekdays()" :key="dayIndex">{{ dayName }}</span>
          </div>
        </div>
        <div class="calendar-body">
          <div  v-if="calendar.loading" class="loader">
            <div class="loading"><div v-for="n in 9"></div></div>
          </div>
          <div class="dates" >
              <div class="week" v-for="(week, weekIndex) in calendarDays()">
                <div :class="['date', [!day.currentMonth? 'inactive': 'available', (isEqualDate(day.date, moment().startOf('day')))? 'selected-day':'']]"
                  v-for="day in week" :key="day.date.dayOfYear()">
                  <span class="day">{{ day.date.date() }}</span>
                  <div class="appointments" :id="decodeString(day.date.format('X'))" >
                    <div class="list" v-for="appointment in appointments[moment(day.date).format('D_M')]" >
                      <div :class="['appointment-info', appointment.status, 'appointment_' + appointment.id, {active: appointment.popup},{ detailed: calendar.isDetailedView }]">
                        <div class="appointment" @click="appointment.popup = !appointment.popup;">
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
                </div>
              </div>
          </div>
        </div>
      </div>
  `,
  components: {
    appointment_popup,
    appointment_detail,
  },
  data: () => ({
    translations: bookit_window.translations
  }),
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
    appointments () {
      return this.$store.getters.getAppointments;
    },
  },
  methods: {
    weekdays() {
      let weekdays = this.moment.weekdays();
      weekdays.push(weekdays.shift());
      return weekdays;
    },
    calendarDays() {
      let startDate = this.calendar.curAppointmentsDate.clone().startOf('month');
      let endDate   = this.calendar.curAppointmentsDate.clone().endOf('month');
      var firstWeek = startDate.clone().startOf('week');
      var lastWeek  = endDate.clone().startOf('week');
      let daysArray = [], tempItem;

      while ( firstWeek.isSameOrBefore(lastWeek) ) {
        let weekArray = [];
        for ( let i = 0; i < 7; i++ ) {
          let item = firstWeek.clone().startOf('week');
          item.set('date', item.date() + i);
          tempItem = {
            date: item,
            currentMonth: ( this.calendar.curAppointmentsDate.month() === item.month() ),
          };
          weekArray.push( tempItem );
        }
        daysArray.push( weekArray );
        firstWeek.add(1, 'week');
      }

      return daysArray;
    },
  }
}