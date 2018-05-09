/* eslint-disable */

// Require styles
require('../css/cloudinary-upload-field.less');

(function ($) {
  $.entwine(($) => {
    $('.cms-edit-form .cloudinary-upload-field').entwine({
      onmatch() {
        const data = this.data('options');
        const actionsContainer = this.find('.cloudinary-upload-field-actions');
        const uploadBtn = actionsContainer.find('.cloudinary-upload-field-upload');
        const removeBtn = actionsContainer.find('.cloudinary-upload-field-remove');
        const deleteBtn = actionsContainer.find('.cloudinary-upload-field-delete');
        const hiddenInput = this.find(`input[name="${data.name}"]`);
        const thumbnailImg = this.find('img.cloudinary-upload-field-thumbnail');
        const handleRemove = removeBtn.length > 0;

        // hide the remove and delete actions if there is no current value
        if (!hiddenInput.attr('value') || hiddenInput.attr('value') == '0') {
          if (handleRemove)
            removeBtn.hide();

          deleteBtn.hide();
        }

        // Define basic options
        let options = {
          cloud_name: data.cloud_name,
          theme: data.theme,
          sources: ['local'],
          multiple: false,
          cropping: data.cropping,
          folder: data.folder,
          resource_type: 'image',
          client_allowed_formats: ['png', 'gif', 'jpeg'],
          cropping_aspect_ratio: data.cropping_aspect_ratio,
          cropping_show_dimensions: true
        };

        // Check if an upload_preset is defined
        if (data.upload_preset)
          options.upload_preset = data.upload_preset;

        /**
         * Check for signed uploads, add the api_key and a function to generate the signature
         * @see https://cloudinary.com/documentation/upload_widget#signed_uploads
         */
        if (data.use_signed) {
          options.api_key = data.api_key;
          options.upload_signature = function(callback, params_to_sign){
            $.ajax({
              url     : `${location.origin}/admin/cloudinary/generateSignature`,
              type    : "GET",
              dataType: "text",
              data    : { data: params_to_sign},
              complete: function() {console.log("complete")},
              success : function(signature, textStatus, xhr) { callback(signature); },
              error   : function(xhr, status, error) { console.log(xhr, status, error); }
            });
          };
        }

        // Show the uploader widget on click
        uploadBtn.on('click', () => {
          // https://cloudinary.com/documentation/upload_widget#upload_widget_options
          cloudinary.openUploadWidget(
            options,
            (error, result) => {
              if (result) {
                result = result[0];

                // Get the thumbnail, either directly from the thumbnail_url if not private or try to get the url of the first eager transformation
                let thumbnail = null;
                if (result.type === 'private') {
                  if (result.hasOwnProperty('eager') && typeof result.eager === 'object' && result.eager.length > 0) {
                    thumbnail = result.eager[0].secure_url;
                  }
                } else {
                  thumbnail = result.thumbnail_url;
                }

                result.thumbnail = thumbnail;

                $.ajax({
                  url: `${location.origin}/admin/cloudinary/onAfterUpload`,
                  type: 'post',
                  data: result,
                  success(response) {
                    hiddenInput.attr('value', response);

                    thumbnailImg.attr('src', thumbnail);

                    // Show the remove and delete buttons
                    if (handleRemove)
                      removeBtn.show();

                    deleteBtn.show();
                  }
                });
              }
            }
          );
        });

        // Remove (but not delete) the image, will be unlinked from the DO on save
        if (handleRemove) {
          removeBtn.on('click', () => {
            hiddenInput.attr('value', 0);
            thumbnailImg.attr('src', '');

            // Hide remove/delete buttons
            removeBtn.hide();
            deleteBtn.hide();
          });
        }

        // Delete the image - this will remove the CloudinaryImage object on our side an also trigger the remote delete
        deleteBtn.on('click', () => {
          $.ajax({
            url: `${location.origin}/admin/cloudinary/deleteImage`,
            type: 'post',
            data: {
              id: this.find('input').attr('value')
            },
            success() {
              hiddenInput.attr('value', 0);
              thumbnailImg.attr('src', '');

              // Hide remove/delete buttons
              if (handleRemove)
                removeBtn.hide();

              deleteBtn.hide();
            }
          });
        });

        this._super();
      }
    });
  });
}(jQuery));
