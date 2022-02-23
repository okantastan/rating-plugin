function checked_rating(a)
{
    //refresh active image
    jQuery('.rating_img').attr('src', myScript.pluginsUrl + '/Rating-Plugin/images/star.png');
    //refresh input checked
    jQuery('.rating').removeAttr('checked');
    //jQuery('#rating_' + a).attr('checked','checked');
    jQuery('#rating_' + a).click();
    for (let i = a; i > 0; i--) {
        jQuery('#rating_img_' + i).attr('src', myScript.pluginsUrl + '/Rating-Plugin/images/star_active.png');
    }
}