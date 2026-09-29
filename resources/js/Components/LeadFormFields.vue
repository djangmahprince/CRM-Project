<script setup>
import FormField from './FormField.vue';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    picklists: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const inputClass = 'ns-input';
</script>

<template>
    <FormField label="Salutation" for-id="salutation" :error="errors.salutation">
        <select id="salutation" v-model="form.salutation" :class="inputClass">
            <option value="">Select</option>
            <option v-for="option in picklists.salutations" :key="option" :value="option">{{ option }}</option>
        </select>
    </FormField>

    <FormField label="First name" for-id="first_name" :error="errors.first_name">
        <input id="first_name" v-model="form.first_name" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Last name" for-id="last_name" :error="errors.last_name">
        <input id="last_name" v-model="form.last_name" type="text" required :class="inputClass" />
    </FormField>

    <FormField label="Company" for-id="company" :error="errors.company">
        <input id="company" v-model="form.company" type="text" required :class="inputClass" />
    </FormField>

    <FormField label="Title" for-id="title" :error="errors.title">
        <input id="title" v-model="form.title" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Email" for-id="email" :error="errors.email">
        <input id="email" v-model="form.email" type="email" :class="inputClass" />
    </FormField>

    <FormField label="Phone" for-id="phone" :error="errors.phone">
        <input id="phone" v-model="form.phone" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Mobile" for-id="mobile" :error="errors.mobile">
        <input id="mobile" v-model="form.mobile" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Lead status" for-id="lead_status" :error="errors.lead_status">
        <select id="lead_status" v-model="form.lead_status" required :class="inputClass">
            <option v-for="option in picklists.lead_statuses" :key="option" :value="option">{{ option }}</option>
        </select>
    </FormField>

    <FormField label="Lead source" for-id="lead_source" :error="errors.lead_source">
        <select id="lead_source" v-model="form.lead_source" :class="inputClass">
            <option value="">Select</option>
            <option v-for="option in picklists.lead_sources" :key="option" :value="option">{{ option }}</option>
        </select>
    </FormField>

    <FormField label="Rating" for-id="rating" :error="errors.rating">
        <select id="rating" v-model="form.rating" :class="inputClass">
            <option value="">Select</option>
            <option v-for="option in picklists.ratings" :key="option" :value="option">{{ option }}</option>
        </select>
    </FormField>

    <FormField label="Industry" for-id="industry" :error="errors.industry">
        <select id="industry" v-model="form.industry" :class="inputClass">
            <option value="">Select</option>
            <option v-for="option in picklists.industries" :key="option" :value="option">{{ option }}</option>
        </select>
    </FormField>

    <FormField label="Annual revenue" for-id="annual_revenue" :error="errors.annual_revenue">
        <input id="annual_revenue" v-model="form.annual_revenue" type="number" min="0" step="0.01" :class="inputClass" />
    </FormField>

    <FormField label="Employees" for-id="number_of_employees" :error="errors.number_of_employees">
        <input id="number_of_employees" v-model="form.number_of_employees" type="number" min="0" :class="inputClass" />
    </FormField>

    <FormField label="Website" for-id="website" :error="errors.website">
        <input id="website" v-model="form.website" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Street" for-id="street" :error="errors.street">
        <input id="street" v-model="form.street" type="text" :class="inputClass" />
    </FormField>

    <FormField label="City" for-id="city" :error="errors.city">
        <input id="city" v-model="form.city" type="text" :class="inputClass" />
    </FormField>

    <FormField label="State" for-id="state" :error="errors.state">
        <input id="state" v-model="form.state" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Postal code" for-id="postal_code" :error="errors.postal_code">
        <input id="postal_code" v-model="form.postal_code" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Country" for-id="country" :error="errors.country">
        <input id="country" v-model="form.country" type="text" :class="inputClass" />
    </FormField>

    <FormField label="Description" for-id="description" :error="errors.description" class="md:col-span-2">
        <textarea id="description" v-model="form.description" rows="4" :class="inputClass" />
    </FormField>
</template>
