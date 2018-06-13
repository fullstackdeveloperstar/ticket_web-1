var admin_info;

$.ajax({
     url: baseURL + ADMIN_USER_INFO,
     type: "GET",
     beforeSend: function(xhr){xhr.setRequestHeader('token', user_token);},
     dataType: 'json',
     success: function(data) 
     { 
     	if(data.success){
     		admin_info = data.user;	
     		console.log(admin_info);
     		InitHeader();
     	}else{
     		window.location = baseURL + "logout";
     	}
     	
     }
});