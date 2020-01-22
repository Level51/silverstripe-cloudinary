<template>
  <div
    class="level51-cu-component"
    :class="[
      {'level51-cu-component--dragging': isDragging},
      {'level51-cu-component--noFile': !file}
    ]"
    @dragover="isDragging = true"
    @dragenter="isDragging = true"
    @dragleave="isDragging = false"
    @dragend="isDragging = false"
    @drop.prevent="handleFileDrop">

    <div
      v-if="configError"
      class="level51-cu-errorMessage">
      {{ i18n('ERR_MISSING_UPLOAD_PRESET') }}
    </div>

    <template v-else>
      <div
        v-if="file"
        class="level51-cu-thumbnailContainer">
        <img
          class="level51-cu-thumbnail"
          :src="file.thumbnailURL">
      </div>

      <div>
        <div
          v-if="file"
          class="level51-cu-fileInfo">
          <strong>{{ i18n('FILENAME') }}:</strong> {{ file.filename }}
          <i
            class="level51-cu-fileMetaInfo font-icon-white-question"
            :title="fileMetaInfo"
          />
          <br>
          <strong>{{ i18n('PUBLIC_ID') }}:</strong>
          <a
            :href="file.mediaLibraryLink"
            target="_blank"
            rel="nofollow noopener">
            {{ file.publicID }}
          </a>
        </div>

        <div class="level51-cu-actions">
          <button
            class="level51-cu-uploadBtn btn btn-outline-primary font-icon-upload"
            @click="openWidget">
            <template v-if="file">
              {{ i18n('CTA_UPLOAD_REPLACE') }}
            </template>
            <template v-else>
              {{ i18n('CTA_UPLOAD') }}
            </template>
          </button>

          <button
            v-if="showRemove"
            class="level51-cu-removeBtn btn btn-outline-danger font-icon-trash-bin"
            @click.prevent="removeFile">
            {{ i18n('CTA_REMOVE') }}
          </button>

          <button
            v-if="file"
            class="level51-cu-deleteBtn btn btn-outline-danger font-icon-trash-bin"
            @click.prevent="deleteFile">
            {{ i18n('CTA_DELETE') }}
          </button>
        </div>
      </div>

      <div
        class="level51-cu-metaInfo"
        :title="metaInfo">
        {{ i18n('CLOUDINARY_INFO') }}
      </div>
    </template>

    <input
      type="hidden"
      :id="payload.id"
      :name="payload.name"
      :value="value">
  </div>
</template>

<script>
  import axios from 'axios';

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
      file: null,
      isDragging: false
    };
  },
  created() {
    if (this.payload.file) this.file = this.payload.file;
    this.init();
  },
  mounted() {
    // Prevent default drag behaviour
    ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach((event) => {
      this.$el.addEventListener(event, (e) => {
        e.preventDefault();
        e.stopPropagation();
      });
    });
  },
  computed: {
    value() {
      return this.file ? this.file.id : 0;
    },
    showRemove() {
      return this.payload.options.showRemove && this.file;
    },
    metaInfo() {
      return `${this.i18n('CLOUD_NAME')}: ${this.payload.cloudinaryOptions.cloudName}\n${this.i18n('DESTINATION_FOLDER')}: ${this.payload.cloudinaryOptions.folder}`;
    },
    configError() {
      return (!this.payload.cloudinaryOptions.useSigned // unsigned
        && !this.payload.cloudinaryOptions.uploadPreset === ''); // preset not empty.
    },
    fileMetaInfo() {
      return `${this.i18n('FORMAT')}: ${this.file.format}
${this.i18n('HEIGHT')}: ${this.file.height}px
${this.i18n('WIDTH')}: ${this.file.width}px
${this.i18n('SIZE')}: ${this.file.niceSize}`;
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

        clientAllowedFormats: this.payload.clientAllowedFormats,
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
    openWidgetWithFile(file) {
      this.widget.open(null, { files: [file] });
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
    },
    handleFileDrop(event) {
      this.isDragging = false;
      event.preventDefault();

      this.openWidgetWithFile(event.dataTransfer.files[0]);

      return false;
    },
    i18n(label) {
      const { i18n } = this.payload;
      return i18n.hasOwnProperty(label) ? i18n[label] : label;
    }
  }
};
</script>

<style lang="less">
  @import "~styles/vars";

  .level51-cu-component {
    border: 2px dashed @color-mono-80;
    border-radius: @border-radius;
    background: @color-mono-100;
    padding: @space-2;
    min-height: 68px;
    display: flex;
    align-items: center;
    transition: all 250ms ease-in-out;

    .level51-cu-fileMetaInfo {
      cursor: help;
    }

    .level51-cu-errorMessage {
      color: @color-error;
    }

    &.level51-cu-component--noFile {
      justify-content: center;
    }

    &.level51-cu-component--dragging {
      border-color: @color-success;
      box-shadow: inset 0 0 10px @color-mono-90;
      cursor: copy;
    }

    .level51-cu-thumbnailContainer {
      margin-right: @space-2;
    }

    .level51-cu-fileInfo {
      margin-bottom: @space-2;
    }

    .level51-cu-metaInfo {
      position: absolute;
      right: 35px;
      bottom: 10px;
      opacity: .75;
      font-size: 0.75rem;

      &:hover {
        cursor: help;
      }
    }
  }
</style>
