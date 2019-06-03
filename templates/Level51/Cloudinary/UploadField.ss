<div id="uploader-{$Name}" class="cloudinary-upload-field" data-options='$Options'>
    <div class="cloudinary-upload-field-thumbnail">
        <img class="cloudinary-upload-field-thumbnail" src="$File.Thumbnail">
    </div>

    <div class="cloudinary-upload-field-actions">
        <% if $File || $showCloudName %>
            <div class="cloudinary-upload-field-meta">

                <% if $File %>
                    <strong>Name:</strong> $File.Filename | <strong>Public ID:</strong> <a href="$File.MediaLibraryLink"
                                                                                           target="_blank">$File.PublicID</a>
                    <br>
                <% end_if %>

                <% if $showCloudName %>
                    <%t Level51\\Cloudinary\\Cloudinary.CLOUD_NAME %>: <strong>$CloudName</strong>
                    <% if $Folder %>| <%t Level51\\Cloudinary\\Cloudinary.DESTINATION_FOLDER %>:
                        <strong>$Folder</strong><% end_if %>
                <% end_if %>
            </div>
        <% end_if %>

        <div>
            <button
                class="cloudinary-upload-field-upload btn btn-outline-primary font-icon-upload"><%t Level51\\Cloudinary\\Cloudinary.CTA_UPLOAD %></button>

            <% if $showRemove %>
                <button
                    class="cloudinary-upload-field-remove btn btn-outline-danger font-icon-trash-bin"><%t Level51\\Cloudinary\\Cloudinary.CTA_REMOVE %></button>
            <% end_if %>

            <button
                class="cloudinary-upload-field-delete btn btn-outline-danger font-icon-trash-bin"><%t Level51\\Cloudinary\\Cloudinary.CTA_DELETE %></button>
        </div>
    </div>

    <input type="hidden" name="$Name" id="$ID" value="$Value">

    <% if not $showCloudName %>
        <div class="cloudinary-metainfo" title="<%t Level51\\Cloudinary\\Cloudinary.CLOUD_NAME %>: $CloudName<% if $Folder %> | <%t Level51\\Cloudinary\\Cloudinary.DESTINATION_FOLDER %>: $Folder<% end_if %>">Cloudinary Info</div>
    <% end_if %>
</div>
