<div id="$HolderID" class="form-group field<% if $extraClass %> $extraClass<% end_if %>">
    <% if $Title %>
        <label for="$ID" id="title-$ID" class="form__field-label">$Title</label>
    <% end_if %>
    <div class="form__field-holder<% if not $Title %> form__field-holder--no-label<% end_if %>">
        <% if $isReadonly %>
            <% if $File %>
                <img class="cloudinary-upload-field-thumbnail" src="$File.Thumbnail">
            <% else %>
                <%t Cloudinary.READ_ONLY_NO_IMAGE %>
            <% end_if %>
        <% else %>
            $Field
        <% end_if %>

        <% if $Message %><p class="alert $MessageType" role="alert" id="message-$ID">$Message</p><% end_if %>
        <% if $Description %><p class="form__field-description form-text" id="describes-$ID">$Description</p><% end_if %>
    </div>
    <% if $RightTitle %><p class="form__field-extra-label" id="extra-label-$ID">$RightTitle</p><% end_if %>
</div>
