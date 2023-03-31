export default {
    name: 'paypal',
    template: `
      <div class="woocommerce">
      <div v-if="loading" class="loader">
        <div class="loading"><div v-for="n in 9"></div></div>
      </div>
      <div :class="['setting-row pt-30 no-border pb-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) )}]">
        <div :class="['form-group small no-margin', {disabled: ( !addon.installed || !addon.isCanUse ) }]">
          <span class="label">{{ paymentName }}</span>
        </div>
        <div :class="['form-group small no-margin', {disabled: ( !addon.installed || !addon.isCanUse ) }]">
          <div class="switcher">
            <div class="bookit-switch">
              <input type="checkbox" @change="checkWoocommerce($event)" v-model="settings_object.payments.woocommerce.enabled" :disabled="!addon.installed">
              <label></label>
            </div>
          </div>
          <span class="label for-switcher" v-html=" settings_object.payments.woocommerce.enabled ? translations.enabled : translations.disabled"></span>
        </div>
        </div>
        <div v-if="settings_object.payments.woocommerce.enabled || ( !addon.installed || !addon.isCanUse ) " :class="['setting-row pt-30 no-border pb-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div class="form-group small">
            <label>{{ translations.woocommerce_product }}</label>
            <select v-model="settings_object.payments.woocommerce.product_id" required :disabled="!addon.installed">
              <option v-for="product in payment.woocommerce_products" :value="product.id">{{ product.title }}</option>
            </select>
          </div>
          <div class="form-group small">
            <label>{{ translations.woocommerce_title }}</label>
            <input type="text" v-model="settings_object.payments.woocommerce.custom_title" :disabled="!addon.installed">
          </div>
        </div>
        <div v-if="settings_object.payments.woocommerce.enabled || ( !addon.installed || !addon.isCanUse ) " :class="['setting-row pt-10', {'not-active': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) ) }]">
          <div class="form-group medium">
            <label>{{ translations.woocommerce_icon }}</label>
            
            <div class="file-load">
              
              <div class="icon small" v-if="woocommerceIcon">
                <img :src="woocommerceIcon"/>
                <span class="delete-icon" @click="removeIcon"></span>
              </div>
              
              <div v-if="!settings_object.payments.woocommerce.custom_icon">
                <div class="file-button" onclick="document.getElementById('load-woocommmerce-icon').click()">
                  <i class="download-icon"></i> {{ translations.choose_icon }}
                </div>

                <input id="load-woocommmerce-icon" type="file" class="import-file" name="file" :ref="'loadWoocommerceIcon'" @change="appendIcon">
                <span @click="loadIcon" class="load-button">{{ translations.save }}</span>
              </div>
              
            </div>
            <span class="error-tip" v-if="errors.woocommerce_icon">{{ errors.woocommerce_icon }}</span>
          </div>
        </div>
      </div>
      
    `,
    data: () => ({
      loading: false,
      translations: bookit_window.translations,
      show_woocommerce_alert: false,
      woocommerceIcon: false,
    }),
    computed: {
      errors() {
        return this.$store.getters.getErrors;
      },
      paymentName() {
            return this.payment.name.replace('comm', function(part, index){
                var partName = part.split('');
                partName[0] = partName[0].toUpperCase();
                partName = partName.join('');
                return index == 0 ? part.toLowerCase() : partName;
            });
        },
    },
    created() {
      this.woocommerceIcon = this.settings_object.payments.woocommerce.custom_icon;
    },
    props: {
        settings_object: {
            type: Object,
            required: true
        },
        payment: {
            type: Object,
            required: true
        },
        addon: {
            type: Object,
            required: true
        },
    },
    methods: {
      checkWoocommerce(event) {
            if ( this.payment.woocommerce_enabled === 'false' && event.target.checked) {
                this.show_woocommerce_alert = true;
            }else {
                this.show_woocommerce_alert = false;
            }

        },
      removeIcon() {
        this.loading = true;
        let formData = new FormData();
        formData.append('action', 'bookit_remove_icon');
        formData.append('nonce', bookit_window.nonces.bookit_load_icon);

        this.axios.post(`${bookit_window.ajax_url}`, formData, this.getPostHeaders()).then((res) => {
          let response = res.data;
          if ( response.success ) {
              this.woocommerceIcon  = false;
              this.settings_object.payments.woocommerce.custom_icon = false;
          }
          this.$toasted.show(response.data.message, {
              type: (response.success) ? 'success' : 'error'
          });
          this.loading = false;
        });
      },
      loadIcon() {
        let files = this.$refs['loadWoocommerceIcon'].files;
        if ( ! files.length ) return;

        this.$store.commit('setErrors',{});
        if ( files[0]['type'].split('/')[0] != 'image' ) {
            this.$store.commit('setErrors', {'woocommerce_icon': this.translations.woocommerce_icon_error});
            this.woocommerceIcon = this.settings_object.payments.woocommerce.custom_icon ? this.settings_object.payments.woocommerce.custom_icon: '';
            return;
        }

        this.loading = true;
        let formData = new FormData();
        formData.append('file', files[0]);
        formData.append('action', 'bookit_load_setting_icon');
        formData.append('nonce', bookit_window.nonces.bookit_load_icon);

        this.axios.post(`${bookit_window.ajax_url}`, formData, this.getPostHeaders()).then((res) => {
            let response = res.data;
            if (response.data.errors && Object.keys(response.data.errors).length > 0
                && response.data.errors.hasOwnProperty('icon')){
                this.$store.commit('setErrors', response.data.errors );
            }

            if ( response.success ) {
                this.woocommerceIcon                                  = response.data.icon_url;
                this.settings_object.payments.woocommerce.custom_icon = response.data.icon_url;
            }
            this.$toasted.show(response.data.message, {
                type: (response.success) ? 'success' : 'error'
            });
            this.loading = false;
        });

      },
      appendIcon(event){
        if ( event.target.files.length > 0 ){
            var errors = {};

            if ( event.target.files[0].type.split('/')[0] == 'image' ) {
                var filename = event.target.files[0].name;
                this.woocommerceIcon = window.URL.createObjectURL(event.target.files[0]);
            }else{
                errors.woocommerce_icon = this.translations.woocommerce_icon_error;
            }

            this.$store.commit('setErrors', errors);
        }
      }
    }
}