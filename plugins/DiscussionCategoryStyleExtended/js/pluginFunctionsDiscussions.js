
// Injects HTML so the discussion category for each discussion is before the profile picture.
// REALLY ugly javascript that needs refactoring, but it works! Need to check browser compatability at some point.
function inject_category_name_before_profile_picture()
{
    var discussion_html_list = document.getElementsByClassName("DataList Discussions")[0].children;
    
    for (var i = 0; i < discussion_html_list.length; i++)
        {
            var discussion_category = discussion_html_list[i].getElementsByClassName("MItem Category");
            if (discussion_category.length > 0)
                {
                    var element_URL = discussion_category[0].children[0].href;
                    var element_ID = discussion_category[0].children[0].innerHTML;
                    var element_HTML = "";
                    
                    element_HTML += '<a class="CategoryLabel" href="' + element_URL + '" id="' + element_ID + '">';
                    element_HTML += '<center>' + element_ID + '</center>';
                    element_HTML += '</a>';
                    
                    discussion_html_list[i].insertAdjacentHTML('afterbegin', element_HTML);
                }
        }
}

$(document).ready(function() {
    inject_category_name_before_profile_picture();
});