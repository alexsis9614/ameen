import Filters from '@components/default/sidebar-filters'
import MonthlyCalendar from '@components/default/monthly-calendar'
import BookingForm from '@components/default/sections/booking-form'

export default {
  template: `
    <div class="default-theme">
      <sidebar-filters></sidebar-filters>
      <monthly-calendar></monthly-calendar>
      <bookit-booking-form v-if="showBookingForm"></bookit-booking-form>
    </div>
  `,
  components: {
    'sidebar-filters': Filters,
    'monthly-calendar': MonthlyCalendar,
    'bookit-booking-form': BookingForm
  },
  computed: {
    showBookingForm () {
      return this.$store.getters.getShowBookingForm;
    }
  }
}