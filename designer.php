<?php
/**
 *
 * @filesource   html.php
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\{QRCodeOutputException, QRImage};
use function imagecopyresampled, imagecreatefrompng, imagesx, imagesy, is_file, is_readable;

require_once 'vendor/autoload.php';

/**
 * @property \chillerlan\QRCodeExamples\LogoOptions $options
 */
class QRImageWithLogo extends QRImage{

	/**
	 * @param string|null $file
	 * @param string|null $logo
	 *
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string $file = null, string $logo = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		// of course you could accept other formats too (such as resource or Imagick)
		// i'm not checking for the file type either for simplicity reasons (assuming PNG)
		if(!is_file($logo) || !is_readable($logo)){
			throw new QRCodeOutputException('invalid logo');
		}

		$this->matrix->setLogoSpace(
			$this->options->logoSpaceWidth,
			$this->options->logoSpaceHeight
			// not utilizing the position here
		);

		// there's no need to save the result of dump() into $this->image here
		parent::dump($file);

		$im = imagecreatefrompng($logo);

		// get logo image size
		$w = imagesx($im);
		$h = imagesy($im);

		// set new logo size, leave a border of 1 module (no proportional resize/centering)
		$lw = ($this->options->logoSpaceWidth - 2) * $this->options->scale;
		$lh = ($this->options->logoSpaceHeight - 2) * $this->options->scale;

		// get the qrcode size
		$ql = $this->matrix->size() * $this->options->scale;

		// scale the logo and copy it over. done!
		imagecopyresampled($this->image, $im, ($ql - $lw) / 2, ($ql - $lh) / 2, 0, 0, $lw, $lh, $w, $h);

		$imageData = $this->dumpImage();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		if($this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

}
class LogoOptions extends QROptions{
	// size in QR modules, multiply with QROptions::$scale for pixel size
	protected int $logoSpaceWidth;
	protected int $logoSpaceHeight;
}

header('Content-Type: text/html; charset=utf-8');

function f_quietzone(){
    return isset($_GET["quietzone"]) ? $_GET["quietzone"] : "#FFFFFF";
}

function f_findertrue(){
    return isset($_GET["findertrue"]) ? $_GET["findertrue"] : "#A71111";
}
function f_finderfalse(){
    return isset($_GET["finderfalse"]) ? $_GET["finderfalse"] : "#FFBFBF";
}

function f_alignmenttrue(){
    return isset($_GET["alignmenttrue"]) ? $_GET["alignmenttrue"] : "#A70364";
}
function f_alignmentfalse(){
    return isset($_GET["alignmentfalse"]) ? $_GET["alignmentfalse"] : "#FFC9C9";
}

function f_timingtrue(){
    return isset($_GET["timingtrue"]) ? $_GET["timingtrue"] : "#98005D";
}
function f_timingfalse(){
    return isset($_GET["timingfalse"]) ? $_GET["timingfalse"] : "#FFB8E9";
}

function f_formattrue(){
    return isset($_GET["formattrue"]) ? $_GET["formattrue"] : "#003804";
}
function f_formatfalse(){
    return isset($_GET["formatfalse"]) ? $_GET["formatfalse"] : "#00FB12";
}

function f_versiontrue(){
    return isset($_GET["versiontrue"]) ? $_GET["versiontrue"] : "#650098";
}
function f_versionfalse(){
    return isset($_GET["versionfalse"]) ? $_GET["versionfalse"] : "#E0B8FF";
}

function f_datatrue(){
    return isset($_GET["datatrue"]) ? $_GET["datatrue"] : "#4A6000";
}
function f_datafalse(){
    return isset($_GET["datafalse"]) ? $_GET["datafalse"] : "#ECF9BE";
}

function f_darkmodule(){
    return isset($_GET["darkmodule"]) ? $_GET["darkmodule"] : "#080063";
}

function f_separator(){
    return isset($_GET["separator"]) ? $_GET["separator"] : "#AFBFBF";
}

function f_logo(){
    return isset($_GET["logo"]) ? $_GET["logo"] : "logo.png";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode test</title>
	<style>
		body{
			margin: 0em;
			padding: 0;
		}

		div.qrcode{
			margin: 0;
			padding: 0;
		}

		/* rows */
		div.qrcode > div {
			margin: 0;
			padding: 0;
			height: 10px;
		}

		/* modules */
		div.qrcode > div > span {
			display: inline-block;
			width: 10px;
			height: 10px;
		}

        span {
            margin:0px;
            display: inline-block;
			width: 10px;
			height: 10px;
        }

        div {
            height:10px;
            padding:0px;
            margin:0px;
        }

