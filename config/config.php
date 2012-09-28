<?php
return array(
	/** Width of the image */
	'width' => 200,

	/** Height of the image */
	'height' => 70,

	/** Dictionary word file (empty for random text) */
	'wordsFile' => 'words'.DS.'en.php',

	/**
     * Path for resource files (fonts, words, etc.)
     *
     * "resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     */
	'resourcesPath'	=> __DIR__.DS.'..'.DS.'resources'.DS,
	
	/** Min word length (for non-dictionary random text generation) */
	'minWordLength' => 5,

	/**
	 * Max word length (for non-dictionary random text generation)
	 * 
	 * Used for dictionary words indicating the word-length
	 * for font-size modification purposes
	 */
	'maxWordLength' => 6,

    /** Sessionname to store the original text */
	'session_var' => 'session_coolcaptcha',

    /** Background color in RGB-array */
	'backgroundColor' => array(255, 255, 255),

	/** Foreground colors in RGB-array */
	'colors' => array(
	        array(27,78,181), // blue
	        array(22,163,35), // green
	        array(214,36,7),  // red
	),

	/** Shadow color in RGB-array or null */
	'shadowColor' => null,

	/** Horizontal line through the text */
	'lineWidth' => 0,

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
	'fonts' => array(
        'Antykwa'  => array('spacing' => -3, 'minSize' => 27, 'maxSize' => 30, 'font' => 'AntykwaBold.ttf'),
        'Candice'  => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 31, 'font' => 'Candice.ttf'),
        'DingDong' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 30, 'font' => 'Ding-DongDaddyO.ttf'),
        'Duality'  => array('spacing' => -2, 'minSize' => 30, 'maxSize' => 38, 'font' => 'Duality.ttf'),
        'Heineken' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 34, 'font' => 'Heineken.ttf'),
        'Jura'     => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 32, 'font' => 'Jura.ttf'),
        'StayPuft' => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 32, 'font' => 'StayPuft.ttf'),
        'Times'    => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 34, 'font' => 'TimesNewRomanBold.ttf'),
        'VeraSans' => array('spacing' => -1, 'minSize' => 20, 'maxSize' => 28, 'font' => 'VeraSansBold.ttf'),
    ),

    /** Wave configuracion in X and Y axes */
    'Yperiod' => 12,
    'Yamplitude' => 14,
    'Xperiod' => 11,
    'Xamplitude' => 5,

    /** letter rotation clockwise */
    'maxRotation' => 8,

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    'scale' => 2,

     /** 
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    'blur' => false,

    /** Debug? */
    'debug' => false,

    /** Image format: jpg, png or gif */
    'imageFormat' => 'jpg',

    /**
    * JPG image quality
    */
    'jpgQuality' => 65,
);