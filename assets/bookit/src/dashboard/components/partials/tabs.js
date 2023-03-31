
export default {
  template: `
    <div>
      <ul class="bookit-tabs">
        <li v-for="tab in tabs" :class="[{ 'active': tab.isActive }, { 'disabled': tab.disabled }]">
            <a :href="tab.href" @click="selectTab(tab)">{{ tab.name }}</a>
        </li>
      </ul>
      <div class="bookit-tabs-details">
          <slot></slot>
      </div>
    </div>
  `,
  data: () => ({
    tabs: []
  }),
  created() {
    this.tabs = this.$children;
  },
  methods: {
    selectTab( selectedTab ) {
      this.tabs.forEach( tab => {
        tab.isActive = ( tab.name == selectedTab.name );
        if ( tab.isActive ) {
          this.$store.commit('setActiveTab', tab);
          /** tabs rows status used in appointment **/
          if(tab.tab_rows_status){
            this.$store.commit('setRows', tab.$children[0].displayedRows);
          }
        }
      });
    }
  }
}