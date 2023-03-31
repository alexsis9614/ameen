import addon_feature from '@dashboard-partials/addon-feature';

export default {
    name: 'googleCalendar',
    template: `
    <div class="google-calendar-settings">
      <div v-if="loading" class="loader">
        <div class="loading"><div v-for="n in 9"></div></div>
      </div>
      <!-- IF ADDON NOT INSTALLED -->
      <div v-if="!addon.installed">
        <addon_feature :freemius="freemius" :addon="addon" addonSlug="google-calendar" :addonLink="addon.link"></addon_feature>
      </div>
      <!-- IF ADDON NOT INSTALLED END-->
      
      <!-- IF ADDON INSTALLED BUT NO LICENSE-->
      <div v-if="addon.installed && !addon.isCanUse">
        <div class="addon-feature activation">
          <span class="addon-icon">
            <i class="google-calendar"></i>
          </span>
          <h2 class="title">{{ addon.title }}</h2>
          <p class="activation-link" v-html="addon.activationLink"></p>
        </div>
      </div>
      <!-- IF ADDON INSALLED BUT NO LICENSE END-->
      
      <div :class="[{'disabled': ( !addon.installed || ( addon.installed && ( !addon.isCanUse || !addon.active) ) )}, {'not-active': !addon.active && addon.installed}]">
      
          <div class="setting-row pb-10">
              <div class="form-group">
                <div class="switcher">
                  <div class="bookit-switch">
                    <input type="checkbox" v-model="settings.enabled" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                    <label></label>
                  </div>
                  <span>{{ translations.enabled }}</span>
                </div>
              </div>
          </div>
          
          <div v-if="( settings && settings.enabled ) || ( !settings && addon.installed && addon.isCanUse )">
              <div class="setting-row no-border pt-30 pb-10">
                <p class="title">{{ translations.main_settings }}</p>
                <div class="form-group small">
                  <div class="help-label-block">
                    <label>{{ translations.client_id }}</label>
                    <span class="round-link-icon" @mouseover="addon.installed && addon.isCanUse ? showHelp.client_id = true : null" @mouseleave="showHelp.client_id = false">!</span>
                    <div class="help" v-if="showHelp.client_id">
                      <div class="help-tip" v-html="translations.client_id_help">
                      </div>
                    </div>
                  </div>
                  <input type="text" v-model="settings.client_id" :class="{error:errors.client_id}" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                  <span class="error-tip" v-if="errors.client_id">{{ errors.client_id }}</span>
                </div>
                <div class="form-group small">
                  <div class="help-label-block">
                    <label>{{ translations.client_secret }}</label>
                    <span class="round-link-icon" @mouseover="addon.installed && addon.isCanUse ? showHelp.client_secret = true : null" @mouseleave="showHelp.client_secret = false">!</span>
                    <div class="help" v-if="showHelp.client_secret" >
                      <div class="help-tip" v-html="translations.client_secret_help">
                      </div>
                    </div>
                  </div>
                  <input type="text" v-model="settings.client_secret" :class="{error:errors.client_secret}" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                  <span class="error-tip" v-if="errors.client_secret">{{ errors.client_secret }}</span>
                </div>
              </div>
            
              <div class="setting-row column pt-10">
                <div class="form-group medium">
                  <label class="full">{{ translations.redirect_uri}}</label>
                  <div class="code full">
                    <code>{{ settings.redirect_url }}</code>
                    <button class="button-copy" type="button" @click="copyURL()">
                      <i class="copy-icon"></i>{{ translations.copy }}
                    </button>
                    <div class="help" v-if="linkCopy">
                      <div class="help-tip" v-html="translations.url_copied">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group medium mt-10 no-margin">
                  <div class="help-label-block">
                    <div class="switcher help-label">
                      <div class="bookit-switch">
                        <input type="checkbox" v-model="settings.send_pending" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                        <label></label>
                      </div>
                      <span>{{ translations.send_pending }}</span>
                    </div>
                    <span class="round-link-icon" @mouseover="addon.installed && addon.isCanUse ? showHelp.send_pending = true : null" @mouseleave="showHelp.send_pending = false">!</span>
                    <div class="help" v-if="showHelp.send_pending">
                      <div class="help-tip" v-html="translations.send_pending_help">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group medium mt-10 no-margin">
                  <div class="help-label-block">
                    <div class="switcher help-label">
                      <div class="bookit-switch">
                        <input type="checkbox" v-model="settings.rm_busy_slots" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                        <label></label>
                      </div>
                      <span>{{ translations.rm_busy_slots_title }}</span>
                    </div>
                    <span class="round-link-icon" @mouseover="addon.installed && addon.isCanUse ? showHelp.rm_busy_slots = true : null" @mouseleave="showHelp.rm_busy_slots = false" >!</span>
                    <div class="help" v-if="showHelp.rm_busy_slots">
                      <div class="help-tip" v-html="translations.rm_busy_slots_help">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group medium mt-10 no-margin">
                  <div class="help-label-block">
                    <div class="switcher help-label">
                      <div class="bookit-switch">
                        <input type="checkbox" v-model="settings.customer_as_attendees" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                        <label></label>
                      </div>
                      <span>{{ translations.customer_as_attendees }}</span>
                    </div>
                    <span class="round-link-icon" @mouseover="addon.installed && addon.isCanUse ? showHelp.customer_as_attendees = true : null" @mouseleave="showHelp.customer_as_attendees = false">!</span>
                    <div class="help" v-if="showHelp.customer_as_attendees">
                      <div class="help-tip" v-html="translations.customer_attendees_help">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="setting-row pb-20">
                <p class="title">{{ translations.template }}</p>
                
                <div class="event-template">
                  <!-- Template Title -->
                  <div class="form-group no-margin three-quarters">
                      <p class="label">{{ translations.title }}</p>
                      <small>
                        <input type="text" class="small" value="[appointment_id]" @click="appendTo('title', $event.target.value)" size="15" readonly>
                        <input type="text" class="small" value="[service_title]" @click="appendTo('title', $event.target.value)" size="12" readonly>
                        <input type="text" class="small" value="[customer_name]" @click="appendTo('title', $event.target.value)" size="15" readonly>
                      </small>
                      <input type="text" v-model="settings.template.title" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                    </div>
                  <!-- Template Title | end-->
                  
                  <!-- Template Body-->
                  <div class="form-group no-margin mt-20 three-quarters">
                      <p class="label">{{ translations.body }}</p>
                      <small>
                        <input type="text" class="small" value="[customer_name]" @click="appendTo('body', $event.target.value)" size="15" readonly>
                        <input type="text" class="small" value="[customer_phone]" @click="appendTo('body', $event.target.value)" size="15" readonly>
                        <input type="text" class="small" value="[customer_email]" @click="appendTo('body', $event.target.value)" size="15" readonly>
                        <input type="text" class="small" value="[service_title]" @click="appendTo('body', $event.target.value)" size="12" readonly>
                        <input type="text" class="small" value="[start_time]" @click="appendTo('body', $event.target.value)" size="10" readonly>
                        <input type="text" class="small" value="[payment_method]" @click="appendTo('body', $event.target.value)" size="17" readonly>
                        <input type="text" class="small" value="[payment_status]" @click="appendTo('body', $event.target.value)" size="15" readonly>
                        <input type="text" class="small" value="[total]" @click="appendTo('body', $event.target.value)" size="5" readonly>
                        <input type="text" class="small" value="[status]" @click="appendTo('body', $event.target.value)" size="6" readonly>
                      </small>
                      <textarea v-model="settings.template.body" rows="12" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )"></textarea>
                    </div>
                  <!-- Template Body | end-->
                </div>
              </div>
              <div class="setting-row">
                <p class="title no-padding">{{ translations.export_appointments }}</p>
                <p class="description">{{ translations.export_appointments_help }}</p>
                <div class="form-group no-margin">
                  <button type="button" class="bookit-button light" @click="exportAppointments()" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
                    {{ translations.export }}
                  </button>
                </div>
              </div>
          </div>
          <button  type="button" @click="save()" class="bookit-button" :disabled="!addon.installed || ( addon.installed && !addon.isCanUse )">
            {{ translations.save }} {{ translations.settings }}
          </button>
      </div>
      
    </div>
  `,
    components: {
        addon_feature,
    },
    data: () => ({
        linkCopy: false,
        loading: false,
        translations: bookit_window.translations,
        settings: {},
        showHelp: {
            'client_id': false,
            'client_secret': false,
            'synchronization_type': false,
            'send_pending': false,
            'rm_busy_slots': false,
            'customer_as_attendees': false,
            'export_appointments': false,
        },
    }),
    computed: {
        errors() {
            return this.$store.getters.getErrors;
        },
    },
    created() {
        this.settings = this.setCorrectBooleanInObject({...this.addon.settings});
    },
    props: {
        addon: {
            type: Object,
            required: true
        },
        freemius: {
            type: Object,
            required: true
        }
    },
    methods: {
        appendTo( object, value) {
            if ( !this.addon.installed || ( this.addon.installed && !this.addon.isCanUse ) ) {
                return;
            }
            this.settings.template[object] += value;
        },
        copyURL() {
            var input = document.body.appendChild(document.createElement("input"));
            input.value = this.settings.redirect_url;
            input.select();
            document.execCommand('copy');
            input.parentNode.removeChild(input);

            this.linkCopy = true;
            setTimeout(() => {
                this.linkCopy = false;
            }, 1000);
        },
        save() {
            if ( !this.addon.installed || ( this.addon.installed && !this.addon.isCanUse ) ) {
                return;
            }
            this.loading         = true;
            this.settings.nonce  = this.addon.nonce;

            this.axios.post(`${bookit_window.ajax_url}?action=bookit_google_calendar_save_settings`, this.generateFormData(this.settings), this.getPostHeaders()).then((res) => {
                let response = res.data;
                if (response.data.errors && Object.keys(response.data.errors).length > 0){
                    this.$store.commit('setErrors', response.data.errors);
                }
                this.$toasted.show(response.data.message, {
                    type: (response.success) ? 'success' : 'error'
                });
                this.loading = false;
            });
        },
        exportAppointments() {
            if ( !this.addon.installed || ( this.addon.installed && !this.addon.isCanUse ) ) {
                return;
            }
            this.loading         = true;
            this.settings.nonce  = this.addon.nonce;
            this.settings.start  = this.moment().set({ hour: new Date().getHours(), minute: new Date().getMinutes() }).unix();

            this.axios.post(`${bookit_window.ajax_url}?action=bookit_google_calendar_export_appointments`, this.generateFormData(this.settings), this.getPostHeaders()).then((res) => {
                let response = res.data;

                this.$toasted.show(response.data.message, {
                    className: 'google-export',
                    type: (response.success) ? 'success' : 'error',
                    duration: false,
                    action : {
                        class:'close-toast',
                        onClick: (e, toastObject) => {
                            toastObject.goAway(0);
                        }
                    }
                });
                this.loading  = false;
            });
        }
    }
}