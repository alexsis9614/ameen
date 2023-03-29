export default {
  name: 'stepCategory',
  template: `
    <ul class="step-category">
    <li @click="selectCategory( category.id )" v-for="category in categories" :key="category.id" :class="{'active': category.id == appointment.category_id}">
        <span class="title">{{ category.name }}</span>
        <div class="services">{{ categoryServiceList(category.id) }}</div>
        <span class="selected-icon" v-if="category.id == appointment.category_id"></span>
      </li>
    </ul>`,
  components: {
  },
  data: () => ({}),
  computed: {
    appointment: {
      get() {
        return this.$store.getters.getAppointment;
      },
      set( appointment ) {
        this.$store.commit('setAppointment', appointment);
      }
    },
    categories() {
      return this.$store.getters.getCategories;
    },
    services() {
      return this.$store.getters.getServices;
    },
  },
  created() {},
  methods: {
    categoryServiceList( categoryId ) {
      var categoryServices = this.services.filter( service => service.category_id === categoryId ).map( service => service.title ).join(', ');

      if (categoryServices.length > 70) {
        return categoryServices.substring(0, 70) + '...';
      }
      return categoryServices;
    },
    selectCategory( categoryId ) {
      this.$store.commit('setSelectedCategory', categoryId);

      if ( this.appointment.category_id != categoryId ){
        var appointment = {'category_id': categoryId};
        this.appointment = appointment;
      }


      /** go to next step **/
      this.$store.commit('setCurrentStepKey', 'service');
    },
  },
}