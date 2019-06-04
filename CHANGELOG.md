[1.1.3] - 2019-06-03
### Added
- Option to make Cloudinary meta data less prominent
- `root_folder` config value, allows to use a folder as root for all upload fields

### Changed
- Updated routes config to fix a interference with default admin/graphql route

[1.1.2] - 2019-05-09
### Changed
- Proper classes for the actions/buttons

[1.1.1] - 2019-05-08
### Added
- Save file size, width and height of the initial upload (after incoming transormation)

### Fixed
- Namespace in the field holder template 

[1.1.0] - 2019-04-24
### Changed
- Namespace update to Level51\Cloudinary

[1.0.1] - 2019-04-16
## Changed
- Ensure that the upload controller route is defined before other admin routes

[1.0.0] - 2019-03-28
## Changed
- Refactor for SilverStripe 4 support - see develop-ss3 branch or 0.x.x releases for SS3

[0.2.1] - 2019-03-19
## Changed
- Trigger Cloudinary delete/destroy only if a public id is set

[0.2.0] - 2019-02-22
## Added
- getCloudinaryUrl service function
- Methods for a few image transformations like pad, limit, scale ...
- A few effect methods like grayscale, blur, sepia ...
- use_custom_gravity config option
- Method to get a link to the image resource in the Cloudinary media library
- Show original filename and the public id within the upload field
- Readonly behaviour

## Changed
- Return the whole image tag in the forTemplate method

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
