export default {
  computed: {
    cookieExpirationTime () {
      return 24 * 60 * 60 * 1000; // 1 day
    },
  },
  methods: {
    isEqualDate(date1, date2) {
      return ( date1 && date2 && date1.format('D-M-Y') === date2.format('D-M-Y') );
    },
    getStaffClearPrice( staff, service ) {
      let current_service = staff.staff_services.find(staff_service => staff_service.id == service.id);
      return parseFloat(current_service.price);
    },
    getStaffPrice( staff, service, settings ) {
      let current_service = staff.staff_services.find(staff_service => staff_service.id == service.id);
      return this.generatePrice(parseFloat(current_service.price), settings);
    },
    generatePrice( price, settings ) {
      let formatted_price = parseFloat(price).toFixed(settings.decimals_number).replace('.', settings.decimals_separator);
      formatted_price     = formatted_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, settings.thousands_separator);
      if ( settings.currency_position === 'left' ) {
        formatted_price = settings.currency_symbol + formatted_price;
      } else {
        formatted_price += settings.currency_symbol;
      }
      return formatted_price;
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
    getTimeSlots( from_time, to_time, break_from, break_to, duration, today = false ) {
      let slots = [],
        from    = this.moment(from_time, 'HH:mm:ss'),
        to      = this.moment(to_time, 'HH:mm:ss'),
        b_from  = this.moment(break_from, 'HH:mm:ss'),
        b_to    = this.moment(break_to, 'HH:mm:ss'),
        now     = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() });
      if ( today ) {
        if ( now.isSameOrAfter(to) ) {
          return [];
        }
        while ( from.isBefore(now) ) {
          from.add(duration, 'seconds');
        }
      }
      while ( from < to ) {
        let temp = from.clone().add(duration, 'seconds');
        if (
          temp.isSameOrBefore(to) &&
          ( !b_from || !b_to ||
            (
              ( !from.isBetween(b_from, b_to, null, '[)') && !temp.isBetween(b_from, b_to, null, '(]') ) &&
              ! ( b_from.isSameOrAfter(from) && b_to.isSameOrBefore(temp) )
            )
          )
        ) {
          slots.push({
            value: from.format('HH:mm:ss'),
            label: `${from.format('HH:mm')} - ${temp.format('HH:mm')}`
          });
        }
        from = temp;
      }
      return slots;
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
     * Get available time slots for appointment.
     * Based on staff and service
     * @param {object} staff_working_hours
     * @param {array} possible_time_slots: time slots with step 15 minutes
     * @param {array} disabled_time_slots: exist appointments for choosen staff on choosen_date
     * @param {int} duration: service duration
     * @param {moment} choosen_date: appointment date
     * @return {object} slots: {start: [], end: []} possible_time_slots
     */
    getSeparateTimeSlots( staff_working_hours, possible_time_slots, disabled_time_slots, duration, choosen_date ) {

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

    /**
     * @param {object} staff_working_hours
     * @param {array} possible_time_slots: time slots with step 15 minutes
     * @param {array} disabled_time_slots: exist appointments for choosen staff on choosen_date
     * @param {int} duration: service duration
     * @param {moment} choosen_date: appointment date
     * @return {array} slots
     */
    getFreeTimeSlotsForStaff( staff_working_hours, possible_time_slots, disabled_time_slots, duration, choosen_date ) {

      let slots = [],
          b_from  = this.setTimeToDate(choosen_date, staff_working_hours.break_from ),
          b_to    = this.setTimeToDate(choosen_date, staff_working_hours.break_to ),
          now     = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() }),
          from    = this.setTimeToDate(choosen_date, staff_working_hours.start_time ),
          to      = this.setTimeToDate(choosen_date, staff_working_hours.end_time );

      let timeFormat = this.getWPSettingsTimeFormat();

      if ( choosen_date.isSame(new Date(), "day") ) {
        if ( now.isSameOrAfter(to) ) {
          return [];
        }
        while ( from.isBefore(now) ) {
          from.add(duration, 'seconds');
        }
      }

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
          var disabledSlotTimeEnd = this.moment.unix(disabled_time_slots[j].end_time);
          var isInTimeBeforeBusy = this.moment.unix(disabled_time_slots[j].start_time).subtract(duration, 'seconds');
          if ( possibleTimeSlot.isBetween(isInTimeBeforeBusy, disabledSlotTimeEnd, null, '()') ){
            is_in_busy_time = true;
            break;
          }
        }
        if (is_in_busy_time){
          continue;
        }

        var end = possibleTimeSlot.clone().add(duration, 'seconds');
        slots.push({
          value: possibleTimeSlot.format('HH:mm:ss'),
          label: `${possibleTimeSlot.format( timeFormat )} - ${end.format( timeFormat )}`
        });
      }

      return slots;
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

    /**
     * Search arraySeek items in arrayWhere
     * @param arraySeek
     * @param arrayWhere
     * @returns {boolean}
     */
    isArrayItemsInArray( arraySeek, arrayWhere ) {
      for (var i = 0; i < arraySeek.length; i++ ) {
        if ( !arrayWhere.includes(arraySeek[i]) ) {
          return false;
        }
      }
      return true;
    },

    /**
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

    /** check email **/
    validEmail( email ) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    /** check phone **/
    validPhone( phone ) {
      const regex = /^((\+)?[0-9]{8,14})$/;
      return regex.test(phone);
    },

    /**
     * @param appointment // todo implement later
     */
    checkIsFreeAppointment ( appointment ) {
      const data = {
        nonce: bookit_window.nonces.bookit_is_free_appointment,
        staff_id: appointment.staff_id,
        service_id: appointment.service_id,
        date_timestamp: appointment.date_timestamp,
        start_time: appointment.start_time,
        end_time: appointment.end_time,
      };
      this.axios.post(`${bookit_window.ajax_url}?action=bookit_is_free_appointment`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          return response.data.is_free;
        }else{
          return false;
        }
      });
    },
    /**
     * @returns {boolean}
     */
    formValidation( appointment, settings ) {
      var errors = {};

      if ( ! appointment.full_name || appointment.full_name.length === 0 ) {
        errors.full_name = bookit_window.translations.required_field;
      }else if ( appointment.full_name.length < 3 || appointment.full_name.length > 25 ){
        errors.full_name = bookit_window.translations.full_name_wrong_length;
      }

      if ( appointment.phone && !this.validPhone( appointment.phone )) {
        errors.phone = bookit_window.translations.invalid_phone;
      }

      if ( ! appointment.email || appointment.email.length === 0  ) {
        errors.email = bookit_window.translations.no_email;
      }else if ( ! this.validEmail( appointment.email ) ) {
        errors.email = bookit_window.translations.invalid_email;
      }

      // if ( settings.booking_type === 'registered' && ! appointment.user_id ) {
      //   if ( ! appointment.password || appointment.password.length === 0) {
      //     errors.password = 'Please enter a password';
      //   }
      //   if ( appointment.password !== appointment.password_confirmation ) {
      //     errors.password_confirmation = bookit_window.translations.confirmation_mismatched;
      //   }
      // }
      return errors;
    },

    isMobile() {
      if( screen.width <= 760 ) {
        return true;
      }
      return false;
    },
    isTablet() {
      if( screen.width > 760 && screen.width < 836 ) {
        return true;
      }
      return false;
    },

    getUniqueObjArrayKey(data, key) {
      return [...new Map(data.map(item => [item[key], item])).values()]
    }
  }
}