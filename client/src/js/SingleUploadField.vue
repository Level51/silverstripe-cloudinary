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
          :src="fileSrc">
      </div>

      <div>
        <div
          v-if="file"
          class="level51-cu-fileInfo">
          <strong>{{ i18n('FILENAME') }}:</strong> {{ file.filename }}
          <i
            class="level51-cu-fileMetaInfo font-icon-white-question"
            :title="fileMetaInfo" />
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
            class="level51-cu-uploadBtn level51-btn level51-btn-outline-primary"
            @click="openWidget">
            <fa-icon icon="upload" />
            <template v-if="file">
              {{ i18n('CTA_UPLOAD_REPLACE') }}
            </template>
            <template v-else>
              {{ i18n('CTA_UPLOAD') }}
            </template>
          </button>

          <button
            v-if="showRemove"
            class="level51-cu-removeBtn level51-btn level51-btn-outline-danger"
            @click.prevent="removeFile">
            <fa-icon icon="trash-can" />
            {{ i18n('CTA_REMOVE') }}
          </button>

          <button
            v-if="file"
            class="level51-cu-deleteBtn level51-btn level51-btn-outline-danger"
            @click.prevent="deleteFile">
            <fa-icon icon="trash-can" />
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
import UploadFieldMixin from './UploadFieldMixin';

export default {
  data() {
    return {
      file: null
    };
  },
  mixins: [UploadFieldMixin],
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
    },
    fileMetaInfo() {
      return `${this.i18n('FORMAT')}: ${this.file.format}
${this.i18n('HEIGHT')}: ${this.file.height}px
${this.i18n('WIDTH')}: ${this.file.width}px
${this.i18n('SIZE')}: ${this.file.niceSize}`;
    },
    options() {
      const options = {
        cropping: this.payload.cloudinaryOptions.cropping,
        croppingAspectRatio: this.payload.cloudinaryOptions.croppingAspectRatio,
        croppingShowDimensions: true,
      };

      return Object.assign({}, this.baseOptions, options);
    },
    fileSrc() {
      if (this.file) {
        return this.file.thumbnailURL ? this.file.thumbnailURL : this.file.url;
      }

      return null;
    }
  },
  methods: {
    init() {
      this.widget = cloudinary.createUploadWidget(
        this.options,
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
  @import "~styles/upload-field";

  .level51-cu-component {
    .level51-cu-fileMetaInfo {
      cursor: help;
    }

    .level51-cu-thumbnailContainer {
      margin-right: @space-2;
    }

    .level51-cu-fileInfo {
      margin-bottom: @space-2;
    }
  }
</style>
