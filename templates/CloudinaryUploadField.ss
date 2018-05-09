<div id="uploader-{$Name}" class="cloudinary-upload-field" data-options='$Options'>
    <div class="cloudinary-upload-field-thumbnail">
        <img class="cloudinary-upload-field-thumbnail" src="$File.Thumbnail">
    </div>

    <div class="cloudinary-upload-field-actions">
        <div class="cloudinary-upload-field-meta">
            <%t Cloudinary.CLOUD_NAME %>: <strong>$CloudName</strong>
            <% if $Folder %>| <%t Cloudinary.DESTINATION_FOLDER %>: <strong>$Folder</strong><% end_if %>
        </div>

        <div>
            <button class="cloudinary-upload-field-upload"><%t Cloudinary.CTA_UPLOAD %></button>

            <% if $showRemove %>
                <button class="cloudinary-upload-field-remove"><%t Cloudinary.CTA_REMOVE %></button>
            <% end_if %>

            <button class="cloudinary-upload-field-delete"><%t Cloudinary.CTA_DELETE %></button>
        </div>
    </div>

    <input type="hidden" name="$Name" id="$ID" value="$Value">
</div>
