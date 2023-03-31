import monthly_calendar from '@dashboard-calendar/sections/monthly-calendar';
import weekly_calendar from '@dashboard-calendar/sections/weekly-calendar';
import daily_calendar from '@dashboard-calendar/sections/daily-calendar';

import month_dropdown from '@dashboard-calendar/sections/partials/month_dropdown';
import week_dropdown from '@dashboard-calendar/sections/partials/week_dropdown';
import day_dropdown from '@dashboard-calendar/sections/partials/day_dropdown';

import appointment_form from '@dashboard-sections/appointment/form';
import delete_form from '@dashboard-partials/delete-appointment-form';

export default {
  name: 'calendar',
  template: `
    <div class="calendar admin">
      <div class="calendar-container noselect">

        <!-- HEADER PART -->
        <div class="calendar-header-navbar bookit-row">
          <div class="col-4 no-padding">
            <div class="calendar-type-nav no-padding">
              <button type="button" :class="['btn', {'active':calendar.type=='day'}]" @click="setCalendarType('day');">{{ translations.day }}</button>
              <button type="button" :class="['btn', {'active':calendar.type=='week'}]" @click="setCalendarType('week')">{{ translations.week }}</button>
              <button type="button" :class="['btn', {'active':calendar.type=='month'}]" @click="setCalendarType('month')">{{ translations.month }}</button>
            </div>
          </div>
          
          <month_dropdown v-if="calendar.type=='month'" :showCalendarDropDown="showCalendarDropDown" v-on:selectDate="selectDate" v-on:showCloseCalendarDropDown="showCloseCalendarDropDown"></month_dropdown>
          <week_dropdown v-if="calendar.type=='week'" :showCalendarDropDown="showCalendarDropDown" v-on:selectDate="selectDate" v-on:showCloseCalendarDropDown="showCloseCalendarDropDown"></week_dropdown>
          <day_dropdown v-if="calendar.type=='day'" :showCalendarDropDown="showCalendarDropDown" v-on:selectDate="selectDate" v-on:showCloseCalendarDropDown="showCloseCalendarDropDown"></day_dropdown>
          
          <div class="col-4 no-padding detail-nav">
            <label class="switcher">
              <span>{{ translations.detailed_view }}</span>
              <div class="bookit-switch">
                <input type="checkbox" @change="setIsDetailedView($event)" v-model="calendar.isDetailedView">
                <label></label>
              </div>
            </label>
            <button type="button" class="btn today" @click="setToday();">{{ translations.today }}</button>
          </div>
        </div>
        <!-- HEADER PART END -->

        <monthly_calendar v-if="calendar.type=='month'"></monthly_calendar>
        <weekly_calendar v-if="calendar.type=='week'"></weekly_calendar>
        <daily_calendar v-if="calendar.type=='day'"></daily_calendar>

        <appointment_form :type="this.actionType" v-if="showEditAddForm"></appointment_form>
        <delete_form type="appointment" v-if="showDeleteForm"></delete_form>
      </div>
    </div>`,
  components: {
    monthly_calendar,
    weekly_calendar,
    daily_calendar,
    month_dropdown,
    week_dropdown,
    day_dropdown,
    appointment_form,
    'delete_form': delete_form,
  },
  data: () => ({
    showCalendarDropDown: false,
    newAppointment: {},
    translations: bookit_window.translations,
  }),
  computed: {
    actionType () {
      return this.$store.getters.getActionType
    },
    showEditAddForm () {
      return this.$store.getters.getShowEditAddForm
    },
    showDeleteForm () {
      return this.$store.getters.getShowDeleteForm
    },
    calendar () {
      return this.$store.getters.getCalendar;
    },
    calendarAppointmentsDate: {
      get() {
        return this.$store.getters.getCalendarAppointmentsDate;
      },
      set( value ) {
        this.$store.commit('setCalendarAppointmentsDate', value);
      }
    },
    appointments () {
      return this.$store.getters.getAppointments;
    },
  },
  destroyed() {
    document.removeEventListener('click', this.closeDropdownAndAppointmentPopupsEvent);
    document.removeEventListener('click', this.createAppointment);
  },
  created() {
    document.addEventListener('click', this.closeDropdownAndAppointmentPopupsEvent);
    document.addEventListener('click', this.createAppointment);
  },
  methods: {
    selectDate( newDate ) {
      if ( newDate !== this.calendar.curAppointmentsDate ) {
        this.calendarAppointmentsDate = newDate;
        this.$store.dispatch('setCalendarMonthYear', newDate);
      }
    },
    setToday() {
      this.calendar.curAppointmentsDate = this.moment().startOf('day');
      this.setCookie('bookit_current_appointment_date', this.moment().startOf('day'));
    },
    setCalendarType( type ) {
      this.calendar.type = type;
      this.setCookie('bookit_calendar_type', type);
    },
    setIsDetailedView( event ) {
      this.calendar.isDetailedView = event.target.checked;
      this.setCookie('bookit_is_detailed', event.target.checked);
    },
    showCloseCalendarDropDown() {
      if (this.showCalendarDropDown === false){
        this.showCalendarDropDown = true;
      }else{
        this.showCalendarDropDown = false;
      }
    },
    hasParentClass(child, classList){
      for (var i = 0; i < classList.length; i ++ ) {
        if(child.className.split(' ').indexOf(classList[i]) >= 0) return true;
      }
      //Throws TypeError if no parent
      try{
        return child.parentNode && this.hasParentClass(child.parentNode, classList);
      }catch(TypeError){
        return false;
      }
  },
    closeDropdownAndAppointmentPopupsEvent() {
      window.addEventListener('click', (e) => {

        if ( e.target.classList.contains('calendar-active-date') || e.target.classList.contains('appointment')
            || this.hasParentClass(e.target, ['appointment-popup', 'appointment-data', 'control-nav'])) {
          return;
        }

        this.showCalendarDropDown = false;

        // close appointment popup in week type slider
        for (const [key, longAppointmentPopup] of Object.entries(this.$store.getters.getShowLongAppointmentPopup)) {
          this.$store.commit('setShowLongAppointmentPopup', {'day_key': key, 'status': false});
        }

        /** close all popups **/
        for (const [key, appointmentData] of Object.entries(this.appointments)) {
          appointmentData.map((app) => {
            app.popup  = false;
          });
        }
      })
    },

    createAppointment() {
      window.addEventListener('click', (e) => {

        if ( !this.hasParentClass(e.target, ['date', 'appointments'])
            || this.hasParentClass(e.target, ['appointment-info', 'appointment-popup', 'appointment-detail', 'appointment', 'slider-control'])) {
          return;
        }

        var dateObj = this.getDateObject(e.target);
        if( !dateObj.classList.contains('available') ) {
          return;
        }

        this.newAppointment = this.getFutureAppointmentDataByDateObj(dateObj);
        if ( this.isPast(this.newAppointment['date_timestamp']) ) { return; }

        this.$store.commit('setActionType', 'add');
        this.$store.commit('setEditRow', this.newAppointment);
        this.$store.commit('setShowEditAddForm', true);
      })
    },

    /** get date object from html **/
    getDateObject( target ) {
      if ( this.calendar.type == 'day' ) {
        return target.closest('.appointments');
      } else {
        return target.closest('.date');
      }

    },

    /** get date info from html for future appointment **/
    getFutureAppointmentDataByDateObj( dateObj ) {

      let result = {
        'status': 'pending',
        'service_id': '',
        'staff_id': '',
        'payment_status': 'pending',
        'payment_method': 'locally',
        'calendarType': this.calendar.type
      };

      switch ( this.calendar.type ) {
        case 'month':
          result['date_timestamp'] = this.encodeString( dateObj.querySelector('.appointments').id );
          /** if choosen today **/
          if ( this.moment.unix(result['date_timestamp']).isSame(new Date(), "day") ) {
            // set current hour and current minutes plus 5
            var date = new Date();
            date.setMinutes(new Date().getMinutes() + 5);
            result['date_timestamp'] = this.moment.unix(result['date_timestamp'])
                .add(date.getHours(), 'hours')
                .add( date.getMinutes(), 'minute')
                .format('X')
          }

          break;
        case 'week':
          result['date_timestamp'] = this.encodeString( dateObj.querySelector('.appointments').id );
          result['start_time']     = this.encodeString( dateObj.querySelector('.appointments').id );
          break;
        case 'day':
          var date_employee = this.encodeString( dateObj.id ).split("_");

          result['date_timestamp'] = date_employee[0];
          result['start_time']     = date_employee[0];
          result['staff_id']       = parseInt(date_employee[1]);
          break;
      }

      return result;
    }
  },
}