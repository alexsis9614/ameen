import appointment_data from '@dashboard-calendar/sections/partials/appointment_data';
export default {
  template: `
    <div class="appointment-detail">
    <div v-if="loading" class="loader">
      <div class="loading"><div v-for="n in 9"></div></div>
    </div>
      <appointment_data :appointment="appointment" v-on:setLoader="setLoader"  ></appointment_data>
    </div>
  `,
  data: () => ({
    loading: false,
  }),
  name: "appointment_detail",
  components: {
    appointment_data,
  },
  props: {
    appointment: {
      required: true,
    },
  },
  methods: {
    setLoader( value ) {
      this.loading = value;
    },
  }
}