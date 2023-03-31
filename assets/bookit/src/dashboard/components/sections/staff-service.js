
export default {
  template: `
    <div class="bookit-row">
      <div class="col-2 staff-services">
        <label class="text-capitalize staff-services-label">
          <input type="checkbox" :value="item.id" :checked="check" @change="handleChange($event)">
          <span>{{ service.title }}</span>
        </label>
      </div>
      <div class="col-3">
        <input type="number" step="0.01" :value="parseFloat(item.price).toFixed(2)" :disabled="!check" placeholder="Price" @input="handleInput($event)">
      </div>
      <div class="col-3"></div>
    </div>
  `,
  data: () => ({
    check: false,
    item: {}
  }),
  props: {
    service: {
      type: Object,
      required: true
    },
    isChecked: {
      type: Boolean,
      required: true
    }
  },
  created() {
    this.item = this.service;
    this.check = this.isChecked;
  },
  methods: {
    handleChange( event ) {
      this.check = event.target.checked;
      this.$emit('emitStaffServices', this.check, this.item);
    },
    handleInput( event ) {
      this.item.price = event.target.value;
      this.$emit('emitStaffServices', this.check, this.item);
    },
  }
}