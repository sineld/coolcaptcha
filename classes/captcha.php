<?php
/**
 * Script para la generación de CAPTCHAS
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 *
 */


namespace CoolCaptcha;

//se: $captcha = new Captcha();

// OPTIONAL Change configuration...
//$captcha->wordsFile = 'words/es.php';
//$captcha->session_var = 'secretword';
//$captcha->imageFormat = 'png';
//$captcha->lineWidth = 3;
//$captcha->scale = 3; $captcha->blur = true;
//$captcha->resourcesPath = "/var/cool-php-captcha/resources";

// OPTIONAL Simple autodetect language example
/*
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array('en', 'es');
    $lang  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($lang, $langs)) {
        $captcha->wordsFile = "words/$lang.php";
    }
}
*/

// Image generation
//se: $captcha->generate();

/**
 * Captcha class
 *
 */
class Captcha {

    /** Width of the image */
    public static $width;

    /** Height of the image */
    public static $height;

    /** Dictionary word file (empty for random text) */
    public static $wordsFile;

    /**
     * Path for resource files (fonts, words, etc.)
     *
     * "resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     */
 
    public static $resourcesPath;

    /** Min word length (for non-dictionary random text generation) */
    public static $minWordLength;

    /**
     * Max word length (for non-dictionary random text generation)
     * 
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
    public static $maxWordLength;

    /** Sessionname to store the original text */
    public static $session_var;

    /** Background color in RGB-array */
    public static $backgroundColor;

    /** Foreground colors in RGB-array */
    public static $colors;

    /** Shadow color in RGB-array or null */
    public static $shadowColor; //array(0, 0, 0);

    /** Horizontal line through the text */
    public static $lineWidth;

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public static $fonts;

    /** Wave configuracion in X and Y axes */
    public static $Yperiod;
    public static $Yamplitude;
    public static $Xperiod;
    public static $Xamplitude;

    /** letter rotation clockwise */
    public static $maxRotation;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    public static $scale;

    /** 
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public static $blur;

    /** Debug? */
    public static $debug;
    
    /** Image format: jpg, png or gif */
    public static $imageFormat;

    /**
    * JPG image quality
    */
    public static $jpgQuality;

    /** GD image */
    public static $im;


    public static $textFinalX = 200;

    /** GD BG Color */
    public static $GdBgColor;

    /** GD Foreground color */
    public static $GdFgColor;

    /** Config array */
    protected static $config;

    final private function __construct() {}

    /**
     * Loads in the config and sets the variables
     *
     * @access  protected
     * @return  void
     */
    protected static function loadconfig()
    {
        // load the config
        static::$config = \Config::get('coolcaptcha::config');

        // update the defaults with the configed values
        foreach(static::$config as $key => $value)
        {
            static::${$key} = $value;
            //print_r($value);
        }
    }

