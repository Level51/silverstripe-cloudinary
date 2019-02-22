# SilverStripe Cloudinary image uploader
Adds a **CloudinaryImage** data object and an appropriate uploader using Cloudinary's javascript upload widget.

## Setup
You have to define some mandatory configuration to get started:

```yaml
Cloudinary:
  cloud_name: String, mandatory
  api_key: String, mandatory
  api_secret: String, mandatory
  upload_preset: String, mandatory if unsigned, optional if signed
  
  # https://cloudinary.com/documentation/upload_widget#look_and_feel_customization
  theme: default 'white', 
  
  # https://cloudinary.com/documentation/upload_widget#signed_uploads
  use_signed: true
  
  # https://cloudinary.com/documentation/admin_api#delete_all_or_selected_resources
  image_type: 'private'
  
  # Whether to show/hide the remove button
  show_remove: false
  
  # Whether to append the g_custom option or not
  use_custom_gravity: true
  
```

### Features
- CloudinaryUploadField using the javascript upload widget - so direct uploads to Cloudinary
- Store the relevant information in a CloudinaryImage object 

### Requirements
- SilverStripe ^3.6.5
- cloudinary_php ^1.9

### Extend
If you need any further fields just extend the CloudinaryImage class with a data extension. To inject information returned by Cloudinary during the upload create another extension for the CloudinaryUploadController and use one of the two extensions hooks **onBeforeImageCreated** or **onAfterImageCreated**. Both get passed the image object, either before or after the first write.

## Maintainer
- Daniel Kliemsch <dk@lvl51.de>