		div.qrcode > div > span {
			background-color: #ccc;
		}
	</style>
</head>
<body>
    <form action="/wp-content/plugins/scan2payme/" method="get">
        <label for="quietzone">Quietzone:</label>
        <input type="text" id="quietzone" name="quietzone" value="<?php echo f_quietzone(); ?>"><br><br>
        <label for="findertrue">Finder true:</label>
        <input type="text" id="findertrue" name="findertrue" value="<?php echo f_findertrue(); ?>"><br><br>
        <label for="finderfalse">Finder false:</label>
        <input type="text" id="finderfalse" name="finderfalse" value="<?php echo f_finderfalse(); ?>"><br><br>
        <label for="alignmenttrue">Alignment true:</label>
        <input type="text" id="alignmenttrue" name="alignmenttrue" value="<?php echo f_alignmenttrue(); ?>"><br><br>
        <label for="alignmentfalse">Alignment false:</label>
        <input type="text" id="alignmentfalse" name="alignmentfalse" value="<?php echo f_alignmentfalse(); ?>"><br><br>
        <label for="timingtrue">Timing true:</label>
        <input type="text" id="timingtrue" name="timingtrue" value="<?php echo f_timingtrue(); ?>"><br><br>
        <label for="timingfalse">timing false:</label>
        <input type="text" id="timingfalse" name="timingfalse" value="<?php echo f_timingfalse(); ?>"><br><br>
        <label for="formattrue">Format true:</label>
        <input type="text" id="formattrue" name="formattrue" value="<?php echo f_formattrue(); ?>"><br><br>
        <label for="formatfalse">format false:</label>
        <input type="text" id="formatfalse" name="formatfalse" value="<?php echo f_formatfalse(); ?>"><br><br>
        <label for="versiontrue">Version true:</label>
        <input type="text" id="versiontrue" name="versiontrue" value="<?php echo f_versiontrue(); ?>"><br><br>
        <label for="versionfalse">Version false:</label>
        <input type="text" id="versionfalse" name="versionfalse" value="<?php echo f_versionfalse(); ?>"><br><br>
        <label for="datatrue">Data true:</label>
        <input type="text" id="datatrue" name="datatrue" value="<?php echo f_datatrue(); ?>"><br><br>
        <label for="datafalse">Data false:</label>
        <input type="text" id="datafalse" name="datafalse" value="<?php echo f_datafalse(); ?>"><br><br>
        <label for="darkmodule">Dark module:</label>
        <input type="text" id="darkmodule" name="darkmodule" value="<?php echo f_darkmodule(); ?>"><br><br>
        <label for="separator">Separator:</label>
        <input type="text" id="separator" name="separator" value="<?php echo f_separator(); ?>"><br><br>
        <label for="logo">Logo:</label>
        <input type="text" id="logo" name="logo" value="<?php echo f_logo(); ?>"><br><br>
        <input type="submit"/>
    </form>
	<div class="qrcode">
<?php

	$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

	$options = new QROptions([
		'version' => 7,
		'outputType' => QRCode::OUTPUT_IMAGE_PNG,
		'eccLevel' => QRCode::ECC_H,
		'moduleValues' => [
			// finder
			1536 => f_findertrue(), // dark (true)
			6    => f_finderfalse(), // light (false)
			// alignment
			2560 => f_alignmenttrue(),
			10   => f_alignmentfalse(),
			// timing
			3072 => f_timingtrue(),
			12   => f_timingfalse(),
			// format
			3584 => f_formattrue(),
			14   => f_formatfalse(),
			// version
			4096 => f_versiontrue(),
			16   => f_versionfalse(),
			// data
			1024 => f_datatrue(),
			4    => f_datafalse(),
			// darkmodule
			512  => f_darkmodule(),
			// separator
			8    => f_separator(),
			// quietzone
			18   => f_quietzone(),
		],
	]);

    $logoOptions = new LogoOptions;
    $logoOptions->version          = 7;
    $logoOptions->eccLevel         = QRCode::ECC_H;
    $logoOptions->imageBase64      = true;
    $logoOptions->logoSpaceWidth   = 13;
    $logoOptions->logoSpaceHeight  = 13;
    $logoOptions->scale            = 5;
    $logoOptions->imageTransparent = false;

    $qrCodePlain = new QRCode($options);

    echo '<img alt="Embedded Image" src="'.$qrCodePlain->render($data).'" />';
    echo '<br />';

    $qrOutputInterface = new QRImageWithLogo($logoOptions, $qrCodePlain->getMatrix($data));
	$picdata = $qrOutputInterface->dump(null, __DIR__.'/'.f_logo());
    echo '<img alt="Embedded Image" src="'.$picdata.'" />';

?>
	</div>
</body>
</html>