    /**
     * Returns an URL to the captcha image
     * For example, you can use in your view something like
     * <img src="<?php echo Captcha\Captcha::img(); ?>" alt="" />
     *
     * @access  public
     * @return  string
     */
    public static function img() {
        return \URL::to('coolcaptcha?'.mt_rand(1, 100000)); //add a random number to avoid browser caching issues
    }

/*
    public static function sin()
    {
        if (!isset(static::$resourcesPath)) static::loadconfig();
        echo static::$resourcesPath.' selam';
    }
*/
    public static function generate() 
    {
        if (!isset(static::$resourcesPath)) static::loadconfig();

        $ini = microtime(true);

        /** Initialization */
        static::ImageAllocate();
        
        /** Text insertion */
        $text = static::GetCaptchaText();
        $fontcfg  = static::$fonts[array_rand(static::$fonts)];
        static::WriteText($text, $fontcfg);

        // $_SESSION[static::$session_var] = $text;

        //echo $text.'<br>'.\Hash::make($text);
        //die;

        //Save to session
        \Session::put(static::$session_var, \Hash::make($text));

        /** Transformations */
        if (!empty(static::$lineWidth)) {
            static::WriteLine();
        }
        static::WaveImage();
        if (static::$blur && function_exists('imagefilter')) {
            imagefilter(static::$im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        static::ReduceImage();


        if (static::$debug) {
            imagestring(static::$im, 1, 1, static::$height-8,
                "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
                static::$GdFgColor
            );
        }


        /** Output */
        static::WriteImage();
        static::Cleanup();
    }


    /**
     * Checks if the supplied captcha test value matches the stored one
     *
     * @param   string  $value
     * @access  public
     * @return  bool
     */
    public static function check($value)
    {
        if (!isset(static::$session_var)) static::loadconfig();
        $session_captcha_hash = \Session::get(static::$session_var, null);

        return $value != null && $session_captcha_hash != null && \Hash::check($value, $session_captcha_hash);
    }

    /**
     * Creates the image resources
     */
    public static function ImageAllocate()
    {
        // Cleanup
        if (!empty(static::$im)) {
            imagedestroy(static::$im);
        }

        static::$im = imagecreatetruecolor(static::$width*static::$scale, static::$height*static::$scale);

        // Background color
        static::$GdBgColor = imagecolorallocate(static::$im,
            static::$backgroundColor[0],
            static::$backgroundColor[1],
            static::$backgroundColor[2]
        );
        imagefilledrectangle(static::$im, 0, 0, static::$width*static::$scale, static::$height*static::$scale, static::$GdBgColor);

        // Foreground color
        $color           = static::$colors[mt_rand(0, sizeof(static::$colors)-1)];
        static::$GdFgColor = imagecolorallocate(static::$im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty(static::$shadowColor) && is_array(static::$shadowColor) && sizeof(static::$shadowColor) >= 3) {
            static::$GdShadowColor = imagecolorallocate(static::$im,
                static::$shadowColor[0],
                static::$shadowColor[1],
                static::$shadowColor[2]
            );
        }
    }

    /**
     * Text generation
     *
     * @return string Text
     */
    public static function GetCaptchaText() {
        $text = static::GetDictionaryCaptchaText();
        if (!$text) {
            $text = static::GetRandomCaptchaText();
        }
        return $text;
    }

    /**
     * Random text generation
     *
     * @return string Text
     */
    public static function GetRandomCaptchaText($length = null) {
        if (empty($length)) {
            $length = rand(static::$minWordLength, static::$maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstvwyz";
        $vocals = "aeiou";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++) {
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1);
            } else {
                $text .= substr($words, mt_rand(0, 22), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }

    /**
     * Random dictionary word generation
     *
     * @param boolean $extended Add extended "fake" words
     * @return string Word
     */
    public static function GetDictionaryCaptchaText($extended = false) {
        if (empty(static::$wordsFile)) {
            return false;
        }

        // Full path of words file
        if (substr(static::$wordsFile, 0, 1) == '/') {
            $wordsfile = static::$wordsFile;
        } else {
            $wordsfile = static::$resourcesPath.'/'.static::$wordsFile;
        }

        if (!file_exists($wordsfile)) {
            return false;
        }

        $fp     = fopen($wordsfile, "r");
        $length = strlen(fgets($fp));
        if (!$length) {
            return false;
        }
        $line   = rand(1, (filesize($wordsfile)/$length)-2);
        if (fseek($fp, $length*$line) == -1) {
            return false;
        }
        $text = trim(fgets($fp));
        fclose($fp);


        /** Change ramdom volcals */
        if ($extended) {
            $text   = preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
            $vocals = array('a', 'e', 'i', 'o', 'u');
            foreach ($text as $i => $char) {
                if (mt_rand(0, 1) && in_array($char, $vocals)) {
                    $text[$i] = $vocals[mt_rand(0, 4)];
                }
            }
            $text = implode('', $text);
        }

        return $text;
    }

    /**
     * Horizontal line insertion
     */
    public static function WriteLine() {

        $x1 = static::$width*static::$scale*.15;
        $x2 = static::$textFinalX;
        $y1 = rand(static::$height*static::$scale*.40, static::$height*static::$scale*.65);
        $y2 = rand(static::$height*static::$scale*.40, static::$height*static::$scale*.65);
        $width = static::$lineWidth/2*static::$scale;

        for ($i = $width*-1; $i <= $width; $i++) {
            imageline(static::$im, $x1, $y1+$i, $x2, $y2+$i, static::$GdFgColor);
        }
    }

    /**
     * Text insertion
     */
    public static function WriteText($text, $fontcfg = array()) {
        if (empty(static::$fontcfg)) {
            // Select the font configuration
            $fontcfg  = static::$fonts[array_rand(static::$fonts)];
        }

        // Full path of font file
        $fontfile = static::$resourcesPath.'fonts/'.$fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = static::$maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);

        // Text generation (char by char)
        $x      = 20*static::$scale;
        $y      = round((static::$height*27/40)*static::$scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand(static::$maxRotation*-1, static::$maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*static::$scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);

            if (static::$shadowColor) {
                $coords = imagettftext(static::$im, $fontsize, $degree,
                    $x+static::$scale, $y+static::$scale,
                    static::$GdShadowColor, $fontfile, $letter);
            }
            $coords = imagettftext(static::$im, $fontsize, $degree,
                $x, $y,
                static::$GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*static::$scale);
        }

        $textFinalX = $x;
    }

    /**
     * Wave filter
     */
    public static function WaveImage() {
        // X-axis wave generation
        $xp = static::$scale*static::$Xperiod*rand(1,3);
        $k = rand(0, 100);
        for ($i = 0; $i < (static::$width*static::$scale); $i++) {
            imagecopy(static::$im, static::$im,
                $i-1, sin($k+$i/$xp) * (static::$scale*static::$Xamplitude),
                $i, 0, 1, static::$height*static::$scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = static::$scale*static::$Yperiod*rand(1,2);
        for ($i = 0; $i < (static::$height*static::$scale); $i++) {
            imagecopy(static::$im, static::$im,
                sin($k+$i/$yp) * (static::$scale*static::$Yamplitude), $i-1,
                0, $i, static::$width*static::$scale, 1);
        }
    }

    /**
     * Reduce the image to the final size
     */
    public static function ReduceImage() {
        // Reduzco el tamaño de la imagen
        $imResampled = imagecreatetruecolor(static::$width, static::$height);
        imagecopyresampled($imResampled, static::$im,
            0, 0, 0, 0,
            static::$width, static::$height,
            static::$width*static::$scale, static::$height*static::$scale
        );
        imagedestroy(static::$im);
        static::$im = $imResampled;
    }

    /**
     * File generation
     */
    public static function WriteImage() {
        // Set no cache
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Pragma: no-cache');

        if (static::$imageFormat == 'png' && function_exists('imagepng')) {
            header("Content-type: image/png");
            imagepng(static::$im);
        } 
        else if(static::$imageFormat == 'gif' && function_exists('imagegif'))
        {
            header('Content-Type: image/gif');
            imagegif(static::$im);
        }
        else
        {
            header("Content-type: image/jpeg");
            imagejpeg(static::$im, null, static::$jpgQuality);
        }
    }

    /**
     * Cleanup
     */
    public static function Cleanup() {
        imagedestroy(static::$im);
    }
}

?>
