<div id="uploader-{$Name}" class="cloudinary-upload-field" data-options='$Options'>
    <div class="cloudinary-upload-field-thumbnail">
        <img class="cloudinary-upload-field-thumbnail" src="$File.Thumbnail">
    </div>

    <div class="cloudinary-upload-field-actions">
        <div class="cloudinary-upload-field-meta">

            <% if $File %>
                <strong>Name:</strong> $File.Filename | <strong>Public ID:</strong> <a href="$File.MediaLibraryLink" target="_blank">$File.PublicID</a> <br>
            <% end_if %>

            <%t Lvl51\\Cloudinary\\Cloudinary.CLOUD_NAME %>: <strong>$CloudName</strong>
            <% if $Folder %>| <%t Lvl51\\Cloudinary\\Cloudinary.DESTINATION_FOLDER %>: <strong>$Folder</strong><% end_if %>
        </div>

        <div>
            <button class="cloudinary-upload-field-upload"><%t Lvl51\\Cloudinary\\Cloudinary.CTA_UPLOAD %></button>

            <% if $showRemove %>
                <button class="cloudinary-upload-field-remove"><%t Lvl51\\Cloudinary\\Cloudinary.CTA_REMOVE %></button>
            <% end_if %>

            <button class="cloudinary-upload-field-delete"><%t Lvl51\\Cloudinary\\Cloudinary.CTA_DELETE %></button>
        </div>
    </div>

    <input type="hidden" name="$Name" id="$ID" value="$Value">
</div>
