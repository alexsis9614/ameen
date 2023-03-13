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
                    code: '',
                    verify: false,
                    message: '',
                    status: '',
                    object: {}
                };
            },
            mounted: function () {
                if (typeof window.additionalRegisterFields !== 'undefined') {
                    this.additionalRegisterFields = window.additionalRegisterFields;
                }

                if (typeof window.additionalInstructorsFields !== 'undefined') {
                    this.additionalInstructorsFields = window.additionalInstructorsFields;
                }

                if (typeof window.profileDefaultFieldsForRegister !== 'undefined') {
                    this.profileDefaultFieldsForRegister = window.profileDefaultFieldsForRegister;
                }

                if (typeof stm_lms_sign_in !== 'undefined') {
                    this.object = stm_lms_sign_in;
                }
            },
            methods: {
                formSubmit: function () {
                    let vm = this;

                    if (vm.verify) {
                        vm.verification();
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
                        'phone': vm.phone
                    };

                    vm.$http.post(url, data).then(
                        function (response) {
                            vm.status = response.data['status'];
                            vm.message = response.data['message'];
                            vm.loading = false;

                            setTimeout(function () {
                                vm.status = '';
                                vm.message = '';
                            }, 1000);

                            if (vm.status === 'success') {
                                vm.verify = true;
                            }
                        }
                    );
                },
                verification: function () {
                    let vm = this,
                        url = stm_lms_ajaxurl + '?action=' + vm.object.actions.verification + '&_ajax_nonce=' + vm.object.nonce;

                    vm.loading = true;
                    vm.message = '';

                    let data = {
                        'code': vm.code
                    };

                    vm.$http.post(url, data).then(
                        function (response) {
                            console.log(response);
                            vm.status = response.data['status'];
                            vm.message = response.data['message'];
                            vm.loading = false;
                        }
                    );
                }
            }
        });
    });
});