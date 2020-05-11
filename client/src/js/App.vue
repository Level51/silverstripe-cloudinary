<template>
  <multi-upload-field
    v-if="isMultiUpload"
    :payload="payload" />

  <single-upload-field
    v-else
    :payload="payload" />
</template>

<script>
import SingleUploadField from './SingleUploadField.vue';
import MultiUploadField from './MultiUploadField.vue';

export default {
  components: { SingleUploadField, MultiUploadField },
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  computed: {
    isMultiUpload() {
      return this.payload.type && this.payload.type === 'multi';
    }
  }
};
</script>
