import { Hooper, Slide } from 'hooper';

export default {
  template: `
    <div v-if="!hideFilter" :class="['sidebar-filters', {'no-header': settings.hide_header_titles}]">
      <div v-if="!settings.hide_header_titles" class="header">
        <span v-if="selectedCategory" @click="backToCategories" class="back-icon"></span>
        <div v-if="showCategories()">{{ translations.choose_category }}</div>
        <div v-else>{{ translations.choose_service }}</div>
      </div>
      <transition name="categories-slide">
        <div v-if="showCategories()" class="filters-container categories-filter">
          <template v-if="categories.length > 5">
            <button class="slide-button" @click.prevent="slidePrev"><span class="up-icon"></span></button>
            <hooper ref="carousel" :vertical="true" :itemsToShow="5" :wheelControl="false" :trimWhiteSpace="true">
              <slide v-for="category in categories" :key="category.id">
                <div class="filter category-carousel" @click="selectCategory(category)">
                  <div class="filter-info-bar">
                    <div class="filter-info">
                      <div class="title">{{ category.name }}</div>
                      <div class="description">{{ categoryServices(category) }}</div>
                    </div>
                  </div>
                  <span v-if="category.id === selectedCategory" class="selected-icon"></span>
                </div>
              </slide>
            </hooper>
            <button class="slide-button bottom" @click.prevent="slideNext"><span class="down-icon"></span></button>
          </template>
          <template v-else>
            <div class="filter category" v-for="category in categories" @click="selectCategory(category)">
              <div class="filter-info-bar">
                <div class="filter-info">
                  <div class="title">{{ category.name }}</div>
                  <div class="description">{{ categoryServices(category) }}</div>
                </div>
              </div>
              <span v-if="category.id === selectedCategory" class="selected-icon"></span>
            </div>
          </template>
        </div>
      </transition>
      <transition name="services-slide">
        <div v-if="!showCategories()" class="filters-container">
          <template v-if="availableServices.length > 0">
            <template v-if="availableServices.length > 6">
              <button class="slide-button" @click.prevent="slidePrev"><span class="up-icon"></span></button>
              <hooper ref="carousel" :vertical="true" :itemsToShow="5" :wheelControl="false" :trimWhiteSpace="true">
                <slide v-for="service in availableServices" :key="service.id">
                  <div
                  :class="['filter', {
                    'active': selectedService && service.id === selectedService.id,
                    'disabled': disabledServices.indexOf(service) >= 0
                  }]"
                  @click="disabledServices.indexOf(service) < 0 && selectService(service)"
                  >
                    <div class="filter-info-bar">
                      <img v-if="service.icon_url" :src="service.icon_url" class="service-icon" :alt="service.title">
                      <div class="filter-info">
                        <div class="title">{{ service.title }}</div>
                        <div class="description">
                          <span v-if="isServicePriceEqualForAllStaff(service) === false">
                            {{ translations.from }}:
                          </span>
                          {{ generatePrice(serviceMinPrice(service), settings) }}
                        </div>
                      </div>
                    </div>
                    <span v-if="selectedService && service.id === selectedService.id" class="selected-icon"></span>
                  </div>
                </slide>
              </hooper>
              <button class="slide-button bottom" @click.prevent="slideNext"><span class="down-icon"></span></button>
            </template>
            <template v-else>
              <div
                :class="['filter', {
                  'active': selectedService && service.id === selectedService.id,
                  'disabled': disabledServices.indexOf(service) >= 0
                }]"
                v-for="service in availableServices"
                @click="disabledServices.indexOf(service) < 0 && selectService(service)"
                :key="service.id"
                >
                <div class="filter-info-bar">
                  <img v-if="service.icon_url" :src="service.icon_url" class="service-icon" :alt="service.title">
                  <div class="filter-info">
                    <div class="title">{{ service.title }}</div>
                    <div class="description">
                          <span v-if="isServicePriceEqualForAllStaff(service) === false">
                            {{ translations.from }}:
                          </span>
                      {{ generatePrice(serviceMinPrice(service), settings) }}
                    </div>
                  </div>
                </div>
                <span v-if="selectedService && service.id === selectedService.id" class="selected-icon"></span>
              </div>
            </template>
          </template>
          <template v-else>
            <div class="filter disabled" >
              <div>
                <div class="title">{{ translations.no_services }}</div>
              </div>
            </div>
          </template>
        </div>
      </transition>
    </div>
  `,
  components: {
    Hooper,
    Slide
  },
  data: () => ({
    selectedCategory: null,
    translations: bookit_window.translations
  }),
  computed: {
    settings () {
      return this.$store.getters.getSettings;
    },
    calendar () {
      return this.$store.getters.getCalendar;
    },
    categories() {
      return this.$store.getters.getCategories;
    },
    services() {
      return this.$store.getters.getServices;
    },
    staff() {
      return this.$store.getters.getStaff;
    },
    calendarAppointmentsDate() {
      return this.$store.getters.getCalendarAppointmentsDate;
    },
    selectedService: {
      get() {
        return this.$store.getters.getSelectedService;
      },
      set( service ) {
        this.$store.commit('setSelectedService', service);
      }
    },
    calendarLoading: {
      get() {
        return this.$store.getters.getCalendarLoading;
      },
      set( value ) {
        this.$store.commit('setCalendarLoading', value);
      }
    },
    hideFilter: {
      get() {
        return this.$store.getters.getHideFilter;
      },
      set( value ) {
        this.$store.commit('setHideFilter', value);
      }
    },
    availableServices() {
      return this.services.filter( item => item.category_id === this.selectedCategory );
    },
    disabledServices() {
      let services = [];
      if ( this.calendarAppointmentsDate ) {
        let staff = [...this.staff];
        services  = [...this.services];
        staff = staff.filter( staff => {
          return staff.working_hours.some( wh => ( wh.weekday === this.calendarAppointmentsDate.weekday() && wh.start_time !== null ));
        });
        staff.forEach( staff => {
          staff.staff_services.forEach( staff_service => {
            services = services.filter( service => service.id != staff_service.id );
          });
        });
      }
      return [...new Set(services)];
    },
  },
  created() {
    if ( this.categories.length <= 1 ) {
      this.selectedCategory = ( this.categories[0] ) ? this.categories[0].id : null;
    }
    if ( this.services.length === 1 ) {
      this.selectedService  = this.services[0];
      this.hideFilter       = true;
    }
  },
  methods: {
    showCategories() {
      if ( this.categories.length === 0 ) {
        return false;
      }
      return !this.selectedCategory;
    },
    selectCategory( category ) {
      this.selectedCategory = category.id;
    },
    categoryServices( category ) {
      var categoryServices = this.services.filter( service => service.category_id === category.id ).map( service => service.title ).join(', ');

      if (categoryServices.length > 90) {
        return categoryServices.substring(0, 90) + '...';
      }

      return categoryServices;
    },
    selectService( service ) {
      this.selectedService = service;
      this.monthAppointments();
    },
    async monthAppointments() {
      if ( this.selectedService ) {
        this.calendarLoading  = true;
        const current_month   = this.moment().month(this.calendar.curMonth).year(this.calendar.curYear);
        const month_data      = {
          nonce: bookit_window.nonces.bookit_month_appointments,
          service_id: this.selectedService.id,
          today_timestamp: this.moment().startOf('day').unix(),
          now_timestamp: this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() }).unix(),
          start_timestamp: (this.moment().month() === current_month.month()) ? current_month.add(1, 'day').startOf('day').unix() : current_month.startOf('month').unix(),
          end_timestamp: current_month.add(1, 'months').startOf('month').unix()
        };

        await this.axios.post(`${bookit_window.ajax_url}?action=bookit_month_appointments`, this.generateFormData(month_data), this.getPostHeaders()).then((res) => {
          let response = res.data;
          if ( response.success ) {
            this.$store.commit('setServiceAppointments', response.data);
          }
        });

        if ( this.calendarAppointmentsDate ) {
          const data = {
            nonce: bookit_window.nonces.bookit_day_appointments,
            date_timestamp: this.calendarAppointmentsDate.unix()
          };
          await this.axios.post(`${bookit_window.ajax_url}?action=bookit_day_appointments`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
            let response = res.data;
            if ( response.success ) {
              this.$store.commit('setDisabledTimeSlots', response.data);
            }
          });
        }

        this.calendarLoading  = false;
      }
    },

    getStaffPricesForService( service ) {
      let staff     = [...this.staff];
      staff         = staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == service.id )
      });

      var prices = [];
      if ( staff.length > 0 ) {
        staff.forEach((staff) => {
          staff.staff_services.filter( staff_service => {
            if ( staff_service.id == service.id ) {
              prices.push(staff_service.price);
            }
          });
        })
      }
      return prices;
    },

    isServicePriceEqualForAllStaff ( service ) {
      /** check if setting not exist and disabled **/
      if ( !this.settings.hasOwnProperty('hide_from_for_equal_service_price')
          || this.settings.hide_from_for_equal_service_price === false ){
        return false;
      }

      /** if enabled , check is equal price **/
      let prices = this.getStaffPricesForService(service);
      return prices.every( (val, i, arr) => val === prices[0] );
    },

    serviceMinPrice( service ) {
      let min_price = service.price;
      let staff     = [...this.staff];
      staff         = staff.filter( staff => {
        return staff.staff_services.some( staff_service => staff_service.id == service.id )
      });

      let prices = this.getStaffPricesForService(service);
      if ( prices.length > 0 ){
        min_price  = Math.min(...prices);
      }
      return parseFloat(min_price);
    },

    backToCategories() {
      this.selectedCategory = null;
      this.selectedService = null;
    },
    slidePrev() {
      this.$refs.carousel.slidePrev();
    },
    slideNext() {
      this.$refs.carousel.slideNext();
    }
  },
  watch: {
    'calendar.curMonth' () {
      this.monthAppointments();
    }
  }
}