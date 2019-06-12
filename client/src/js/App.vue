<template>
  <div class="level51-cu-component">
    <div
      v-if="file">
      <div class="level51-cu-thumbnailContainer">
        <img
          class="level51-cu-thumbnail"
          :src="file.thumbnailURL">
      </div>
    </div>

    <div class="level51-cu-actions">
      <button
        class="level51-cu-uploadBtn btn btn-outline-primary font-icon-upload"
        @click="openWidget">
        Upload
      </button>

      <!-- TODO implement remove action -->
      <button
        v-if="showRemove"
        class="level51-cu-removeBtn btn btn-outline-danger font-icon-trash-bin"
        @click.prevent="removeFile">
        Remove
      </button>

      <!-- TODO handle delete action -->
      <button
        v-if="file"
        class="level51-cu-deleteBtn btn btn-outline-danger font-icon-trash-bin"
        @click.prevent="deleteFile">
        Delete
      </button>
    </div>

    <input
      type="hidden"
      :id="payload.id"
      :name="payload.name"
      :value="value">
  </div>
</template>

<script>
import axios from 'axios';

// TODO localization
// TODO styling
// TODO meta data (always visible || showCloudName = false)
// TODO drag&drop handling?
export default {
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      widget: null,
      file: null
    };
  },
  created() {
    if (this.payload.file) this.file = this.payload.file;
    this.init();
  },
  computed: {
    value() {
      return this.file ? this.file.id : 0;
    },
    showRemove() {
      return this.payload.options.showRemove && this.file;
    }
  },
  methods: {
    init() {
      // Define basic options
      const options = {
        cloudName: this.payload.cloudinaryOptions.cloudName,
        // TODO uploadPreset is a required option - ensure that
        uploadPreset: this.payload.cloudinaryOptions.uploadPreset,

        sources: ['local'],
        multiple: false,
        // maxFiles: 10, only relevant if multiple uploads are enabled
        cropping: this.payload.cloudinaryOptions.cropping,
        croppingAspectRatio: this.payload.cloudinaryOptions.croppingAspectRatio,
        croppingShowDimensions: true,

        folder: this.payload.cloudinaryOptions.folder,
        resourceType: 'image',

        theme: this.payload.cloudinaryOptions.theme,

        clientAllowedFormats: ['png', 'gif', 'jpeg'],
        // maxFileSize: 1500000, Number of bytes, no client side limit per default
        // maxImageHeight: 2000 // Client-isde scale down
        // maxImageWidth: 2000  // -- "" --

        showAdvancedOptions: false,

        // language: en // see https://cloudinary.com/documentation/upload_widget#localization

        // TODO check useful additional options
      };

      /**
       * Check for signed uploads, add the apiKey and a function to generate the signature
       *
       * @see https://cloudinary.com/documentation/upload_widget#signed_uploads
       */
      if (this.payload.cloudinaryOptions.useSigned) {
        options.apiKey = this.payload.cloudinaryOptions.apiKey;

        options.uploadSignature = (callback, paramsToSign) => {
          axios.get(
            `${location.origin}/admin/cloudinary/generateSignature`,
            {
              params: paramsToSign
            }
          ).then((response) => {
            callback(response.data);
          });
        };
      }

      this.widget = cloudinary.createUploadWidget(
        options,
        (error, result) => {
          if (!error && result && result.event === 'success') {
            axios.post(
              `${location.origin}/admin/cloudinary/onAfterUpload`,
              result.info
            ).then((response) => {
              this.file = response.data;
            });
          }
        }
      );
    },
    openWidget() {
      this.widget.open();
    },
    removeFile() {
      this.file = null;
    },
    deleteFile() {
      axios.delete(
        `${location.origin}/admin/cloudinary/deleteImage`,
        {
          data: {
            id: this.file.id
          }
        }
      ).then((response) => {
        // TODO error handling?
        if (response.data) this.file = null;
      });
    }
  }
};
</script>

<style lang="less">
  @import "~styles/vars";

  .level51-cu-component {
    border: 2px dashed @color-border;
    border-radius: @border-radius;
    background: @color-mono-100;
    padding: @space-2;
    height: 68px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>
