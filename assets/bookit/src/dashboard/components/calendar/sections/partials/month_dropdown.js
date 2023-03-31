
export default {
  template: `
    <div class="col-4 date-nav no-padding">
      <div class="control-nav">
        <button class="btn" @click="slideCalendar(false)"><i class="left-icon"></i></button>
        <div :class="['calendar-active-date', { active: showCalendarDropDown }]" @click="$emit('showCloseCalendarDropDown')" >{{ calendar.curAppointmentsDate.format('MMMM YYYY') }}</div>
        <button class="btn" @click="slideCalendar(true)"><i class="right-icon"></i></button>
        
        <div v-if="showCalendarDropDown" class='calendar-dropdown'>
          <div class="month dropdown">
            <div class="slider-control">
              <div class="prev" @click.prevent="slideYear(false)">
                <i class="left-icon"></i>
              </div>
              <div class="slider-title">{{ calendar.curAppointmentsDate.format('YYYY') }}</div>
              <div class="next" @click.prevent="slideYear(true)">
                <i class="right-icon"></i>
              </div>
            </div>
            <div class="month list">
              <div @click="selectDate(month)" v-for="month in months" :class="['month list-item', { active: month.month() == calendar.curAppointmentsDate.month() }]">{{ month.format('MMMM') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  name: "month_header",
  data: () => ({
    months: [],
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
    this.months = this.getMonths();
  },
  methods: {
    selectDate( newDate ) {
      this.$emit('selectDate', newDate);
      this.months = this.getMonths();
    },
    slideCalendar( next ) {
      var curMonth      = this.calendar.curAppointmentsDate.clone();
      let moveTo        = ( next ) ? curMonth.month() + 1 : curMonth.month() - 1;
      curMonth.set('month', moveTo);
      this.selectDate(curMonth);
    },
    slideYear( next ) {
      var currentDate = this.calendar.curAppointmentsDate.clone();
      let newDate     = ( next ) ? currentDate.add(1, 'year'): currentDate.subtract(1, 'year');
      this.selectDate(newDate);
    },
    getMonths( month, year ) {
      let currentDate  = this.calendar.curAppointmentsDate.clone().startOf('year');
      let months        = [];
      for ( let i = 0; i < 12; i++ ) {
        months.push( currentDate.clone().set('month', i) )
      }
      return months;
    },
  },
}