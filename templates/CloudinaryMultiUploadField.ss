<div id="uploader-{$Name}" class="cloudinary-multi-upload-field" data-options='$Options'>

    <div class="cloudinary-multi-upload-field-actions-container">
        <div class="cloudinary-multi-upload-field-meta">
            <%t Cloudinary.CLOUD_NAME %>: <strong>$CloudName</strong>
            <% if $Folder %>| <%t Cloudinary.DESTINATION_FOLDER %>: <strong>$Folder</strong><% end_if %>
        </div>

        <div class="cloudinary-multi-upload-field-actions">
            <button class="cloudinary-multi-upload-field-upload"><%t Cloudinary.CTA_UPLOAD %></button>

            <% if $Items %>
                <button class="cloudinary-multi-upload-field-delete-all">Alle löschen</button>
            <% end_if %>
        </div>
    </div>

    <div class="cloudinary-multi-upload-items">
        <ul>
            <% if $Items %>
                <% loop $Items %>
                    <% include CloudinaryMultiUploadItem %>
                <% end_loop %>
            <% end_if %>
        </ul>
    </div>
</div>
