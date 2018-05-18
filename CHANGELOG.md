[Unreleased]
## Added
- getCloudinaryUrl service function

## Fixed
- Use the service function to get the cloudinary url within the CloudinaryImage Link() function (ensures proper credentials setup)

[0.1.0] - 2018-05-09
## First public release
- CloudinaryImage: data object holding relevant data and provide the first manipulation methods
- CloudinaryUploadField: image uploader using the upload widget including some options like cropping, aspect ratio, upload folder name
- CloudinaryService: helper class for the whole communication with cloudinary
- Basic template with styles and js logic
- webpack dev setup
