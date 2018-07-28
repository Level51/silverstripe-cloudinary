/* eslint-disable no-restricted-globals,no-shadow,no-undef,no-underscore-dangle */
// Require styles
require('../css/cloudinary-multi-upload-field.less');

(function ($) {
  $.entwine(($) => {
    $('.cms-edit-form .cloudinary-multi-upload-field').entwine({
      onmatch() {
        const that = this;
        const data = this.data('options');
        const uploadBtn = this.find('.cloudinary-multi-upload-field-upload');

        // Define basic options
        const options = {
          cloud_name: data.cloud_name,
          theme: data.theme,
          sources: ['local'],
          multiple: true,
          folder: data.folder,
          resource_type: 'image',
          client_allowed_formats: ['png', 'gif', 'jpeg']
        };

        // Check if an upload_preset is defined
        if (data.upload_preset) {
          options.upload_preset = data.upload_preset;
        }

        /**
         * Check for signed uploads, add the api_key and a function to generate the signature
         * @see https://cloudinary.com/documentation/upload_widget#signed_uploads
         */
        if (data.use_signed) {
          options.api_key = data.api_key;
          options.upload_signature = (callback, paramsToSign) => {
            $.ajax({
              url: `${location.origin}/admin/cloudinary/generateSignature`,
              type: 'GET',
              dataType: 'text',
              data: { data: paramsToSign },
              success(signature) {
                callback(signature);
              },
              error(xhr, status, error) {
                console.log(xhr, status, error);
              }
            });
          };
        }

        // Show the uploader widget on click
        uploadBtn.on('click', () => {
          // https://cloudinary.com/documentation/upload_widget#upload_widget_options
          cloudinary.openUploadWidget(options);
        });

        // Handle DataObject creation as soon as all images have been uploaded
        $(document).off('cloudinarywidgetsuccess').on('cloudinarywidgetsuccess', (e, response) => {
          $.ajax({
            url: `${location.origin}/admin/cloudinary/onAfterMultipleUpload`,
            type: 'post',
            data: {
              images: response,
              relation: data.relation
            },
            success(response) {
              $(that).find('.cloudinary-multi-upload-items').find('ul').append(response);
            }
          });
        });

        this._super();

        // Delete all action
        $('.cloudinary-multi-upload-field-delete-all').entwine({
          onclick() {
            const ids = [];
            $(that).find('.cloudinary-multi-upload-field-delete').each(function () {
              ids.push($(this).data('id'));
            });

            $.ajax({
              url: `${location.origin}/admin/cloudinary/deleteImages`,
              type: 'post',
              data: {
                ids
              },
              success() {
                $(that).find('.cloudinary-multi-upload-items').find('ul').empty();
              }
            });
          }
        });
      }
    });

    // Listen for delete button clicks
    $('.cloudinary-multi-upload-field-delete').entwine({
      onclick() {
        const btn = $(this);

        $.ajax({
          url: `${location.origin}/admin/cloudinary/deleteImage`,
          type: 'post',
          data: {
            id: btn.data('id')
          },
          success() {
            btn.parent().remove();
          }
        });
      }
    });
  });
}(jQuery));
