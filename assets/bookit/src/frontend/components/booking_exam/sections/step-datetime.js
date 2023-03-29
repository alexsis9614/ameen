export default {
  name: 'stepDateTime',
  template: `
  <div>
    <div class="datetime-content">
      <!-- MOBILE PART -->
      <div v-if="isMobile()" :class="['select-datetime-mobile', {'hidden': !showDate }]" >
        <div class='select-datetime'>
          <div class='title'>
            {{ translations.select_date }}
          </div>
          <div class="close" @click="showDate=false">
            <i class="close-icon"></i>
          </div>
        </div>
        <div class='calendar-select'>
          <div class="month-slide-control">
            <div class="prev" @click.prevent="slideMonth(false)">
              <i class="left-icon"></i>
            </div>
            <div class="slider-title">{{ activeDate.format('MMMM') }}</div>
            <div class="next" @click.prevent="slideMonth(true)">
              <i class="right-icon"></i>
            </div>
          </div>
          <div class="day-list">
            <div class="week-titles">
              <div class="title" v-for="(weekTitle, weekDayIndex) in weekdays()" :key="weekDayIndex">{{ weekTitle }}</div>
            </div>
            <div v-for="(week, weekIndex) in dayList" class="week">
              <div v-for="day in week" :key="day.date.dayOfYear()"
                   @click="selectDate( day.date )"
                   :class="['day', { 'day-off': day.dayOff }, {'active': isEqualDate(activeDate, day.date) }, {'today': isEqualDate(today, day.date)}, {'selected': (activeDate !=null && isEqualDate(day.date, activeDate)) }, {'inactive': (!day.currentMonth || today.isAfter(day.date))} ]">
                {{ day.date.date() }}
              </div>
            </div>
            <div class="info">
              <p><span class="available"></span>{{ translations.available_for_booking }}</p>
              <p><span class="unavailable"></span>{{ translations.unavailable_for_booking }}</p>
            </div>
          </div>
        </div>
        <div class="select-time">
          <button @click="showTime=true;showDate=false;">
            {{ translations.select_time }}<i class="right-icon"></i>
          </button>
        </div>
      </div>
      
      <div v-if="isMobile()"  :class="['select-datetime-mobile', {'hidden': !showTime }]" >
        <div class='select-datetime'>
          <div class="select-date" @click="showTime=false;showDate=true;">
            <i class="left-icon"></i>
          </div>
          <div class='title'>
            {{ translations.select_time }}
          </div>
          <div class="close" @click="showTime=false">
            <i class="close-icon"></i>
          </div>
        </div>
        <div class='time-select'>
          <div class="time-list">
            <div v-for="slot in allAvailableTimeSlots.start"  @click="selectTime( slot )" :class="['time-slot', {'active' : activeStartTime && activeStartTime.value == slot.value }, { 'not-available' : slot.value == null }]" >
              {{ slot.label }}
              <span v-if="activeStartTime && activeStartTime.value == slot.value" class="selected-icon"></span>
            </div>
          </div>
        </div>
        <div class="select-time">
          <button @click="showTime = false">
            {{ translations.continue }}<i class="right-icon"></i>
          </button>
        </div>
      </div>
      
      <div v-if="isMobile()" :class="['datetime', {'hidden': showDateTime}]">
        <div class="date mobile" @click="showDate = !showDate">
          <i class="calendar-icon"></i>
          <span class="selected-date">
                {{ translations.date }}: <span>{{ activeDate.format('DD MMMM YYYY') }}</span>
              </span>
          <span class="round-arrow">
                <i class="right-icon"></i>
              </span>
        </div>
        <div class="time mobile" @click="showTime = !showTime">
          <i class="time-icon"></i>
          <span class="selected-time">
                {{ translations.time }}: 
                <span v-if="activeStartTime">{{ activeStartTime.label }}</span>
                <span v-else>{{ translations.select_time }}</span>
              </span>
          <span class="round-arrow">
                <i class="right-icon"></i>
              </span>
        </div>
      </div>
      <!-- MOBILE PART|END -->
      
      <!-- DESKTOP PART -->
      <div v-else class="datetime">
        <div class="date">
          <label>{{ translations.date }}</label>
          <span :class="['select date', {'open': showDate}, {'error': errors.date_timestamp}]" @click="showDate = !showDate">
                <span v-if="activeDate">{{ activeDate.format('DD MMMM YYYY') }}</span>
                <span v-else>{{ translations.select_date }}</span>
          </span>
          <span class="error-tip" v-if="errors.date_timestamp">{{ errors.date_timestamp }}</span>
          <div :class="['calendar-select', {'hidden': !showDate}]">
            <div class="month-slide-control">
              <div class="prev" @click.prevent="slideMonth(false)">
                <i class="left-icon"></i>
              </div>
              <div class="slider-title">{{ activeDate.format('MMMM') }}</div>
              <div class="next" @click.prevent="slideMonth(true)">
                <i class="right-icon"></i>
              </div>
            </div>
            <div class="day-list">
              <div class="week-titles">
                <div class="title" v-for="(weekTitle, weekDayIndex) in weekdays()" :key="weekDayIndex">{{ weekTitle }}</div>
              </div>
              <div v-for="(week, weekIndex) in dayList" class="week">
                <div v-for="day in week" :key="day.date.dayOfYear()"
                     @click="selectDate( day.date )"
                     :class="['day', { 'day-off': day.dayOff }, {'active': ( isEqualDate(activeDate, day.date) && !today.isAfter(day.date) )}, {'today': isEqualDate(today, day.date)}, {'selected': (activeDate !=null && isEqualDate(day.date, activeDate)) }, {'inactive': (!day.currentMonth || today.isAfter(day.date))} ]">
                  {{ day.date.date() }}
                </div>
              </div>
              <div class="info">
                <p><span class="available"></span>{{ translations.available_for_booking }}</p>
                <p><span class="unavailable"></span>{{ translations.unavailable_for_booking }}</p>
              </div>
            </div>
          </div>
        </div>
    
        <div class="time">
          <label>{{ translations.time }}</label>
          <span :class="['select time', {'open': showTime}, {'error': errors.start_time}]" @click="showTime = !showTime; showDate=false; deleteError('start_time');">
                <span v-if="activeStartTime">{{ activeStartTime.label }}</span>
                <span v-else>{{ translations.select_time }}</span>
          </span>
          <span class="error-tip" v-if="errors.start_time">{{ errors.start_time }}</span>
          <div :class="['time-select', {'hidden': !showTime}]" v-if="allAvailableTimeSlots.start.length">
            <div :class="['time-list', {'empty': allAvailableTimeSlots.start[0].value == null}]">
              <div v-for="slot in allAvailableTimeSlots.start" @click="selectTime( slot )" :class="['time-slot', {'active' : activeStartTime && activeStartTime.value == slot.value }, { 'not-available' : slot.value == null }]">
                {{ slot.label }}
                <span v-if="activeStartTime && activeStartTime.value == slot.value" class="selected-icon"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- DESCTOP PART | END -->
    
      <div :class="['staff-content', {'hidden': showDateTime}]" ></div>
    </div>
  </div>
    `,

  components: {},
  data: () => ({
    activeDate: null,
    activeStartTime: null,
    dayList: [],
    showDate: false,
    showTime: false,
    staffFreeHours: {},
    translations: bookit_window.translations
  }),
  props: {
    attributes: {
      type: Object,
      required: false
    },
  },
  computed: {
    appointment: {
      get() {
        return this.$store.getters.getAppointment;
      },
      set( appointment ) {
        this.$store.commit('setAppointment', appointment);
      }
    },
    availableStaff () {
      let staff = [...this.staff];
      return staff.find( staff => {
        return staff.staff_services.find( staff_service => staff_service.id == this.selectedService.id )
      });
    },
    allAvailableTimeSlots() {
      let vm = this,
          allStaffTimeSlotList = [];

      vm.staffFreeHours      = {};

      if ( vm.selectedStaff && vm.selectedStaff.events !== undefined ) {
          let events = vm.selectedStaff.events.filter( event => {
             return vm.moment( event.date ).isSame( vm.activeDate );
          });

          const workingHours = vm.selectedStaff.working_hours.find(
              wh => parseInt(wh.weekday) === parseInt(vm.activeDate.isoWeekday())
          );

          const staffDisabledTimeSlots = vm.disabledTimeSlots.filter(
              dts => parseInt(dts.staff_id) === parseInt(vm.selectedStaff.id)
          );

          let timeSlotsForEmployee = vm.getSeparateTimeSlots(
              workingHours,
              vm.timeSlotList,
              staffDisabledTimeSlots,
              vm.selectedService.duration,
              vm.activeDate
          );

          vm.staffFreeHours[vm.selectedStaff.id] = timeSlotsForEmployee['start'].map(a => a.value);
          allStaffTimeSlotList = allStaffTimeSlotList.concat(timeSlotsForEmployee['start']);

          let timeSlot = [];

          events.forEach( event => {
              let timeSlotEvent = allStaffTimeSlotList.find((item) => {
                  return item.value === event.time.value;
              });
              if ( timeSlotEvent ) {
                  timeSlot.push( timeSlotEvent );
              }
          });

          allStaffTimeSlotList = timeSlot;
      }

        allStaffTimeSlotList = vm.getUniqueObjArrayKey(allStaffTimeSlotList, 'value');
        allStaffTimeSlotList.sort(function (a, b) {
            return a.value.split(':')[0] - b.value.split(':')[0];
        });
        if (allStaffTimeSlotList.length === 0 ) {
            allStaffTimeSlotList.push({ value: null, label: vm.translations.not_available });
            vm.activeStartTime = null;
        }

        /** remove time if not free **/
        if ( vm.activeStartTime ) {
            let isAvailable = allStaffTimeSlotList.filter( st => st.value === vm.activeStartTime.value );
            if ( isAvailable.length === 0 ) {
                vm.activeStartTime = null;
            }
        }

        // if ( vm.appointment.staff_id ) {
        //     if ( !vm.staffFreeHours.hasOwnProperty(vm.appointment.staff_id)
        //         || vm.staffFreeHours[vm.appointment.staff_id].length === 0 ) {
        //         let appointment      = Object.assign({}, vm.appointment);
        //         delete appointment.staff_id;
        //         vm.appointment = appointment;
        //     }
        // }

      return { 'start': allStaffTimeSlotList };
    },
    disabledTimeSlots: {
      get() {
        return this.$store.getters.getDisabledTimeSlots;
      },
      set( timeSlots ) {
        this.$store.commit('setDisabledTimeSlots', timeSlots);
      }
    },
    errors: {
      get() {
        return this.$store.getters.getErrors;
      },
      set( errors ) {
        this.$store.commit('setErrors', errors);
      }
    },
    language() {
      return this.$store.getters.getCurrentLanguage;
    },
    navigation: {
      get() {
        return this.$store.getters.getStepNavigation;
      },
      set( navigation ) {
        this.$store.commit('setStepNavigation', navigation);
      }
    },
    selectedService () {
      return this.$store.getters.getSelectedService;
    },
    selectedStaff: {
      get() {
        return this.$store.getters.getSelectedStaff;
      },
      set( staff ) {
        this.$store.commit('setSelectedStaff', staff);
      }
    },
    settings () {
      return this.$store.getters.getSettings;
    },
    showDateTime: {
      get() {
        return this.$store.getters.getShowDateTime;
      },
      set( showDateTime ) {
        this.$store.commit('setShowDateTime', showDateTime);
      }
    },
    staff() {
      return this.$store.getters.getStaff;
    },
    timeSlotList () {
      return this.$store.getters.getTimeSlotList;
    },
    today () {
      return this.moment().startOf('day');
    },
    wpTimeFormat() {
      return this.getWPSettingsTimeFormat();
    },
  },
  created() {
    /** set current day as selected on create **/
    this.moment.updateLocale( this.language, {
      week : {
        dow : 1
      }
    });
    
    if ( this.appointment.date_timestamp ) {
      this.activeDate = this.moment.unix(this.appointment.date_timestamp);
    }else{
      this.selectDate( this.moment().startOf('day') );
    }

    if ( this.appointment.start_time ) {
      this.activeStartTime = {
        value: this.moment.unix(this.appointment.start_time).format('HH:mm:ss'),
        label: `${this.moment.unix(this.appointment.start_time).format( this.wpTimeFormat )}`
      }
    }

    this.selectedStaff = this.availableStaff;

    this.dayList    = this.calendarDays();

    if ( !this.isMobile() ) {
      document.addEventListener('click', this.closeCustomSelect);
    }
  },
  destroyed() {
    if ( !this.isMobile() ) {
      document.removeEventListener('click', this.closeCustomSelect);
    }
  },
  methods: {
    calendarDays() {
      let vm = this;

      if ( !vm.selectedStaff && vm.selectedStaff.events === undefined ) {
        return;
      }

      vm.moment.updateLocale( vm.language, {
        week : {
          dow : 1
        }
      });
      let selectedDate = vm.today;
      if ( vm.activeDate ) {
        selectedDate = vm.activeDate;
      }
      let startDate = selectedDate.clone().startOf('month'),
          endDate   = selectedDate.clone().endOf('month'),
          firstWeek = startDate.clone().startOf('week'),
          lastWeek  = endDate.clone().endOf('week'),
          daysArray = [], tempItem;

      let workingDays = vm.getStaffWorkingDays();

      while ( firstWeek.isSameOrBefore(lastWeek) ) {
        let weekArray = [];
        for ( let i = 0; i < 7; i++ ) {
          let item = firstWeek.clone().startOf('week');
          item.set('date', item.date() + i);
          tempItem = {
            dayOff: !workingDays.includes(item.isoWeekday()) || !vm.selectedStaff.events.find(event => {
                return vm.moment( event.date ).isSame(item);
            }),
            date: item,
            currentMonth: ( vm.activeDate.month() == item.month() ),
          };
          weekArray.push( tempItem );
        }
        daysArray.push( weekArray );
        firstWeek.add(1, 'week');
      }

      return daysArray;
    },
    closeCustomSelect() {
      window.addEventListener('click', (e) => {
        if ( e.target.classList.contains('select') || e.target.classList.contains('select')
            || this.hasParentClass(e.target, ['select', 'calendar-select', 'time-select'])) {
          return;
        }
        this.showTime = false;
        this.showDate = false;
      })
    },
    deleteError( errorIndex ) {
      var errors = Object.assign({}, this.errors);
      delete errors[errorIndex];

      this.errors = errors;
    },
    // isStaffFreeTime( staffId ) {
    //   if ( this.activeStartTime && this.staffFreeHours.hasOwnProperty(staffId) ) {
    //       return this.staffFreeHours[staffId].includes(this.activeStartTime.value);
    //   }
    //   return false;
    // },
    getStaffWorkingDays() {
      let workingDays = [];

      workingDays = workingDays.concat(
          this.selectedStaff.working_hours.map( item => { if ( item.start_time !== null ) return item.weekday; } )
      );

      return [...new Set(workingDays)];
    },
    selectDate( selectedDate, isSlideMonth = false ) {
        if ( !isSlideMonth && ( this.today.isAfter(selectedDate) || (this.activeDate && this.activeDate.month() != selectedDate.month()) ) ) {
        return;
      }
      this.activeDate = selectedDate;
      this.dayList    = this.calendarDays();

      var appointment            = Object.assign({}, this.appointment);
      appointment.date_timestamp = this.activeDate.unix();
      this.appointment           = appointment;

      this.deleteError('date_timestamp');
      if ( this.isMobile() ){
        this.showDate = false;
      }
    },
    selectTime ( selectedTime ) {
      if ( selectedTime !== null && selectedTime.value !== null ) {
        this.activeStartTime = selectedTime;
        var startTime = this.activeDate.clone().set({
          hour: this.activeStartTime.value.split(':')[0],
          minute: this.activeStartTime.value.split(':')[1],
        })

        let appointment = Object.assign({}, this.appointment);
        appointment.start_time = startTime.unix();
        appointment.end_time   = startTime.clone().add(this.selectedService.duration, 'seconds').unix();
        this.appointment = appointment;

        if (this.isMobile()){
          this.showTime = false;
        }
      }
    },
    slideMonth( next ) {
      var currentDate = this.activeDate.clone();
      let newDate     = ( next ) ? currentDate.add(1, 'month'): currentDate.subtract(1, 'month');
      newDate.startOf('month');
      if ( this.moment().startOf('day').month() == newDate.month() ){
        newDate.set('date', this.moment().startOf('day').date());
      }
      this.selectDate(newDate, true);
    },
    weekdays() {
      let weekdays = this.moment.weekdaysShort();
      weekdays.push(weekdays.shift());
      return weekdays;
    },
  },
  watch: {
    'activeDate' ( value ) {
      let appointment            = Object.assign({}, this.appointment);
      appointment.date_timestamp = this.activeDate.unix();
      this.appointment           = appointment;

      this.$emit('setDisabledTimeSlots');
    },
    // staffFreeHours() {
    //     if ( this.selectedStaff && this.selectedStaff.hasOwnProperty('id') && !this.isStaffFreeTime(this.selectedStaff.id)) {
    //         this.selectedStaff = null;
    //     }
    // },
    showDate( value ) {
      if ( this.isMobile() && (value || this.showTime ) ){
        this.showDateTime = true;
      }else{
        this.showDateTime = false;
      }
    },
    showTime( value ) {
      if ( this.isMobile() && (value || this.showDate ) ){
        this.showDateTime = true;
      }else{
        this.showDateTime = false;
      }
    },
    selectedStaff () {
      let vm = this,
          appointment = Object.assign({}, vm.appointment),
          staffPrice  = 0;
          // paymentStep = this.navigation.find(step => step.key === 'payment');

      if ( vm.selectedStaff != null && vm.selectedStaff ){
        appointment.staff_id = vm.selectedStaff.id;
        staffPrice           = vm.selectedStaff.staff_services.find(staff_service => staff_service.id == vm.selectedService.id).price;s
      }else {
        delete appointment.staff_id;
      }

      appointment.payment_method = 'locally';
      // paymentStep.class          = 'skip';

      /** disable payment step if zero price **/
      // if ( parseFloat(staffPrice) == 0 ) {
      //   paymentStep.class          = 'skip';
      //   appointment.payment_method = 'locally';
      // } else {
      //   paymentStep.class          = '';
      //   delete appointment.payment_method;
      // }
      vm.appointment = appointment;
    },
  }
}