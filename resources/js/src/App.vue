<script>
import axios from 'axios';
import { FormKit } from '@formkit/vue';

export default {
  components: {
    FormKit
  },
  data() {
    return {
      form: {
        deal_name: '',
        deal_stage: '',
        account_name: '',
        account_website: '',
        account_phone: ''
      },
	   stages: [
        "Qualification",
        "Needs Analysis",
        "Value Proposition",
        "Identify Decision Makers",
        "Proposal/Price Quote",
        "Negotiation/Review",
        "Closed Won"
      ],
      message: ''
    };
  },
  methods: {
    async submitForm() {
      try {
        const response = await axios.post('http://localhost/create-deal-account', this.form);
        this.message = response.data.message;
      } catch (error) {
        this.message = 'Error creating deal and account';
      }
    }
  }
};
</script>

<template>
  <div class="form-container">
	<h1 class="form-title">Create Deal and Account</h1>
    <FormKit type="form" @submit="submitForm">
      <FormKit type="text" label="Deal Name" v-model="form.deal_name" validation="required" class="input" />
      <FormKit type="select" label="Stage" name="deal_stage" v-model="form.deal_stage" :options="stages" validation="required" class="select"/>
      <FormKit type="text" label="Account Name" v-model="form.account_name" validation="required" class="input"/>
      <FormKit type="url" label="Account Website" v-model="form.account_website" validation="url" class="input"/>
      <FormKit type="text" label="Account Phone" v-model="form.account_phone" validation="matches:/^[0-9\-\+\s]+$/" />
    </FormKit>
    <p v-if="message" class="mt-2">{{ message }}</p>
  </div>
</template>

<style scoped>
.form-container {
  max-width: 500px;
  margin: auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.form-title {
  text-align: center;
  font-size: 22px;
  font-weight: bold;
  margin-bottom: 20px;
}

.formkit-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.message {
  margin-top: 20px;
  font-size: 18px;
  text-align: center;
  color: #333;
  font-weight: bold;
}
</style>
