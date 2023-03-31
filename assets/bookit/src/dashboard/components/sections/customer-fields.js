export default {
  template: `
    <div class="bookit-modal-body margin">
      <div class="bookit-row">
        <div class="form-group col-2">
          <label for="full_name">{{ translations.full_name }}</label>
          <input type="text" v-model="row.full_name" :placeholder="translations.full_name" :class="{error:errors.full_name}">
          <span class="error-tip" v-if="errors.full_name">{{ errors.full_name }}</span>
        </div>
        <div class="form-group col-2">
          <label for="email">{{ translations.email }}</label>
          <input type="text" v-model="row.email" :placeholder="translations.email" :class="{error:errors.email}">
          <span class="error-tip" v-if="errors.email">{{ errors.email }}</span>
        </div>
      </div>
      <div class="bookit-row">
        <div class="form-group col-2">
          <label for="phone">{{ translations.phone }}</label>
          <input type="text" v-model="row.phone" :placeholder="translations.phone" :class="{error:errors.phone}">
          <span class="error-tip" v-if="errors.phone">{{ errors.phone }}</span>
        </div>
        <div class="form-group col-2">
          <label for="phone">{{ translations.wp_user }}</label>
          <select v-model="row.wp_user_id" @change="changeWpUser($event)" >
            <option :value="null">-- {{ translations.no_user }} --</option>
            <option v-for="wp_user in row.unset.wp_users" :value="wp_user.ID">
              {{ wp_user.display_name }}
            </option>
          </select>
        </div>
      </div>
    </div>
  `,
  props: {
    row: {
      type: Object,
      required: true
    },
  },
  data: () => ({
    translations: bookit_window.translations,
  }),
  computed: {
    wp_users () {
      return this.$store.getters.getWpUsers;
    },
    errors() {
      return this.$store.getters.getErrors;
    },
  },
  created() {
    if ( Object.keys(this.row).length === 0 ) {
      this.row.unset = { wp_users: this.wp_users };
      this.$store.commit('setEditRow', this.row);
    }
  },
  methods: {
    changeWpUser( event ) {
      this.row.unset.wp_user = this.row.unset.wp_users.find( (wp_user) => wp_user.ID === event.target.value );
    }
  }
}