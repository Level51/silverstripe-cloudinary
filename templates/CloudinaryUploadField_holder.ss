<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>">
    <% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
    <div class="middleColumn">
        <% if $isReadonly %>
            <% if $File %>
                <img class="cloudinary-upload-field-thumbnail" src="$File.Thumbnail">
            <% else %>
                <%t Cloudinary.READ_ONLY_NO_IMAGE %>
            <% end_if %>
        <% else %>
            $Field
        <% end_if %>
    </div>
    <% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <% if $Description %><span class="description">$Description</span><% end_if %>
</div>
