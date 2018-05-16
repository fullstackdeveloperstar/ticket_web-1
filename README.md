login

	-post http://159.203.99.60/api/login
	-request
		email, password
	-response
		{
			"success":true,
			"user":
			{
				"userId":"7",
				"name":null,
				"roleId":"3",
				"role":"Employee",
				"user_token":"5afba87a4ba42"
			}
		}


signup

	-post http://159.203.99.60/api/signup
	-request 
		fname, lname, email, password, cpassword
	-response
		{
			"success":false,
			"msg":"User already exists! Please try with other information!"
		}

		{
			"success":true,
			"msg":"Signup is successed"
		}



forgot password

	-post http://159.203.99.60/api/forgotpassword
	-request
		email
	-response
		{
			"email":"rubby.star@hotmail.com",
			"activation_id":"Nbfo8vrAjnBgDoX",
			"createdDtm":"2018-05-16 05:55:44",
			"agent":"Unidentified User Agent",
			"client_ip":"0.0.0.0",
			"success":true,
			"msg":"Email is sent"
		}