<?php

Route::get('coolcaptcha', function()
{
	CoolCaptcha\Captcha::generate();
});