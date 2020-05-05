<template>
  <div class="level51-cmu">
    <div
      class="level51-cu-component level51-cu-component--noFile"
      :class="[
        {'level51-cu-component--dragging': isDragging},
        {'level51-cu-component--disabled': fileLimitReached}
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
        <div>
          <div class="level51-cu-actions">
            <button
              class="level51-cu-uploadBtn btn btn-outline-primary font-icon-upload"
              @click="openWidget">
              {{ i18n('CTA_UPLOAD') }}
            </button>
          </div>
        </div>

        <div
          class="level51-cu-metaInfo"
          :title="metaInfo">
          {{ i18n('CLOUDINARY_INFO') }}
        </div>
      </template>
    </div>

    <div
      class="level51-cmu-filesWrapper"
      v-if="hasFiles">
      <upload-file
        v-for="file in files"
        :key="file.id"
        :file="file"
        :payload="payload"
        @deleted="fileDeleted" />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import UploadFile from './UploadFile.vue';
import UploadFieldMixin from './UploadFieldMixin';

export default {
  data() {
    return {
      files: []
    };
  },
  mixins: [UploadFieldMixin],
  components: { UploadFile },
  created() {
    if (this.payload.files) this.files = this.payload.files;
    this.init();
  },
  computed: {
    hasFiles() {
      return this.files && Array.isArray(this.files) && this.files.length > 0;
    },
    fileLimitReached() {
      return this.hasFiles && this.files.length >= this.payload.cloudinaryOptions.maxFiles;
    },
    options() {
      const options = {
        multiple: true,
        maxFiles: this.payload.cloudinaryOptions.maxFiles,
      };

      return Object.assign({}, this.baseOptions, options);
    }
  },
  methods: {
    init() {
      this.widget = cloudinary.createUploadWidget(
        this.options,
        (error, result) => {
          if (!error && result) {
            if (result.event === 'queues-end') {
              const files = result.info.files.filter(f => f.done && !(f.aborted || f.failed));

              if (files.length > 0) {
                axios.post(
                  `${location.origin}/admin/cloudinary/onAfterMultiUpload`,
                  {
                    files,
                    relation: this.payload.relation
                  }
                ).then((response) => {
                  if (!this.files || !Array.isArray(this.files)) {
                    this.files = [];
                  }
                  this.files = this.files.concat(response.data);
                });
              }
            }
          }
        }
      );
    },
    fileDeleted(id) {
      this.files.splice(this.files.findIndex(f => f.id === id), 1);
    }
  }
};
</script>

<style lang="less">
  @import "~styles/upload-field";

  .level51-cmu {
    .level51-cu-component {
      &.level51-cu-component--disabled {
        pointer-events: none;
        opacity: 0.5;
      }
    }

    .level51-cmu-filesWrapper {
      display: flex;
      flex-wrap: wrap;
      margin: @space-2 -@space-1 0;
    }
  }
</style>
