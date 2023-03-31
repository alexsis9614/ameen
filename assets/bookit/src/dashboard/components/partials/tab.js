
export default {
  template: `
    <div :class="div_class ? div_class : ''" v-show="isActive"><slot></slot></div>
  `,
  props: {
    name: {
      required: true
    },
    div_class: {
      type: String,
      required:false
    },
    selected: {
      default: false
    },
    is_main_tab: {
      default: false
    },
    tab_rows_status: {
      default: false
    },
    disabled: {
      required: false,
      default: false,
    }
  },
  data: () => ({
    isActive: false
  }),
  computed: {
    href() {
      return '#' + this.name.toLowerCase().replace(/ /g, '-');
    }
  },
  mounted() {
    var anchor = window.location.hash.substr(1);
    let main_tabs = [];
    this.$parent.$data.tabs.forEach(tab => {
      if( tab.is_main_tab){
        main_tabs.push(tab.name.toLowerCase().replace(/ /g, '-'));
      }
    })
    if(anchor && this.name.toLowerCase().replace(/ /g, '-') == anchor && main_tabs.includes(anchor)){
        this.isActive = true;
    }
    if (!anchor || !main_tabs.includes(anchor)){
      this.isActive = this.selected;
    }

    if ( this.isActive ){
      this.$store.commit('setActiveTab', this);
    }
  }
}