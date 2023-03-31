export default {
  template: `
    <div class="search-customer">
      <input type="text" name="customer" v-model="search" @input="onChange"  @keyup.down="onArrowDown" @keyup.up="onArrowUp" @keydown.enter.prevent="onEnter" @focus="cleanError" :placeholder="placeholder" :class="['customer', {error:error}]" autocomplete="off"/>
      <span class="search-icon"></span>
      
      <div class="autocomplete">
        <ul v-show="isOpen" class="autocomplete-list">
          <li ref="customerBar"  v-for="(item, i) in results" :key="i"
              @click="setResult(item)"
              class="autocomplete-list-item"
              :class="{ 'is-active': i === arrowCounter }">
            {{ item.full_name }}
          </li>
        </ul>
      </div>
      <span class="error-tip" v-if="error">{{ error }}</span>
    </div>
  `,
  name: 'customerAutocomplete',
  data: () => ({
    search: '',
    results: [],
    isOpen: false,
    arrowCounter: -1,
  }),
  props: {
    placeholder: {
      type: String,
    },
    customer_id: {
      type: String,
    },
    options: {
      type: Array,
      required:true
    },
    error:{
      type: String,
      immediate: true,
    },
  },
  destroyed() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  created () {
    this.results = this.options;
    if (this.customer_id) {
      var current_item =this.options.filter(customer => customer.id.indexOf(this.customer_id) > -1);
      this.search = current_item[0].full_name;
    }
  },
  methods: {
    cleanError(){
      this.$store.commit('setErrors', {});
    },
    handleClickOutside(evt) {
      if (!this.$el.contains(evt.target)) {
        this.isOpen = false;
        this.arrowCounter = 0;
      }
    },
    onChange(){
      this.isOpen = true;
      this.filterResults();
      this.cleanError();

      if (this.results.length == 1) {
        this.setResult(this.results[0]);
      }else{
        this.setResult(this.search);
      }
    },
    filterResults() {
      this.results = this.options.filter(item =>
          item.full_name.toLowerCase().indexOf(this.search.toLowerCase()) > -1
          || item.full_name.toLowerCase().indexOf(this.search.toLowerCase()) > -1);
    },

    setResult(result) {
      let customerId, customerName = '';

      if (result instanceof Object == false || (result instanceof Object && Object.keys(result).includes('full_name') == false)){
        customerName = this.search;
      }
      if (result instanceof Object && Object.keys(result).includes('id')){
        this.search = result.full_name;
        this.isOpen = false;
        this.arrowCounter = -1;
        this.$emit('setChoosenValue', result);
      }
    },
    onArrowDown() {
      this.isOpen = true;
      if (this.arrowCounter < this.results.length) {
        this.arrowCounter = this.arrowCounter + 1;
      }
      var inlineCenter = { behavior: 'smooth', block: 'center', inline: 'start' };
      this.$refs['customerBar'][this.arrowCounter].scrollIntoView(inlineCenter);
    },
    onArrowUp() {
      if (this.arrowCounter > 0) {
        this.arrowCounter = this.arrowCounter - 1;
      }
      var inlineCenter = { behavior: 'smooth', block: 'center', inline: 'start' };
      this.$refs['customerBar'][this.arrowCounter].scrollIntoView(inlineCenter);
    },
    onEnter() {
      this.setResult(this.results[this.arrowCounter]);
    },
  }
}