
export default {
  template: `
    <div class="col-4 date-nav no-padding">
    <div class="control-nav">
      <button class="btn" @click="slideCalendar(false)"><i class="left-icon"></i></button>
      <div :class="['calendar-active-date', { active: showCalendarDropDown }]" @click="$emit('showCloseCalendarDropDown')" >{{ calendar.curAppointmentsDate.format('MMMM DD') }}</div>
      <button class="btn" @click="slideCalendar(true)"><i class="right-icon"></i></button>

      <div v-if="showCalendarDropDown" class='calendar-dropdown day'>
        <div class="day dropdown">
          <div class="day slider-control">
            <div class="prev" @click.prevent="slideMonth(false)">
              <i class="left-icon"></i>
            </div>
            <div class="slider-title">{{ calendar.curAppointmentsDate.format('MMMM') }}</div>
            <div class="next" @click.prevent="slideMonth(true)">
              <i class="right-icon"></i>
            </div>
          </div>
          <div class="day list">
            <div class="week-titles">
              <div class="title" v-for="weekTitle in weekDays()">{{ weekTitle }}</div>
            </div>
            <div v-for="(week, weekIndex) in dayList" class="week">
              <div @click="selectDate(day.date)" v-for="day in week" :key="day.date.dayOfYear()" :class="['day list-item', [!day.currentMonth? 'inactive': 'available', (isEqualDate(day.date, calendar.curAppointmentsDate))? 'active':'']]">
                {{ day.date.date() }}
              </div>
            </div>
          </div>
          <div class="info">
            <!--              <p><span class="available"></span>Available for booking</p>-->
            <!--              <p><span class="unavailable"></span>Unavailable for booking</p>-->
          </div>
        </div>
      </div>
    </div>
    </div>
  `,
  name: "day_header",
  data: () => ({
    dayList: [],
  }),
  props: {
    showCalendarDropDown: {
      type: Boolean,
      default: false
    },
  },
  computed: {
    calendar () {
      return this.$store.getters.getCalendar;
    },
  },
  created() {
    this.dayList = this.calendarDays();
  },
  methods: {
    weekDays() {
      var weekDays = this.moment.weekdaysShort();
      weekDays.push(weekDays.shift());
      return weekDays;
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
    selectDate( newDate ) {
      this.$emit('selectDate', newDate);
      this.dayList = this.calendarDays();
    },
    slideCalendar( next ) {
      var currentDate = this.calendar.curAppointmentsDate.clone();
      let moveTo  = ( next ) ? currentDate.date() + 1 : currentDate.date() - 1;
      currentDate.date(moveTo);
      this.selectDate(currentDate);
    },
    slideMonth( next ) {
      var currentDate = this.calendar.curAppointmentsDate.clone();
      let newDate     = ( next ) ? currentDate.add(1, 'month'): currentDate.subtract(1, 'month');
      this.selectDate(newDate);
    },
  },
  watch: {
    'calendar.curAppointmentsDate' () {
      this.dayList = this.calendarDays();
    },
  },
}