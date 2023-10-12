<?php

 function getDatabaseConfig():array
{
	return [
		"database" => [
			//database tesring
			"test" => [
				"url" => "mysql:host=localhost:3306;dbname=php_login_management_test",
				"username" => "root",
				"password" => "Admin123"
			],
			//database production
			"prod" => [
				"url" => "mysql:host=localhost:3306;dbname=php_login_management",
				"username" => "root",
				"password" => "Admin123"
			]
		]
	];
}