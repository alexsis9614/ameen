import moment from 'moment-timezone'
moment.tz.setDefault('GMT');

export default {
  state: {
    appointment: {},
    appointments: {},
    errors: {},
    categories: [],
    currentStepKey: 'category',
    parentBlockWidth: '',
    services: [],
    staff: [],
    user: {},
    settings: {},
    serviceAppointments: [],
    selectedCategory: null,
    selectedService: null,
    selectedStaff: null,
    selectedTime: null,
    stepNavigation: [],
    disabledTimeSlots: [],
    showBookingForm: false,
    showDateTime: false,
    hideFilter: false,
    calendar: {
      curYear: moment().year(),
      curMonth: moment().month(),
      curDate: moment().date(),
      curWeek: null,
      curAppointmentsDate: null,
      loading: false
    },
    timeFormat: 'g:i a',
    timeSlotList: [],
    currentLanguage: '',
    loading: false,
    stripe: {
      stripe: '',
      elements: '',
      card: '',
      client_secret: ''
    },
  },
  getters: {
    getAppointment: state => state.appointment,
    getStepNavigation: state => state.stepNavigation,
    getLoading: state => state.loading,
    getErrors: state => state.errors,
    getAppointments: state => state.appointments,
    getCategories: state => state.categories,
    getCurrentStepKey: state => state.currentStepKey,
    getServices: state => state.services,
    getStaff: state => state.staff,
    getUser: state => state.user,
    getSettings: state => state.settings,
    getServiceAppointments: state => state.serviceAppointments,
    getSelectedCategory: state => state.selectedCategory,
    getSelectedService: state => state.selectedService,
    getSelectedStaff: state => state.selectedStaff,
    getSelectedTime: state => state.selectedTime,
    getDisabledTimeSlots: state => state.disabledTimeSlots,
    getShowBookingForm: state => state.showBookingForm,
    getShowDateTime: state => state.showDateTime,
    getHideFilter: state => state.hideFilter,
    getCalendar: state => state.calendar,
    getCalendarWeek: state => state.calendar.curWeek,
    getCalendarDay: state => state.calendar.curDate,
    getCalendarAppointmentsDate: state => state.calendar.curAppointmentsDate,
    getCalendarLoading: state => state.calendar.loading,
    getTimeFormat: state => state.timeFormat,
    getTimeSlotList: state => state.timeSlotList,
    getCurrentLanguage: state => state.currentLanguage,
    getStripe: state => state.stripe,
    getParentBlockWidth: state => state.parentBlockWidth,
  },
  mutations: {
    setAppointment(state, appointment) {
      state.appointment = appointment;
    },
    setLoading(state, loading) {
      state.loading = loading;
    },
    setAppointmentProperty(state, propertyName, propertyValue) {
      state.appointment[propertyName] = propertyValue;
    },
    setStepNavigation(state, navigation) {
      state.stepNavigation = navigation;
    },
    setErrors(state, errors) {
      state.errors = errors;
    },
    setAppointments(state, appointments) {
      state.appointments = appointments;
    },
    setCategories(state, categories) {
      state.categories = categories;
    },
    setCurrentStepKey(state, value) {
      state.currentStepKey = value;
    },
    setServices(state, services) {
      state.services = services;
    },
    setStaff(state, staff) {
      state.staff = staff;
    },
    setUser(state, user) {
      state.user = user;
    },
    setSettings(state, settings) {
      state.settings = settings;
    },
    setServiceAppointments(state, serviceAppointments) {
      state.serviceAppointments = serviceAppointments;
    },
    setSelectedService(state, selectedService) {
      state.selectedService = selectedService;
    },
    setSelectedCategory(state, category) {
      state.selectedCategory = category;
    },
    setSelectedStaff(state, selectedStaff) {
      state.selectedStaff = selectedStaff;
    },
    setSelectedTime(state, time) {
      state.selectedTime = time;
    },
    setDisabledTimeSlots(state, disabledTimeSlots) {
      state.disabledTimeSlots = disabledTimeSlots;
    },
    setShowBookingForm(state, showBookingForm) {
      state.showBookingForm = showBookingForm;
    },
    setShowDateTime(state, showDateTime) {
      state.showDateTime = showDateTime;
    },
    setHideFilter(state, hideFilter) {
      state.hideFilter = hideFilter;
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
    setCalendarDay(state, day) {
      state.calendar.curDate = day;
    },
    setCalendarAppointmentsDate(state, date) {
      state.calendar.curAppointmentsDate = date;
    },
    setCalendarLoading(state, value) {
      state.calendar.loading = value;
    },
    setTimeFormat(state, timeFormat) {
      state.timeFormat = timeFormat;
    },
    setTimeSlotList(state, timeSlotList) {
      state.timeSlotList = timeSlotList;
    },
    setCurrentLanguage(state, currentLanguage) {
      state.currentLanguage = currentLanguage;
    },
    setStripe(state, stripe) {
      state.stripe = stripe;
    },
    setParentBlockWidth(state, width) {
      state.parentBlockWidth = width;
    }
  },
  actions: {
    setCalendarMonthYear({commit, getters}, selectedMonth) {
      commit('setCalendarMonth', selectedMonth.month());
      commit('setCalendarYear', selectedMonth.year());
    }
  }
}