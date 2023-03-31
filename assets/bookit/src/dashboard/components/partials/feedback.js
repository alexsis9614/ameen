
export default {
  template: `
  <transition name="bookit-modal" v-if="!has_feedback">
    <div id="bookit-feedback-modal" class="bookit-feedback-modal" @click="close($event)">
      <div class="feedback-modal-content" @click.stop>
        <span class="feedback-modal-close" @click="close($event)">&times;</span>
        <span class="feedback-thank-you" v-show="feedback_sent"></span>
        <h2 v-if="!feedback_sent">Please leave a Feedback</h2>
        <h2 v-else>Thank You for Feedback</h2>
    
        <div class="feedback-rating-stars">
          <ul id="feedback-stars">
            <li 
              v-for="index in 5"
              :class="['star', {'selected': rating >= index, 'disabled': feedback_sent}]"
              :disabled="feedback_sent"
              @click="!feedback_sent && setStar(index)"
            >
              <i class="feedback-star"></i>
            </li>
          </ul>
          <span class="rating-text">{{ rating_text }}</span>
        </div>
    
        <p class="feedback-review-text" v-show="feedback_sent">{{ review }}</p>
        <div class="feedback-extra" v-show="rating < 4 && !feedback_sent">
          <textarea id="feedback-review" v-model="review" rows="5" placeholder="Please enter your Review..."></textarea>
          <small>Found a bug in the plugin? <a :href="getTicketUrl()" target="_blank">Click here</a> to report it.</small>
        </div>
        <a href="https://bit.ly/3bhmesM" class="feedback-submit" v-show="!feedback_sent" target="_blank" @click="leaveFeedback($event)">
          Submit
          <i class="external-icon" v-show="rating > 3"></i>
        </a>
      </div>
    </div>
  </transition>
  `,
  data: () => ({
    has_feedback: bookit_window.has_feedback,
    feedback_sent: false,
    review: '',
    rating: 5,
    rating_text: 'Excellent!',
    ratings: {
      1: 'Poor',
      2: 'Bad',
      3: 'Fair',
      4: 'Good',
      5: 'Excellent!',
    }
  }),
  methods: {
    getTicketUrl() {
      let type = bookit_window.pro_disabled ? 'pre-sale' : 'support';
      return `https://support.stylemixthemes.com/tickets/new/${type}?item_id=35`;
    },
    setStar( rating ) {
      this.rating       = rating;
      this.rating_text  = this.ratings[rating];
    },
    leaveFeedback( event ) {
      if ( this.rating < 4 ) {
        event.preventDefault();
        let data = {
          'item': 'bookit',
          'type': 'plugin',
          'rating': this.rating,
          'review': this.review,
        };
        this.axios.post(`https://panel.stylemixthemes.com/api/item-review`, this.generateFormData(data)).then((res) => {});
      }

      this.axios.get(`${bookit_window.ajax_url}?action=bookit_add_feedback&nonce=${bookit_window.nonces.bookit_add_feedback}`).then((res) => {});

      this.feedback_sent = true;
      this.$emit('setHasFeedback', true);
    },
    close( event ) {
      event.preventDefault();
      this.$store.commit('setShowFeedbackForm', false);
    }
  }
}