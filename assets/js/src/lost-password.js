import Vue from 'vue';
import Axios from 'axios';
import VueAxios from 'vue-axios';
import MaskedInput from 'vue-masked-input';
const $ = require('jquery');

$(window).on('load', () => {
    Vue.use( VueAxios, Axios );

    $('.stm-lms-lost-password:not(.loaded)').each(function () {
        $(this).addClass('loaded');

        new Vue({
            el: this, // selector
            components: {
                MaskedInput
            },
            data: function () {
                return {
                    vue_loaded: true,
                    loading: false,
                    phone: '',
                    email: '',
                    code: '',
                    enter_password: '',
                    password_re: '',
                    verify: false,
                    register: false,
                    password: false,
                    resend: false,
                    message: '',
                    status: '',
                    object: {}
                };
            },
            mounted: function () {
                if (typeof stm_lms_sign_in !== 'undefined') {
                    this.object = stm_lms_sign_in;
                }
            },
            methods: {
                formSubmit: function () {
                    let vm = this;

                    vm.status  = '';
                    vm.message = '';

                    if (vm.verify && ! vm.resend) {
                        vm.verification();
                    }
                    else if ( vm.password ) {
                        vm.reset_password();
                    }
                    else {
                        vm.lostPassword();
                    }
                },
                lostPassword: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.lost_password + '&_ajax_nonce=' + vm.object.lost_password_nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'phone': vm.phone,
                    };

                    vm.$http.post(url, data).then(
                        function (response) {
                            vm.status = response.data['status'];
                            vm.message = response.data['message'];
                            vm.loading = false;

                            if (vm.status === 'success') {
                                vm.verify = true;
                            }
                            else if ( vm.status === 'password' ) {
                                vm.password = true;
                            }

                            vm.resend = false;
                        }
                    );
                },
                verification: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.verification + '&_ajax_nonce=' + vm.object.nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'phone': vm.phone,
                        'code': vm.code
                    };

                    vm.$http.post(url, data).then(
                        function (response) {
                            vm.status = response.data['status'];
                            vm.message = response.data['message'];
                            vm.loading = false;

                            if (vm.status === 'success') {
                                vm.password = true;
                                vm.register = true;
                                vm.verify   = false;
                            }
                        }
                    );
                },
                reset_password: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.reset_password + '&_ajax_nonce=' + vm.object.lost_password_nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'phone': vm.phone,
                        'password': vm.enter_password,
                        'password_re': vm.password_re
                    };

                    vm.$http.post(url, data).then(
                        function (response) {
                            vm.status  = response.data['status'];
                            vm.message = response.data['message'];
                            vm.loading = false;

                            if ( vm.status === 'success' ) {
                                vm.password = true;
                                vm.register = false;
                            }

                            if ( response.data['user_page'] ) {
                                window.location = response.data['user_page'];
                            }
                        }
                    );
                }
            }
        });
    });
});