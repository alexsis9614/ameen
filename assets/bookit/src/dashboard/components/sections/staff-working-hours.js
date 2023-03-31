
export default {
  template: `
    <div class="bookit-row">
      <div class="col-4">
        <label class="text-capitalize staff-services-label working-hours-label">{{ moment.weekdays( working_hour.weekday ) }}</label>
      </div>
      <div>
        <select @change="handleChangeWorkingHours($event)" name="start_time" class="width-auto">
          <option :value="null" :selected="item.start_time == null">
            {{ translations.day_off }}
          </option>
          <option v-for="time in getTimeList()" :value="time.value" :selected="item.start_time == time.value">
            {{ time.label }}
          </option>
        </select>
        <span v-show="!dayOff">
          &nbsp; <b>{{ translations.to }}</b> &nbsp;
          <select @change="handleChangeWorkingHours($event)" name="end_time" class="width-auto">
            <option v-for="time in getTimeList(toHour, toMinutes)" :value="time.value" :selected="item.end_time == time.value">
              {{ time.label }}
            </option>
          </select>
        </span>
      </div>
    </div>
  `,
  data: () => ({
    item: {},
    dayOff: true,
    toHour: 0,
    toMinutes: 0,
    translations: bookit_window.translations,
  }),
  props: {
    working_hour: {
      type: Object,
      required: true
    },
  },
  created() {
    this.item       = this.working_hour;
    this.toHour     = this.moment(this.item.start_time, 'h:mm a').format('H');
    this.toMinutes  = this.moment(this.item.start_time, 'h:mm a').format('m');
    if ( this.item.start_time !== null ) {
      this.dayOff = false;
    }
    /** use wordpress language **/
    this.moment.updateLocale( bookit_window.language, {});
  },
  methods: {
    handleChangeWorkingHours( event ) {
      var value = event.target.value;
      var start = this.item.start_time;
      var end   = this.item.end_time;

      if ( event.target.name == 'start_time' ) {
        this.dayOff = ( value === '' );
        start       = value;
        if ( this.dayOff ) {
          start = null;
          end   = null;
        }

        /** set default end time after enable day off **/
        if (this.item.end_time == null ) {
          end             = '18:00:00';
          this.toMinutes  = '00';
          this.toHour     = '00';
        }
      }

      if ( event.target.name == 'end_time' ) {
        end = value;
      }

      this.item.start_time  = start;
      this.item.end_time    = end;

      /** show warning info if edit **/
      var row = this.$store.getters.getEditRow;
      if ( row.id > 0 ) {
        this.$emit('updateShowEditWhInfo', true);
      }
    },
  }
}