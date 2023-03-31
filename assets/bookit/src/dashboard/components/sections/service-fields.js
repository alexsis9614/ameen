import WpMedia from '@dashboard-partials/wp-media';

export default {
  template: `
    <div class="bookit-modal-body margin">
      <div class="bookit-row">
        <div class="form-group">
          <label for="title">{{ translations.title }}</label>
          <input type="text" id="title" v-model="row.title" :class="{error:errors.title}">
          <span class="error-tip" v-if="errors.title">{{ errors.title }}</span>
        </div>
        <div class="form-group">
          <label for="category">{{ translations.category }}</label>
          <select id="category" v-model="row.category_id" :class="{error:errors.category_id}">
            <option :value="null">--{{ translations.no_category }}--</option>
            <option v-for="category in categories" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <span class="error-tip" v-if="errors.category_id">{{ errors.category_id }}</span>
        </div>
      </div>
      <div class="bookit-row">
        <div class="form-group">
          <label for="duration">{{ translations.duration }}</label>
          <select id="duration" v-model="row.duration" @change="changeDuration($event)" :class="{error:errors.duration}">
            <option v-for="duration in getDurationList()" :value="duration.value">
              {{ duration.label }}
            </option>
          </select>
          <span class="error-tip" v-if="errors.duration">{{ errors.duration }}</span>
          <span class="info-tip" v-if="showDurationInfo" > {{ translations.confirm_service_edit_duration }}</span>
        </div>
        <div class="form-group">
          <label for="price">{{ translations.price }}</label>
          <input type="text" id="price" step="0.01" v-model="row.price" :class="{error:errors.price}">
          <span class="error-tip" v-if="errors.price">{{ errors.price }}</span>
        </div>
      </div>
      <div class="bookit-row">
        <div class="form-group wp-media">
          <label>{{ translations.icon }}</label>
          <wp-media :row="row" object_key="icon_id"></wp-media>
        </div>
      </div>
    </div>
  `,
  components: {
    'wp-media': WpMedia
  },
  data: () => ({
    translations: bookit_window.translations,
    showDurationInfo: false
  }),
  props: {
    row: {
      type: Object,
      required: true
    },
  },
  computed: {
    categories () {
      return this.$store.getters.getCategories;
    },
    errors() {
      return this.$store.getters.getErrors;
    },
  },
  created() {
    if ( Object.keys(this.row).length === 0 ) {
      this.row.category_id      = null;
      this.row.duration         = 900;
      this.row.price            = '0.00';
      this.row.icon_id          = null;
      this.row.unset            = { categories: this.categories };
      this.row.unset.duration   = this.secondsToFormatted( this.row.duration, true );
      this.row.unset.media_url  = null;

      this.$store.commit('setEditRow', this.row);
    }
  },
  methods: {
    changeDuration( event ) {
      this.row.unset.duration = this.secondsToFormatted( event.target.value, true );
      var row = this.$store.getters.getEditRow;
      if ( row.id > 0 ) {
        this.showDurationInfo = true;
      }
    }
  }
}