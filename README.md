login

	-post http://159.203.99.60/api/login
	-request
		email, password
	-response
		{
			"success":true,
			"user":
			{
				"userId":"4",
				"password":"$2y$10$t7sVZgFTt0aRlGgXg89Xn.S702Q33HNt932bsRJRue.h.EYmvDMlW",
				"name":null,
				"roleId":"3",
				"role":"Employee",
				"user_token":"5afba6d5109e0",
				"fname":"rubby",
				"lname":"star",
				"profile_image":"http:\/\/localhost\/assets\/uploads\/user_profile\/FA-A39450-2.jpg",
				"createdDtm":"2018-05-16 05:34:45"
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