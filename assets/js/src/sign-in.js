import Vue from 'vue';
import Axios from 'axios';
import VueAxios from 'vue-axios';
import MaskedInput from 'vue-masked-input';
const $ = require('jquery');

$(window).on('load', () => {
    Vue.use( VueAxios, Axios );

    $('.stm-lms-sign-in:not(.loaded)').each(function () {
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
                    enter_name: '',
                    password_re: '',
                    verify: false,
                    register: false,
                    password: false,
                    limit: false,
                    sent_limit: false,
                    resend: false,
                    message: '',
                    status: '',
					position: '',
                    object: {}
                };
            },
            mounted: function () {
				let object = {};

                if ( typeof stm_lms_sign_in_form !== 'undefined' ) {
					object = stm_lms_sign_in_form;
                }
				else if ( typeof stm_lms_sign_in_modal !== 'undefined' ) {
					object = stm_lms_sign_in_modal;
				}

				this.object = object;

				if ( this.object.position !== undefined ) {
					this.position = this.object.position;
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
                        vm.create_account();
                    } else {
                        vm.signIn();
                    }
                },
                signIn: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.sign_in + '&_ajax_nonce=' + vm.object.nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'email': vm.email,
                        'phone': vm.phone,
                        'limit': vm.limit
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
                            else if ( vm.status === 'limit' ) {
                                vm.limit = true;
                            }
                            else if ( vm.status === 'sent_request_limit' ) {
                                vm.limit = false;
                                vm.sent_limit = true;
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
                create_account: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.create_account + '&_ajax_nonce=' + vm.object.nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'phone': vm.phone,
                        'register': vm.register,
                        'password': vm.enter_password,
                        'name': vm.enter_name,
						'position': vm.object.position,
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