
export default {
  template: `
    <div>
      <img v-if="row.unset.media_url" :src="row.unset.media_url"/>
      
      <a v-if="!row.unset.media_url" type="submit" class="bookit-button" @click="addMedia()">
        {{ translations.add_icon }}
      </a>
      <a v-if="row.unset.media_url" type="submit" class="bookit-button" @click="removeMedia()">
        {{ translations.remove_icon }}
      </a>
      <a v-if="row.unset.media_url" type="submit" class="bookit-button" @click="addMedia()">
        {{ translations.replace_icon }}
      </a>
    </div>
  `,
  data: () => ({
    media_modal: '',
    translations: bookit_window.translations,
  }),
  props: {
    row: {
      type: Object,
      required: true
    },
    object_key: {
      type: String,
      required: true
    }
  },
  mounted() {
  },
  methods: {
    addMedia() {
      this.media_modal = wp.media({
        frame: 'select',
        multiple: false,
        editing: true,
      });

      this.media_modal.on('select', function (value) {
        let attachment            = this.media_modal.state().get('selection').first().toJSON();
        this.row.unset.media_url  = attachment.url;
        this.row[this.object_key] = attachment.id;
      }, this);

      this.media_modal.open();
    },
    removeMedia() {
      this.row[this.object_key] = this.row.unset.media_url = this.media_url = null;
    },
  }
}