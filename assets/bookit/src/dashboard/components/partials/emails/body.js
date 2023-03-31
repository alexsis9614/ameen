
export default {
  template: `
    <div class="form-group no-margin mt-20" v-if="settings_object.emails[template].enabled">
      <p class="label">{{ title }}</p>
      <small>
        <input type="text" class="small" value="[customer_name]" @click="appendTo(template, 'body', $event.target.value)" size="15" readonly>
        <input type="text" class="small" value="[customer_phone]" @click="appendTo(template, 'body', $event.target.value)" size="15" readonly>
        <input type="text" class="small" value="[customer_email]" @click="appendTo(template, 'body', $event.target.value)" size="15" readonly>
        <input type="text" class="small" value="[service_title]" @click="appendTo(template, 'body', $event.target.value)" size="12" readonly>
        <input type="text" class="small" value="[staff_name]" @click="appendTo(template, 'body', $event.target.value)" size="11" readonly>
        <input type="text" class="small" value="[staff_phone]" @click="appendTo(template, 'body', $event.target.value)" size="12" readonly>
        <input type="text" class="small" value="[start_time]" @click="appendTo(template, 'body', $event.target.value)" size="10" readonly>
        <input type="text" class="small" value="[appointment_day]" @click="appendTo(template, 'body', $event.target.value)" size="17" readonly>
        <input type="text" class="small" value="[payment_method]" @click="appendTo(template, 'body', $event.target.value)" size="17" readonly>
        <input type="text" class="small" value="[payment_status]" @click="appendTo(template, 'body', $event.target.value)" size="15" readonly>
        <input type="text" class="small" value="[total]" @click="appendTo(template, 'body', $event.target.value)" size="5" readonly>
        <input type="text" class="small" value="[status]" @click="appendTo(template, 'body', $event.target.value)" size="6" readonly>
        <input v-if="template =='appointment_deleted_staff' || template == 'appointment_deleted_customer'" type="text" 
               class="small" value="[reason]" @click="appendTo(template, 'body', $event.target.value)" size="6" readonly>
      </small>
      <textarea v-model="settings_object.emails[template].body" rows="12" :disabled="!settings_object.emails[template].enabled"></textarea>
    </div>
  `,
  props: {
    settings_object: {
      type: Object,
      required: true
    },
    template: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    }
  },
  methods: {
    appendTo(settings, object, value) {
      this.settings_object.emails[settings][object] += value;
    }
  }
}