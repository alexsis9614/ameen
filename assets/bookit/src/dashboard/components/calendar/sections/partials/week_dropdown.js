
export default {
  template: `
    <div class="col-4 date-nav no-padding">
    <div class="control-nav">
      <button class="btn" @click="slideCalendar(false)"><i class="left-icon"></i></button>
      <div :class="['calendar-active-date', { active: showCalendarDropDown }]" @click="$emit('showCloseCalendarDropDown')" >{{ getDropDownTitle() }}</div>
      <button class="btn" @click="slideCalendar(true)"><i class="right-icon"></i></button>

      <div v-if="showCalendarDropDown" class='calendar-dropdown'>
        <div class="week dropdown">
          <div class="slider-control">
            <div class="prev" @click.prevent="slideMonth(false)">
              <i class="left-icon"></i>
            </div>
            <div class="slider-title">{{ getDropDownTitle() }}</div>
            <div class="next" @click.prevent="slideMonth(true)">
              <i class="right-icon"></i>
            </div>
          </div>
          <div class="week list">
            <div @click="selectDate(week.date)" v-for="week in currentWeeks" :class="['week list-item', { active: isActive(week.date) }]">
              {{ week.weekTitle.monthTitle }}  <b>{{ week.weekTitle.dates }}</b>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  `,
  name: "week_header",
  data: () => ({
    currentWeeks: [],
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
    this.currentWeeks = this.weeks();
  },
  methods: {
    getDropDownTitle() {
      let currentWeekStart = this.calendar.curAppointmentsDate.clone().startOf('week');
      let currentWeekEnd   = this.calendar.curAppointmentsDate.clone().endOf('week');

      var title = this.calendar.curAppointmentsDate.format('MMMM');
      if ( currentWeekStart.year() != currentWeekEnd.year() ) {
        title = [currentWeekStart.format('MMM YYYY'), currentWeekEnd.format('MMM YYYY')].join(' — ');
      }
      if ( currentWeekStart.month() != currentWeekEnd.month() && currentWeekStart.year() == currentWeekEnd.year()) {
        title = [currentWeekStart.format('MMM DD'), currentWeekEnd.format('MMM DD')].join(' — ');
      }

      return title;
    },
    isActive(weekDate) {
      let currentWeekStart = this.calendar.curAppointmentsDate.clone().startOf('week');
      return weekDate.month() == currentWeekStart.month()
          && weekDate.year() == currentWeekStart.year()
          && weekDate.week() == currentWeekStart.week();
    },
    slideMonth( next ) {
      var currentDate = this.calendar.curAppointmentsDate.clone();
      let newDate     = ( next ) ? currentDate.add(1, 'month'): currentDate.subtract(1, 'month');
      this.selectDate(newDate.startOf('week'));
    },
    selectDate( newDate ) {
      this.$emit('selectDate', newDate);
      this.currentWeeks = this.weeks();
    },
    weeks() {
      let currentDate  = this.calendar.curAppointmentsDate.clone().startOf('week');
      let weeks        = [];
      let start    = currentDate.week() - 2;

      for ( let i = 0; i < 5; i++ ) {
        var week = currentDate.clone().set('week', start + i);
        var weekTitle = this.weekTitleByStartWeekDate(week);
        weeks.push( {'weekTitle': weekTitle, 'date': currentDate.clone().set('week', start + i)} );
      }

      return weeks;
    },
    slideCalendar( next ) {
      var curWeek = this.calendar.curAppointmentsDate.clone().startOf('week');
      let moveTo  = ( next ) ? curWeek.week() + 1 : curWeek.week() - 1;
      curWeek.set('week', moveTo);
      this.selectDate(curWeek);
    },
    weekTitleByStartWeekDate( startWeekDate ) {
      var endDate = startWeekDate.clone().endOf('week');

      var monthTitle = startWeekDate.format('MMMM');
      if (startWeekDate.month() != endDate.month()){
        monthTitle = startWeekDate.format('MMMM') + '/' +endDate.format('MMMM');
      }

      return {'monthTitle': monthTitle, 'dates': [startWeekDate.date(), endDate.date()].join(' - ')};
    }
  },
  watch: {
    'calendar.curAppointmentsDate' () {
      this.currentWeeks = this.weeks();
    },
  },
}