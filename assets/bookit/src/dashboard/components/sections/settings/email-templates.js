import Tabs from '@dashboard-partials/tabs';
import Tab from '@dashboard-partials/tab';
import EmailSwitcher from '@dashboard-partials/emails/switcher';
import EmailRecipients from '@dashboard-partials/emails/recipients';
import EmailSubject from '@dashboard-partials/emails/subject';
import EmailBody from '@dashboard-partials/emails/body';

export default {
  template: `
    <tabs class="vertical email-templates-tabs">
    
      <tab :name="translations.new_appointment" :selected="true">
        <div class="setting-row no-border">
          <p class="title">{{ translations.new_appointment }}</p>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_created_customer" :title="translations.customer_notification"></email-switcher>
            <email-subject :settings_object="settings_object" template="appointment_created_customer" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_created_customer" :title="translations.message_body"></email-body>
          </div>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_created_admin" :title="translations.admin_notification"></email-switcher>
            <email-recipients :settings_object="settings_object" template="appointment_created_admin" :title="translations.recipients"></email-recipients>
            <email-subject :settings_object="settings_object" template="appointment_created_admin" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_created_admin" :title="translations.message_body"></email-body>
          </div>
        </div>
      </tab>
      <tab :name="translations.appointment_updated">
        <div class="setting-row no-border">
          <p class="title">{{ translations.appointment_updated }}</p>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_updated_customer" :title="translations.customer_notification"></email-switcher>
            <email-subject :settings_object="settings_object" template="appointment_updated_customer" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_updated_customer" :title="translations.message_body"></email-body>
          </div>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_updated_admin" :title="translations.admin_notification"></email-switcher>
            <email-recipients :settings_object="settings_object" template="appointment_updated_admin" :title="translations.recipients"></email-recipients>
            <email-subject :settings_object="settings_object" template="appointment_updated_admin" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_updated_admin" :title="translations.message_body"></email-body>
          </div>
        </div>
      </tab>
      <tab :name="translations.payment_complete">
        <div class="setting-row no-border">
          <p class="title">{{ translations.payment_complete }}</p>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="payment_complete_customer" :title="translations.customer_notification"></email-switcher>
            <email-subject :settings_object="settings_object" template="payment_complete_customer" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="payment_complete_customer" :title="translations.message_body"></email-body>
          </div>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="payment_complete_admin" :title="translations.admin_notification"></email-switcher>
            <email-recipients :settings_object="settings_object" template="payment_complete_admin" :title="translations.recipients"></email-recipients>
            <email-subject :settings_object="settings_object" template="payment_complete_admin" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="payment_complete_admin" :title="translations.message_body"></email-body>
          </div>
        </div>
      </tab>
      <tab :name="translations.appointment_status_change">
        <div class="setting-row no-border">
          <p class="title">{{ translations.appointment_status_change }}</p>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_status_changed" :title="translations.customer_notification"></email-switcher>
            <email-subject :settings_object="settings_object" template="appointment_status_changed" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_status_changed" :title="translations.message_body"></email-body>
          </div>
          <div class="email-card">
            <email-switcher :settings_object="settings_object" template="appointment_status_changed_admin" :title="translations.admin_notification"></email-switcher>
            <email-recipients :settings_object="settings_object" template="appointment_status_changed_admin" :title="translations.recipients"></email-recipients>
            <email-subject :settings_object="settings_object" template="appointment_status_changed_admin" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_status_changed_admin" :title="translations.message_body"></email-body>
          </div>
        </div>
      </tab>
      <tab :name="translations.delete_appointment">
        <div class="setting-row no-border">
          <p class="title">{{ translations.delete_appointment }}</p>
          <div class="email-card">
            <div class="form-group">
              <label class="big-label">{{ translations.customer_notification }}</label>
            </div>
            <email-subject :settings_object="settings_object" template="appointment_deleted_customer" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_deleted_customer" :title="translations.message_body"></email-body>
          </div>
          <div class="email-card">
            <div class="form-group">
              <label class="big-label">{{ translations.staff_notification }}</label>
            </div>
            <email-subject :settings_object="settings_object" template="appointment_deleted_staff" :title="translations.subject"></email-subject>
            <email-body :settings_object="settings_object" template="appointment_deleted_staff" :title="translations.message_body"></email-body>
          </div>
        </div>
      </tab>
    </tabs>
  `,
  components: {
    tabs: Tabs,
    tab: Tab,
    'email-switcher': EmailSwitcher,
    'email-recipients': EmailRecipients,
    'email-subject': EmailSubject,
    'email-body': EmailBody
  },
  data: () => ({
    translations: bookit_window.translations,
  }),
  props: {
    settings_object: {
      type: Object,
      required: true
    }
  },
  methods: {
    appendTo(settings, object, value) {
      this.settings_object.emails[settings][object] += value;
    }
  }
}