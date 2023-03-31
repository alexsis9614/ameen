import CategoryItem from '@dashboard-sections/category-item';

export default {
  template: `
    <div class="bookit-categories-wrapper">
      <div class="bookit-categories">
        <div class="bookit-category" v-for="category in categories">
          <category-item :category="category"></category-item>
        </div>
      </div>
      
      <div class="bookit-category-form">
        <div class="form-group no-bottom-margin">
          <label class="text-uppercase" v-html="translations.add_new_category"></label>
          <div class="input-group" :class="{error:errors.category_name}">
              <input type="text" @blur="clearErrors" v-model="new_category" :placeholder="translations.enter_category_name">
            <div class="input-group-append">
              <button type="submit" class="bookit-button" @click="addCategory" v-html="translations.add"></button>
            </div>
          </div>
          <span class="error-tip-for-input-group" v-if="errors.category_name">{{ errors.category_name }}</span>
        </div>
      </div>
    </div>
  `,
  components: {
    'category-item': CategoryItem
  },
  data: () => ({
    translations: bookit_window.translations,
    new_category: ''
  }),
  computed: {
    categories: {
      get() {
        return this.$store.getters.getCategories;
      },
      set( categories ) {
        let rows = this.$store.getters.getRows;
        rows.map((object) => {
          object.unset = { categories: this.categories };
        });
        this.$store.commit('setRows', rows);
        this.$store.commit('setCategories', categories);
      }
    },
    errors() {
      return this.$store.getters.getErrors;
    },
  },
  methods: {
    addCategory() {
      const data = {
        nonce: bookit_window.nonces.bookit_save_category,
        name: this.new_category
      };

      this.$store.commit('setErrors', {});

      this.axios.post(`${bookit_window.ajax_url}?action=bookit_save_category`, this.generateFormData( data ), this.getPostHeaders()).then((res) => {
        let response = res.data;
        if (response.data.errors && Object.keys(response.data.errors).length > 0){
          this.$store.commit('setErrors', response.data.errors);
        }

        if ( response.success ) {
          this.categories.push( { id: response.data.id, name: this.new_category } );
          this.new_category = '';
        }
      });
    },
    clearErrors(){
      this.$store.commit('setErrors', {});
    },
  }
}