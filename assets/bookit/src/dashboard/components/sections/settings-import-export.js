
export default {
  template: `
    <div class="import-tab">
      <div id="bookit-demo-import">
        <div class="bookit-row-import">
          <div v-show="!demo_importing" class="box-align">
            <div class="bookit-import-export-inline-row">
              <div class="demo-btn-wrapper">
                <div class="demo-demo-icon"><span class="demo-import"></span></div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-import-text">{{ translations.import_demo_content }}</div>
                <div class="demo-import-text-description">{{ translations.import_demo_content_info }}</div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-btn-item">
                  <div class="bookit-export-button">
                    <span @click="importDemo" class="bookit-demo-import-button default">{{ translations.demo_import }}</span>
                  </div>
                  <div class="errors_block" v-if="errors.demo_import" >
                    <span class="error" >{{ errors.demo_import }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-show="!importing" class="import-loading">
            <div class="text-center" v-if="demoImport.step_progress">
              <div class="import-title">{{ translations.importing }} <br /><span>{{ demoImport.step_progress }}</span></div>
            </div>
            <div v-if="demoImport.progress_load" class="progress">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                  :aria-valuenow="demoImport.progress" aria-valuemin="0" aria-valuemax="100"
                  :style="'width: ' + demoImport.progress + '%'">                
              </div>
            </div>
          </div>
        </div>
        
        <div class="bookit-row-import bookit-row-second">
          <div v-show="!importing" class="box-align">
            <div class="bookit-import-export-inline-row demo-second-row">
              <div class="demo-btn-wrapper">
                <div class="demo-demo-icon"><span class="json-import"></span></div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-import-text">{{ translations.import }}</div>
                <div class="demo-import-text-description">{{ translations.import_info }}</div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-btn-item bookit-file-upload">
                  <div class="panel-custom" v-show="uploaded_file">
                    <span class="uploaded-file">{{ uploaded_file }}</span>
                  </div>
                  <div class="panel-custom" v-if="errors.import_json">
                    <span class="error">{{ errors.import_json }}</span>
                  </div>
                  <div class="panel-custom text-center">
                    <span class="import-file-button"  onclick="document.getElementById('bookit-file-button').click()">
                      <i class="paper-clip-icon"></i>
                      <span class="button-text">
                        {{ translations.select_file }}
                      </span>
                    </span>
                    <input id="bookit-file-button" type="file" class="import-file" name="file" :ref="'import_file'" @change="appendFileName">
                    <span @click="importJson" class="bookit-demo-import-button">{{ translations.import_json }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-show="!importing" class="line-break"><hr class="hr-line"/></div>
          <div class="clear"></div>
          <div v-show="!importing" class="box-align">
            <div class="bookit-import-export-inline-row export-row">
              <div class="demo-btn-wrapper">
                <div class="demo-demo-icon"><span class="json-import"></span></div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-import-text">{{ translations.export }}</div>
                <div class="demo-import-text-description">{{ translations.export_info }}</div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-btn-item">
                  <div class="bookit-export-button">
                    <span class="bookit-demo-import-button">
                      <a :href="export_ajax_url"><span>{{ translations.export_json }}</span></a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-show="!demo_importing" class="import-loading">
            <div class="text-center" v-if="demoImport.step_progress">
              <h4 class="import-title"> {{ translations.importing }} <br />{{ demoImport.step_progress }}</h4>
            </div>
            <div v-if="demoImport.progress_load" class="progress">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                  :aria-valuenow="demoImport.progress" aria-valuemin="0" aria-valuemax="100"
                  :style="'width: ' + demoImport.progress + '%'">                 
              </div>
            </div>
          </div>
        </div>
        
        <div class="bookit-row-import">
          <div class="box-align">
            <div class="bookit-import-export-inline-row">
              <div class="demo-btn-wrapper">
                <div class="demo-demo-icon"><span class="excel"></span></div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-import-text">{{ translations.export_appointments }}</div>
                <div class="demo-import-text-description">{{ translations.export_appointments_info }}</div>
              </div>
              <div class="demo-btn-wrapper">
                <div class="demo-btn-item">
                  <div class="bookit-export-button">
                    <a :href="export_excel_ajax_url">
                      <span class="bookit-demo-import-button default button-excel">{{ translations.export_excel }}</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  `,
  data: () => ({
    export_ajax_url: `${bookit_window.ajax_url}?action=bookit_export&nonce=${bookit_window.nonces.bookit_export}`,
    export_excel_ajax_url: `${bookit_window.ajax_url}?action=bookit_export_excel&nonce=${bookit_window.nonces.bookit_export}`,
    wpnonce: bookit_window.nonces.bookit_import,
    importing: false,
    demo_importing: false,
    demoImport: {
      // Custom demo import
      image: {
        file: null
      },
      files: null,
      file: null,
      custom: false,
      noFile: 'No file chosen',

      // Default demo import
      progress_load: false,
      progress: 0,
      step_progress: null,
      step: [],
      info: {
        'settings': 0
      },
      info_progress: [],
      finish: false
    },
    translations: bookit_window.translations,
    uploaded_file: false,
  }),
  computed: {
    errors() {
      return this.$store.getters.getErrors;
    },
  },
  methods: {
    importJson() {
      let files = this.$refs['import_file'].files;
      if ( ! files.length ) return;
      this.$store.commit('setErrors',{});

      if ( files[0]['type'] !== 'text/plain' ) {
        this.$store.commit('setErrors', {'import_json': 'Please Upload valid txt file with json data!'});
        this.uploaded_file = '';
        return;
      }

      let formData = new FormData();
      formData.append('file', files[0]);
      formData.append('action', 'bookit_import');
      formData.append('nonce', this.wpnonce);

      this.axios.post(`${bookit_window.ajax_url}`, formData, this.getPostHeaders()).then((res) => {
        this.uploaded_file = '';

        if (res.data.hasOwnProperty('data') && res.data.data.hasOwnProperty('errors')
            && Object.keys(res.data.data.errors).length > 0){
          this.$store.commit('setErrors', res.data.data.errors);
        }

        if ( res.data.success ) {
          this.importing  = true;
          this.demoImport.files = null;
          this.demoImport.noFile = 'No file chosen';
          this.demoImport.image.file = '';
          this.responseHandler(res.data);
        }
      });
    },
    importDemo() {
      this.demo_importing = true;
      let data = {
        'action': 'bookit_demo_import_apply',
        'nonce': this.wpnonce
      };
      this.$store.commit('setErrors',{});

      this.axios.post(`${bookit_window.ajax_url}`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        if ( res.data.success ) {
          this.responseHandler(res.data);
        }

        if (res.data.hasOwnProperty('data') && res.data.data.hasOwnProperty('errors')
            && Object.keys(res.data.data.errors).length > 0){
          this.demo_importing = false;
          this.$store.commit('setErrors', res.data.data.errors);
        }
      });
    },
    progressImport() {
      let data = {
        'action': 'bookit_demo_import_run',
        'step': this.demoImport.step_progress,
        'key': this.demoImport.info_progress[this.demoImport.step_progress],
        'nonce': this.wpnonce
      };

      this.axios.post(`${bookit_window.ajax_url}`, this.generateFormData(data), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if ( response.success ) {
          if ( this.demoImport.info[this.demoImport.step_progress] > this.demoImport.info_progress[this.demoImport.step_progress] ) {
            this.demoImport.info_progress[this.demoImport.step_progress] = response.key;
            this.demoImport.progress = Math.ceil((response.key / this.demoImport.info[this.demoImport.step_progress]) * 100);
          }

          if ( this.demoImport.info[this.demoImport.step_progress] === this.demoImport.info_progress[this.demoImport.step_progress] ) {
            this.demoImport.step_progress = this.nextKey(this.demoImport.info, this.demoImport.step_progress);
            this.demoImport.progress = 0;
            if ( this.demoImport.step_progress ) {
              this.$toasted.show(this.demoImport.step_progress + ' <br>' + this.translations.import_started + '!', {
                type: 'success'
              });
            }
          }

          if ( this.demoImport.step_progress != null && response.success ) {
            this.progressImport();
          } else {
            this.demoImport.finish        = true;
            this.demoImport.progress_load = false;
            this.importing                = false;
            this.demo_importing           = false;

            this.$toasted.show(this.translations.import_completed, {
              type: 'success'
            });
          }
        }
      });
    },
    responseHandler(response) {
      this.demoImport.info    = response.info;
      this.demoImport.finish  = false;
      for ( let index in this.demoImport.info ) {
        this.demoImport.info_progress[index] = 0;
      }
      this.demoImport.custom  = true;
      this.importDemos();
    },
    importDemos() {
      this.demoImport.progress_load = true;
      this.demoImport.step          = Object.keys(this.demoImport.info);
      this.demoImport.step_progress = this.demoImport.step[0];
      this.progressImport();
    },
    nextKey(info, key) {
      let keys = Object.keys(info), i = keys.indexOf(key);
      i++;
      return ( typeof keys[i] !== 'undefined' ) ? keys[i] : null;
    },
    appendFileName(event){
      if (event.target.files.length > 0){
        var filename = event.target.files[0].name;
        this.uploaded_file = filename;
        this.$store.commit('setErrors', {});
      }
    }
  }
}