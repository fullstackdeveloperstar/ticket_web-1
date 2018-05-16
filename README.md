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

forgot password