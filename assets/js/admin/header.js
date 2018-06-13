var header_user_name = $("#header-user-name");
var header_user_name1 = $("#header-user-name1");
var header_user_avatar = $("#header-user-avatar");
var header_user_avatar1 = $("#header-user-avatar1");
function InitHeader()
{
	header_user_name.html(admin_info.name);	
	header_user_name1.html(admin_info.name);
	if(admin_info.profile_image == ""){
		header_user_avatar.attr("src",baseURL + 'assets/dist/img/avatar.png');
		header_user_avatar1.attr("src",baseURL + 'assets/dist/img/avatar.png');
	}else{
		header_user_avatar.attr("src",admin_info.profile_image);
		header_user_avatar1.attr("src",admin_info.profile_image);
	}
	
}
