login

	-post http://159.203.99.60/api/login
	-request
		email, password, device_token
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
		fname, lname, email, password, cpassword, device_token
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

users api

myapi
	
	-get http://159.203.99.60/api/user/myinfo
	-header
		token
	-response
		{
			"userId":"4",
			"email":"rubby.star@hotmail.com",
			"name":null,
			"fname":"rubby",
			"lname":"star",
			"mobile":null,
			"roleId":"3",
			"user_token":"5afba6d5109e0",
			"profile_image":"http:\/\/localhost\/assets\/uploads\/user_profile\/FA-A39450-2.jpg",
			"device_token":"123123",
			"isDeleted":"0",
			"createdBy":"-1",
			"createdDtm":"2018-05-16 05:34:45",
			"updatedBy":null,
			"updatedDtm":null
		}

event list
	
	-get http://159.203.99.60/api/event/getall

	-request
		header: token
	-response
		[
			{
				"event_id":"1",
				"event_title":"test Event Title",
				"event_description":"Our currency rankings show that the most popular Euro exchange rate is the USD to EUR rate. The currency code for Euros is EUR, and the currency symbol is \u20ac.",
				"event_image":"http:\/\/localhost\/assets\/uploads\/event_image\/test.png",
				"event_start_date_time":"2018-05-17 03:00:10",
				"event_end_date_time":"2018-05-17 04:00:10",
				"event_address1":"NYC rails 1234",
				"event_address_2":"New York city , United States",
				"event_org_id":"1",
				"event_created_dtm":"2018-05-17 05:55:02",
				"tickets":[
					{
						"ticket_id":"1",
						"ticket_type":"type 1",
						"ticket_price":"12.5",
						"ticket_event_id":"1",
						"ticket_created_dtm":"2018-05-17 07:59:34"
					},
					{
						"ticket_id":"2",
						"ticket_type":"type 2",
						"ticket_price":"13",
						"ticket_event_id":"1",
						"ticket_created_dtm":"2018-05-17 07:59:34"
					}
				]
			},
			{...}
		]