<?php

Autoloader::map(array(
	'CoolCaptcha\\Captcha' => __DIR__.DS.'classes'.DS.'captcha.php',
));

Laravel\Validator::register('coolcaptcha', function($attribute, $value, $parameters)
{
	return CoolCaptcha\Captcha::check($value);
});