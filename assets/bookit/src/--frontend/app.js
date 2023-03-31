import Vue from 'vue'
import Vuex from 'vuex'
import Axios from 'axios'
import VueAxios from 'vue-axios'
import Toasted from 'vue-toasted'
import mixin from '@mixins/index'
import moment from 'moment-timezone'
import Bookit from '@views/bookit'
import store from '@store/index'
const _ = require('lodash')
const $ = require('jquery')

/**
 * Init Moment
 */
moment.tz.setDefault('GMT');
Vue.prototype.moment = moment;
Vue.filter('moment', function (date, format) {
  return moment(date).format(format);
});
Vue.use(Vuex)


$( document ).ready(() => {
  Vue.use( VueAxios, Axios );
  Vue.mixin( mixin );
  Vue.use( Toasted);

  $('.bookit-app').each(function () {
    new Vue({
      el: $(this)[0],// selector
      beforeCreate() {
        this.$store = createStore(_.cloneDeep(store));
      },
      components: {
        'bookit': Bookit, // Frontend main component
      },
    });
  });

});

function createStore (store) {
  return new Vuex.Store(store);
}

