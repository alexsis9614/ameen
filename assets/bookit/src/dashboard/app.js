import Vue from 'vue'
import Axios from 'axios'
import VueAxios from 'vue-axios'
import Toasted from 'vue-toasted'
import moment from 'moment-timezone'
import mixin from '@dashboard-mixins/index'
import store from '@dashboard-store/index'
import Appointments from '@dashboard-components/appointments'
import Services from '@dashboard-components/services'
import Staff from '@dashboard-components/staff'
import Customers from '@dashboard-components/customers'
import Settings from '@dashboard-components/settings'
import { VuejsDatatableFactory } from 'vuejs-datatable'
import VueInputAutowidth from 'vue-input-autowidth'
import Calendar from '@dashboard-components/calendar'

/**
 * Init Moment
 */
moment.tz.setDefault('GMT');
Vue.prototype.moment = moment;
Vue.filter('moment', function (date, format) {
  return moment(date).format(format);
});

VuejsDatatableFactory.useDefaultType( false )
  .registerTableType( 'datatable', tableType => tableType.mergeSettings( {
    table: {
      class:   'bookit-table',
      sorting: {
        sortAsc:  '↑',
        sortDesc: '↓',
        sortNone: '<span class="arrow-up-down"></span>',
      },
    }
  } ) );

window.addEventListener('load', () => {
  Vue.use( VuejsDatatableFactory );
  Vue.use( VueInputAutowidth );
  Vue.use( VueAxios, Axios );
  Vue.use( Toasted, {
    duration: 2000
  } );
  Vue.mixin( mixin );

  new Vue({
    store, // render vuex
    el: '#bookit-dashboard-app', // selector
    components: {
      'bookit-appointments': Appointments,
      'bookit-services': Services,
      'bookit-staff': Staff,
      'bookit-customers': Customers,
      'bookit-settings': Settings,
      'bookit': Calendar,
    },
  });
});