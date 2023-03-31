export default {
  computed: {
    cookieExpirationTime () {
      return 24 * 60 * 60 * 1000; // 1 day
    },
  },
  methods: {
    isMobile() {
      if( screen.width <= 760 ) {
        return true;
      }
      return false;
    },
    getPostHeaders() {
      return {
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'}
      }
    },
    generateFormData( data ) {
      let form_data = new FormData;
      for ( const [key, value] of Object.entries( data ) ) {
        if ( key !== 'unset' ) {
          if ( typeof value === 'object' ) {
            form_data.append( key, JSON.stringify(value) )
          } else {
            form_data.append( key, value );
          }
        }
      }
      return form_data;
    },
    getQueryFromObject( object ) {
      return Object.keys( object ).map( key => key + '=' + object[key] ).join('&');
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
    getTimeList( hour = 0, minutes = 0 ) {
      let hours = [];
      let $i    = 0;
      for ( hour; hour < 24; hour++ ) {
        let minute = ( $i === 0 ) ? parseInt(minutes) : parseInt(0);
        for ( minute; minute < 60; minute += 30 ) {
          let moment_time = this.moment({ hour, minute });
          hours.push( { value: moment_time.format('HH:mm:ss'), label: moment_time.format('h:mm a') } );
        }
        $i++;
      }
      return hours;
    },
    getDurationList() {
      let durations = [];
      let $i    = 0;
      for ( let hour = 0; hour < 12; hour++ ) {
        let minute = ( $i === 0 ) ? parseInt(15) : parseInt(0);
        for ( minute; minute < 60; minute += 15 ) {
          let duration = this.moment.duration({ minutes: minute, hours: hour});
          durations.push( { value: duration.asSeconds(), label: this.secondsToFormatted( duration.asMilliseconds() ) } );
        }
        $i++;
      }
      durations.push( { value: parseInt(43200), label: `12 h` } );
      return durations;
    },
    secondsToFormatted( seconds, toMilliseconds = false ) {
      if ( toMilliseconds ) seconds = seconds * 1000;
      let moment_time = this.moment.utc(seconds);
      let hours_label = ( moment_time.format('H') != 0 ) ? moment_time.format('h [h] ') : '';
      let minutes_label = ( moment_time.format('m') != 0 ) ? moment_time.format('m [min]') : '';
      return `${hours_label}${minutes_label}`;
    },

    /**
     * @param {moment} date_obj
     * @param {string} time_str: pattern (dd:dd:dd)
     * @returns {moment} obj
     */
    setTimeToDate(date_obj, time_str) {
      if ( !date_obj instanceof this.moment || !time_str){
        return date_obj;
      }

      var seconds = '00';
      if (time_str.split(':')[2]){
        seconds = time_str.split(':')[2];
      }

      return  date_obj.clone().set({
        hour: time_str.split(':')[0],
        minute: time_str.split(':')[1],
        second: seconds,
      })
    },

    /**
     *
     * @param {string} start_time timestamp
     * @returns {boolean}
     */
    isPast( startTime ) {
      var now = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      var start  = this.moment.unix(startTime);
      if ( now.isSameOrAfter(start) ) {
        return true
      }
      return false
    },

    /**
     * Get free staff by start time.
     * @param {array} staff
     * @param {array} services
     * @param {array} disabled_time_slots: exist appointments for choosen staff on choosen_date
     * @param {moment} choosen_date: appointment date
     * @return {array} staff: [{}] available staff
     */
    getFreeStaffByStartTime( staff, services, disabled_time_slots, startTime ) {

      let tempStaff = [...staff];
      var startTimeObj = this.moment.unix(startTime);

      /** remove staff from available list if have appointment that starts on choosen time **/
      for (var i = 0; i < disabled_time_slots.length; i++) {
        if (startTime == disabled_time_slots[i].start_time ) {
          tempStaff.splice(tempStaff.findIndex(st => st.id === disabled_time_slots[i].staff_id), 1);
        }
      }

      let now = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      if ( startTimeObj.isSameOrBefore(now) ) {
        return [];
      }

      for (var i = 0; i < tempStaff.length; i++) {

        let workingHours = tempStaff[i].working_hours.find( wh => wh.weekday === startTimeObj.isoWeekday() );

        let b_from  = this.setTimeToDate(startTimeObj, workingHours.break_from ),
            b_to    = this.setTimeToDate(startTimeObj, workingHours.break_to ),
            from    = this.setTimeToDate(startTimeObj, workingHours.start_time ),
            to      = this.setTimeToDate(startTimeObj, workingHours.end_time );

        /*  remove staff if time in break hours  */
        if ( b_from && b_to ) {
          if ( startTimeObj.isBetween(b_from, b_to, null, '[)') ) {
            tempStaff.splice(i, 1);
            i--;
            continue;
          }
        }
        /*  remove staff if time not in working hours  */
        if ( from && to ) {
            if ( startTimeObj.isBefore(from) || startTimeObj.isAfter(to) ) {
              tempStaff.splice(i, 1);
              i--;
              continue;
            }
        }
      }

      return tempStaff;
    },

    /**
     * Get available time slots for appointment.
     * Based on staff and service
     * @param {int} id: appointment id
     * @param {object} staff_working_hours
     * @param {array} possible_time_slots: time slots with step 15 minutes
     * @param {array} disabled_time_slots: exist appointments for choosen staff on choosen_date
     * @param {int} duration: service duration
     * @param {moment} choosen_date: appointment date
     * @return {object} slots: {start: [], end: []} possible_time_slots
     */
    getSeparateTimeSlots( id, staff_working_hours, possible_time_slots, disabled_time_slots, duration, choosen_date ) {

      let slots = {'start': [], 'end': [], 'busy_by_google': []},
          b_from  = this.setTimeToDate(choosen_date, staff_working_hours.break_from ),
          b_to    = this.setTimeToDate(choosen_date, staff_working_hours.break_to ),
          now     = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() }),
          from    = this.setTimeToDate(choosen_date, staff_working_hours.start_time ),
          to      = this.setTimeToDate(choosen_date, staff_working_hours.end_time );

      let timeFormat = this.getWPSettingsTimeFormat();

      if ( choosen_date.isSame(new Date(), "day") ) {
        if ( now.isSameOrAfter(to) ) {
          return {'start': [], 'end': []};
        }
        while ( from.isBefore(now) ) {
          from.add(duration, 'seconds');
        }
      }

      var busy_by_google = [];

      for (var i = 0; i < possible_time_slots.length; i++) {

        var possibleTimeSlot = this.setTimeToDate(choosen_date, possible_time_slots[i].value );
        var isInTimeBeforeEnd = to.clone().subtract(duration, 'seconds');

        /*  remove rest hours */
        if ( possibleTimeSlot.isBefore(from) || possibleTimeSlot.isAfter(isInTimeBeforeEnd) ) {
          continue;
        }

        /*  remove break hours  */
        if ( b_from && b_to ){
          var isInTimeBeforeBreak = b_from.clone().subtract(duration, 'seconds');
          if ( possibleTimeSlot.isBetween(isInTimeBeforeBreak, b_to, null, '()') ){
            continue;
          }
        }

        /*  remove busy time slots */
        var is_in_busy_time = false;

        for (var j = 0; j < disabled_time_slots.length; j++) {
          if ( id == disabled_time_slots[j].id) { continue;}
          var disabledSlotTimeEnd = this.moment.unix(disabled_time_slots[j].end_time);
          var isInTimeBeforeBusy = this.moment.unix(disabled_time_slots[j].start_time).subtract(duration, 'seconds');
          if ( possibleTimeSlot.isBetween(isInTimeBeforeBusy, disabledSlotTimeEnd, null, '()')
              && disabled_time_slots[j].status != 'google_calendar' ){
            is_in_busy_time = true;
            break;
          }
          if ( possibleTimeSlot.isBetween(isInTimeBeforeBusy, disabledSlotTimeEnd, null, '()')
              && disabled_time_slots[j].status == 'google_calendar' ) {
            busy_by_google.push(possibleTimeSlot.format('HH:mm:ss'));
          }
        }
        if (is_in_busy_time){
          continue;
        }

        slots['busy_by_google'] = busy_by_google;
        slots['start'].push({
          value: possibleTimeSlot.format('HH:mm:ss'),
          label: `${possibleTimeSlot.format( timeFormat )}`
        });

        var end = possibleTimeSlot.clone().add(duration, 'seconds');
        slots['end'].push({
          value: end.format('HH:mm:ss'),
          label: `${end.format( timeFormat )}`
        });
      }

      return slots;
    },

    /**
     * Get Wp Time Format Js Equivalent
     */
    getWPSettingsTimeFormat(){
      const phpToJsTimeFormatEquivalent = {'g': 'hh', 'H': 'HH', 'i': 'mm'};
      var timeFormat = this.$store.getters.getTimeFormat;

      Object.keys( phpToJsTimeFormatEquivalent ).forEach((key) => {
        timeFormat = timeFormat.replaceAll(key, phpToJsTimeFormatEquivalent[key]);
      });

      return timeFormat;
    },

    getCookie(name) {
      var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
      return value ? value[2] : false;
    },
    setCookie(name, value) {
      var date_expire = new Date();
      date_expire.setTime(date_expire.getTime() + this.cookieExpirationTime);
      document.cookie = name + '=' + value + ';expires=' + date_expire.toGMTString();
    },
    deleteCookie(name) { this.setCookie(name, '', -1); },

    isEqualDate(date1, date2) {
      return ( date1 && date2 && date1.format('D-M-Y') === date2.format('D-M-Y') );
    },

    /**
     * Compare current date hour with choosen date
     * @param {moment} chosen_date
     * @returns {boolean}
     */
    isCurrentDayHour(chosen_date) {
      let now = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      let end = chosen_date.clone().endOf('hour');

      if ( now.isBetween(chosen_date, end, null, '()') ){
        return true;
      }
      return false;
    },

    /**
     * Left and top offset data
     * @param {HTMLElement} element
     * @returns {top: number, left: number}
     */
    getOffset( element ) {
      var _x = 0;
      var _y = 0;
      while( element && !isNaN( element.offsetLeft ) && !isNaN( element.offsetTop ) ) {
        _x += element.offsetLeft - element.scrollLeft;
        _y += element.offsetTop - element.scrollTop;
        element = element.offsetParent;
      }
      return { top: _y, left: _x };
    },

    /**
     * @param {obj} appointment
     * @returns {boolean}
     */
    isInHourAppointment( appointment ) {
      var startHour = this.moment.unix(appointment.start_time).startOf('hour');
      var endHour = startHour.clone().add(1, 'hours');
      if ( this.moment.unix(appointment.end_time).isBetween(startHour, endHour, null, '(]') ){
        return true;
      }
      return false;
    },

    /**
     * Calculate height difference from appointment html tag and hour html tag in px
     * @param {obj} appointment
     * @returns {number}
     */
    getAppointmentHourDifferenceInHeight( appointment ) {
      var endTime = this.moment.unix(appointment.end_time);

      var appointmentElement  = this.$el.getElementsByClassName(['appointment', appointment.id].join('_'))[0];
      var appointmentOffset   = this.getOffset(appointmentElement);

      var hourElement = this.$parent.$el.getElementsByClassName(endTime.format('H'))[0];
      var hourOffset  = this.getOffset(hourElement);
      var height      = parseInt(hourOffset.top - appointmentOffset.top - appointmentElement.getBoundingClientRect().height);

      if (endTime.format('m') > 0 ){
        var perHourHeight = hourElement.clientHeight;
        height    = (parseInt(endTime.format('mm')*perHourHeight)/60) + height;
      }
      return height;
    },

    decodeString( string ) {
      return window.btoa(string);
    },
    encodeString( string ) {
      return window.atob(string);
    },
    checkEmail( email ) {
      if ( /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(email) ) {
        return true;
      }
      return false;
    },

    /** update object string boolean values to real boolean **/
    setCorrectBooleanInObject( object ) {
      Object.keys(object).forEach((key) => {
        if ( object[key] === 'true' ) {
          object[key] = true;
        } else if ( object[key] === 'false' ) {
          object[key] = false;
        }
      });
      return object;
    }
  }
}