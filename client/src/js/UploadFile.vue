<template>
  <div class="level51-cu-uploadedFile">
    <div class="level51-cu-imageContainer">
      <img
        class="level51-cu-thumbnail"
        :src="fileSrc">
    </div>

    <div
      class="level51-cu-fileInfo">
      {{ file.filename }}
      <i
        class="level51-cu-fileMetaInfo font-icon-white-question"
        :title="fileMetaInfo"
      />
    </div>

    <div class="level51-cu-uploadedFile-actions">
      <a
        class="level51-btn level51-btn-outline-primary level51-btn-sm"
        :href="file.mediaLibraryLink"
        target="_blank"
        rel="nofollow noopener">
        <fa-icon icon="external-link" />
        {{ i18n('CTA_SHOW') }}
      </a>
      <button
        v-if="file"
        class="level51-cu-deleteBtn level51-btn level51-btn-sm level51-btn-outline-danger"
        @click.prevent="deleteFile">
        <fa-icon icon="trash-can" />

        {{ i18n('CTA_DELETE') }}
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    file: {
      type: Object,
      required: true
    },
    payload: {
      type: Object,
      required: true
    }
  },
  computed: {
    fileMetaInfo() {
      return `${this.i18n('FORMAT')}: ${this.file.format}
${this.i18n('HEIGHT')}: ${this.file.height}px
${this.i18n('WIDTH')}: ${this.file.width}px
${this.i18n('SIZE')}: ${this.file.niceSize}`;
    },
    fileSrc() {
      return this.file.thumbnailURL ? this.file.thumbnailURL : this.file.url;
    }
  },
  methods: {
    i18n(label) {
      const { i18n } = this.payload;
      return i18n.hasOwnProperty(label) ? i18n[label] : label;
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
        if (response.data) this.$emit('deleted', this.file.id);
      });
    }
  }
};
</script>

<style lang="less">
  @import (reference) "~styles/vars";

  .level51-cu-uploadedFile {
    margin: @space-1;
    padding: @space-2;
    position: relative;
    text-align: center;
    display: flex;
    flex-direction: column;
    border: 2px dashed @color-mono-80;
    border-radius: @border-radius;
    width: calc(25% ~'-' @space-1 * 2);

    .level51-cu-imageContainer {
      margin-bottom: @space-2;
      height: 60px;

      img {
        max-height: 100%;
        max-width: 100%;
        display: block;
        margin: 0 auto;
      }
    }

    .level51-cu-fileMetaInfo {
      position: absolute;
      top: @space-1;
      right: @space-1;
      cursor: help;
    }

    .level51-cu-fileInfo {
      .flex-auto;
    }

    .level51-cu-uploadedFile-actions {
      justify-self: flex-end;
      margin-top: @space-2;
      display: flex;
      justify-content: center;

      a.btn {
        display: flex;
        align-items: center;
      }

      .level51-btn-sm {
        padding: .25rem .5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: .2rem;
        display: flex;
        align-items: center;
        margin: 0 @space-1;

        &[class*=font-icon-]:before {
          font-size: 1rem;
        }
      }
    }
  }
</style>
