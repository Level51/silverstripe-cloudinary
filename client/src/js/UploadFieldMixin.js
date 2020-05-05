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
      isDragging: false
    };
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
    metaInfo() {
      return `${this.i18n('CLOUD_NAME')}: ${this.payload.cloudinaryOptions.cloudName}\n${this.i18n('DESTINATION_FOLDER')}: ${this.payload.cloudinaryOptions.folder}`;
    },
    configError() {
      return !this.payload.cloudinaryOptions.uploadPreset
        && !this.payload.cloudinaryOptions.useSigned;
    },
    baseOptions() {
      const options = {
        cloudName: this.payload.cloudinaryOptions.cloudName,
        sources: ['local'],
        folder: this.payload.cloudinaryOptions.folder,
        resourceType: 'image',
        theme: this.payload.cloudinaryOptions.theme,
        showAdvancedOptions: false,

        // language: en // see https://cloudinary.com/documentation/upload_widget#localization

        // TODO check useful additional options
      };

      // Set upload preset
      if (this.payload.cloudinaryOptions.uploadPreset) {
        options.uploadPreset = this.payload.cloudinaryOptions.uploadPreset;
      }

      // Limit allowed file formats
      if (this.payload.allowedExtensions) {
        options.clientAllowedFormats = this.payload.allowedExtensions;
      }

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

      return options;
    }
  },
  methods: {
    openWidget() {
      this.widget.open();
    },
    openWidgetWithFile(file) {
      this.openWidgetWithFiles([file]);
    },
    openWidgetWithFiles(files) {
      this.widget.open(null, { files });
    },
    handleFileDrop(event) {
      this.isDragging = false;
      event.preventDefault();

      this.openWidgetWithFiles(event.dataTransfer.files);

      return false;
    },
    i18n(label) {
      const { i18n } = this.payload;
      return i18n.hasOwnProperty(label) ? i18n[label] : label;
    }
  }
};
