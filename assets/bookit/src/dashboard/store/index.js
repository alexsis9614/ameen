import Vue from 'vue'
import Vuex from 'vuex'
import moment from "moment-timezone";

Vue.use(Vuex);
export default new Vuex.Store({
  state: {
    actionType: 'add',
    addons: [],
    appointments: [],
    customers: [],
    editView: false,
    rows: [],
    categories: [],
    services: [],
    filter_appointments: {},
    filter_services: [],
    staff: [],
    wp_users: [],
    showEditAddForm: false,
    showDeleteForm: false,
    showFeedbackForm: false,
    editRow: {},
    errors: {},
    timeFormat: 'g:i a',
    timeSlotList: [],
    disabledTimeSlotList: [],
    paymentMethods: {},
    appointmentStatuses: {},
    activeTab: {},
    settings: {},
    calendar: {
      curAppointmentsDate: null,
      isDetailedView: false,
      type: 'month',
      loading: false,
    },
    isObjectUpdated: false,
    showLongAppointmentPopup: {},
    currentLanguage: '',
  },
  getters: {
    getActionType: state => state.actionType,
    getAddons: state => state.addons,
    getEditView:state => state.editView,
    getShowLongAppointmentPopup: state => state.showLongAppointmentPopup,
    getIsObjectUpdated: state => state.isObjectUpdated,
    getActiveTab: state => state.activeTab,
    getAppointments: state => state.appointments,
    getRows: state => state.rows,
    getCategories: state => state.categories,
    getServices: state => state.services,
    getCustomers: state => state.customers,
    getFilterServices: state => state.filter_services,
    getFilterAppointments: state => state.filter_appointments,
    getShowDeleteForm: state => state.showDeleteForm,
    getStaff: state => state.staff,
    getWpUsers: state => state.wp_users,
    getShowEditAddForm: state => state.showEditAddForm,
    getShowFeedbackForm: state => state.showFeedbackForm,
    getEditRow: state => state.editRow,
    getErrors: state => state.errors,
    getPaymentMethods: state => state.paymentMethods,
    getAppointmentStatuses: state => state.appointmentStatuses,
    getTimeSlotList: state => state.timeSlotList,
    getTimeFormat: state => state.timeFormat,
    getSettings: state => state.settings,
    getCalendar: state => state.calendar,
    getCalendarWeek: state => state.calendar.curWeek,
    getCalendarAppointmentsDate: state => state.calendar.curAppointmentsDate,
    getCalendarLoading: state => state.calendar.loading,
    getCurrentLanguage: state => state.currentLanguage,
  },
  mutations: {
    setCurrentLanguage(state, currentLanguage) {
      state.currentLanguage = currentLanguage;
    },
    setActionType(state, type) {
      state.actionType = type;
    },
    setAddons(state, addons) {
      state.addons = addons;
    },
    setEditView(state, editView) {
      state.editView = editView;
    },
    setShowLongAppointmentPopup(state, data) {
      state.showLongAppointmentPopup[data['day_key']] = data['status'];
    },
    setIsObjectUpdated(state, value) {
      state.isObjectUpdated = value;
    },
    setActiveTab(state, tab) {
      state.activeTab = tab;
    },
    setAppointments(state, appointments) {
      state.appointments = appointments;
    },
    setRows(state, rows) {
      state.rows = rows;
    },
    setEditedRow(state, row) {
      const index = state.rows.findIndex(item => parseInt( item.id) === parseInt(row.id));
      if (index !== -1) state.rows.splice(index, 1, row);
    },
    unshiftRows (state, row) {
      state.rows.unshift(row);
    },
    setCategories(state, categories) {
      state.categories = categories;
    },
    setServices(state, services) {
      state.services = services;
    },
    setCustomers(state, customers) {
      state.customers = customers;
    },
    setFilterServices(state, services) {
      state.filter_services = services;
    },
    setFilterAppointments(state, filter) {
      state.filter_appointments = filter;
    },
    setStaff(state, staff) {
      state.staff = staff;
    },
    setWpUsers(state, wp_users) {
      state.wp_users = wp_users;
    },
    setShowEditAddForm(state, showEditAddForm) {
      state.showEditAddForm = showEditAddForm;
    },
    setShowDeleteForm(state, showDeleteForm) {
      state.showDeleteForm = showDeleteForm;
    },
    setShowFeedbackForm(state, showFeedbackForm) {
      state.showFeedbackForm = showFeedbackForm;
    },
    setEditRow(state, row) {
      state.editRow = row;
    },
    setErrors(state, errors) {
      state.errors = errors;
    },
    setTimeFormat(state, timeFormat) {
      state.timeFormat = timeFormat;
    },
    setTimeSlotList(state, timeSlotList) {
      state.timeSlotList = timeSlotList;
    },
    setPaymentMethods(state, paymentMethods) {
      state.paymentMethods = paymentMethods;
    },
    setAppointmentStatuses(state, appointmentStatuses) {
      state.appointmentStatuses = appointmentStatuses;
    },
    setSettings(state, settings) {
      state.settings = settings;
    },
    setCalendarMonth(state, month) {
      state.calendar.curMonth = month;
    },
    setCalendarYear(state, year) {
      state.calendar.curYear = year;
    },
    setCalendarWeek(state, week) {
      state.calendar.curWeek = week;
    },
    setCalendarAppointmentsDate(state, date) {
      state.calendar.curAppointmentsDate = date;
    },
    setCalendarLoading(state, value) {
      state.calendar.loading = value;
    },
  },
  actions: {
    addCustomer({commit, getters}, customer) {
      let customers = getters.getCustomers;
      customers.push(customer);
      commit('setCustomers', customers);
    },
    addWPUser({commit, getters}, wp_user) {
      let wp_users = getters.getWpUsers;
      wp_users.push(wp_user);
      commit('setWpUsers', wp_users);
    },
    delete_appointment({commit, getters}, row) {
      let rows = getters.getRows;
      rows.splice(rows.findIndex(i => i.id === row.id), 1);
      commit('setRows', rows);
    },
    delete_category({commit, getters}, category) {
      let categories = getters.getCategories;
      let rows = getters.getRows;

      // remove services connected to category
      var indices = rows.map((e, i) => e.category_id === category.id ? i : '').filter(String);
      for (let i = 0; i < indices.length; i++) {
        rows.splice(rows.findIndex(i => i.category_id === category.id), 1);
      }

      categories.splice(categories.findIndex(i => i.id === category.id), 1);
      commit('setRows', rows);
      commit('setCategories', categories);
    },
    delete_item({commit, getters}, item) {
      let rows = getters.getRows;
      rows.splice(rows.findIndex(i => i.id === item.id), 1);
      commit('setRows', rows);
    },
    delete_row({commit, getters}, row) {
      let rows = getters.getRows;
      rows.splice(rows.indexOf(row), 1);
      commit('setRows', rows);
    },
    setCalendarMonthYear({commit, getters}, selectedMonth) {
      commit('setCalendarMonth', selectedMonth.month());
      commit('setCalendarYear', selectedMonth.year());
    },
    removeAppointment({commit, getters}, row) {
      let appointments = getters.getAppointments;
      if ( appointments[moment.unix(row.start_time).format('D_M')] !== undefined ) {
        appointments[moment.unix(row.start_time).format('D_M')].splice(appointments[moment.unix(row.start_time).format('D_M')].findIndex(i => i.id === row.id), 1);
      }
      commit('setAppointments', appointments);
    },
  },
  modules: {},
})