[Unreleased]
## Added
- getCloudinaryUrl service function
- Methods for a few image transformations like pad, limit, scale ...
- A few effect methods like grayscale, blur, sepia ...

## Fixed
- Use the service function to get the cloudinary url within the CloudinaryImage Link() function (ensures proper credentials setup)
- privateDownloadLink if the "$asDownload" param is set to false

[0.1.0] - 2018-05-09
## First public release
- CloudinaryImage: data object holding relevant data and provide the first manipulation methods
- CloudinaryUploadField: image uploader using the upload widget including some options like cropping, aspect ratio, upload folder name
- CloudinaryService: helper class for the whole communication with cloudinary
- Basic template with styles and js logic
- webpack dev setup
