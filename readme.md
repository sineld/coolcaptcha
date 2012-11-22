# CoolCaptcha Bundle ver. 1.1
CoolCaptcha is a captcha bundle for the Laravel framework. It does not require any external dependencies and is easy to use.

## Installation

	php artisan bundle:install coolcaptcha

Note: **CoolCaptcha requires to have a Session Driver set**. Check /application/config/session.php. I recommend you to set **'driver' => 'file'**, especially for development. Setting "'driver' => 'cookie'" on localhost may cause domain-related problems.

## Bundle Registration

Add the following to your **/application/bundles.php** file:

	'coolcaptcha' => array('auto' => true, 'handles' => 'coolcaptcha'),

## Usage

In **/application/routes.php** place something like:

	// on "get" we display /views/layouts/register.php, which contains our registration form
	Route::get('register', function()
	{
		return View::make('layouts.register');
	});

	// on "post" we validate the input
	Route::post('register', function()
	{
		$rules = array(
			'captcha' => 'coolcaptcha|required'
		);
		$messages = array(
			'coolcaptcha' => 'Invalid captcha',
		);

		$validation = Validator::make(Input::all(), $rules, $messages);

		if ($validation->valid())
		{
			// valid captcha
		} else
		{
			return Redirect::to('register')->with_errors($validation);
		}
	});

Feel free to add to the above code other validation rules according to your application.

Next, in your view (say, **/views/layouts/register.php**), place something like:

	echo Form::open('register', 'POST', array('class' => 'register_form'));
	... [other fields] ...
	echo Form::text('captcha', '', array('class' => 'captchainput', 'placeholder' => 'Insert captcha...'));
	echo Form::image(CoolCaptcha\Captcha::img(), 'captcha', array('class' => 'captchaimg'));
	... [other fields] ...
	echo Form::close();

## Customisation

You can configure all settings in **config/config.php**. Each line of the config is thoroughly documented.

## Further information
This bundle is maintained by Sinan Eldem (sinan@sinaneldem.com.tr). If you have any questions or suggestions, email me. You can always grab the latest version from http://github.com/sineld/coolcaptcha