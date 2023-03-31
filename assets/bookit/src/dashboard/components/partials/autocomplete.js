export default {
  template: `
    <div class="form-group small">
      <label>{{ label }}</label>
      <input :class="{error:error}"  type="text" @focus="cleanError" v-model="search" @input="onChange"  @keyup.down="onArrowDown" @keyup.up="onArrowUp" @keydown.enter.prevent="onEnter"/>
      <span class="error-tip" v-if="error">{{ error }}</span>
      <div class="autocomplete">
        <ul v-show="isOpen" class="autocomplete-list">
          <li ref="bar"  v-for="(item, i) in results" :key="i" 
              @click="setResult(item)" 
              class="autocomplete-list-item" 
              :class="{ 'is-active': i === arrowCounter }">
            {{ item.alias }}
          </li>
        </ul>
      </div>
    </div>
  `,
  name: 'autocomplete',
  data: () => ({
    search: '',
    results: [],
    isOpen: false,
    arrowCounter: -1,
  }),
  props: {
    label: {
      type: String,
      required: true
    },
    current_value: {
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
    if (this.current_value) {
      var current_item =this.options.filter(item => item.value.toLowerCase().indexOf(this.current_value.toLowerCase()) > -1);
      this.search = current_item[0].alias;
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
        this.setResult(this.results[this.arrowCounter]);
      }else{
        this.setResult(this.search);
      }

    },

    filterResults() {
      this.results = this.options.filter(item =>
          item.alias.toLowerCase().indexOf(this.search.toLowerCase()) > -1
          || item.value.toLowerCase().indexOf(this.search.toLowerCase()) > -1);
    },

    setResult(result) {
      let value, symbol = '';

      if (result instanceof Object == false || (result instanceof Object && Object.keys(result).includes('alias') == false)){
        value = this.search;
      }
      if (result instanceof Object && Object.keys(result).includes('alias')){
        value  = result.value;
        symbol = result.symbol;
        this.search = result.alias;
        this.isOpen = false;
        this.arrowCounter = -1;
      }

      this.$emit('setChoosenValue', value, symbol);
    },
    onArrowDown() {
      if (this.arrowCounter < this.results.length) {
        this.arrowCounter = this.arrowCounter + 1;
      }
      var inlineCenter = { behavior: 'smooth', block: 'center', inline: 'start' };
      this.$refs['bar'][this.arrowCounter].scrollIntoView(inlineCenter);
    },
    onArrowUp() {
      if (this.arrowCounter > 0) {
        this.arrowCounter = this.arrowCounter - 1;
      }
      var inlineCenter = { behavior: 'smooth', block: 'center', inline: 'start' };
      this.$refs['bar'][this.arrowCounter].scrollIntoView(inlineCenter);
    },
    onEnter() {
      this.setResult(this.results[this.arrowCounter]);
    },
  }
}