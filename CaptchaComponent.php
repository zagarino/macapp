<?php
app::uses('Component', 'Controller');

App::uses('CakeSession', 'Model/Datasource');

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');


class CaptchaComponent extends Component
{
	private $width = 250;
	private $height = 100;

	public function __construct(ComponentCollection $collection, $settings = array())
	{
		if(isset($settings['folder']))
		{
			$this->folder = $settings['folder'];
		}
		else
		{
			$this->folder = ROOT . DS . 'app' . DS . 'webroot' . DS . 'img' . DS . 'captcha' . DS;
		}
	}

	public function startup(Controller $controller)
	{
		$dir = new Folder($this->folder);

		$captchas = $dir->find('.*\.png');

		foreach ($captchas as $captcha)
		{
			$captcha = new File($dir->pwd() . DS . $captcha);

			$difference = time() - $captcha->lastChange();
			if ($difference >= 144) // file is older than 24min
			{
				$captcha->delete();
			}
		}

		parent::startup($controller);
	}

	public function delete()
	{
		return (CakeSession::delete('captcha')) ? true : false;
	}

	public function create()
	{
		$name = md5(microtime());
		$code = strtoupper(substr(md5(microtime()),rand(0,5),5));

		CakeSession::write('captcha.name', $name);
		CakeSession::write('captcha.code', $code);

		$im = imagecreate($this->width, $this->height);
		$background_color = ImageColorAllocate ($im, 217, 35, 42);
		$text_color = imagecolorallocate($im, 255, 255, 255);
		$icon_color = imagecolorallocate($im,27,25,25);
		$sport_color = imagecolorallocate($im,116,23,25);
		//imagestring($im, 5, 5, 5,  $code , $text_color);

		$fontPath = ROOT . DS . 'app' . DS . 'webroot' .DS . 'fonts' . DS;

		for($cursor = 0;$cursor < 4;$cursor++) //clouds
		{
			imagettftext($im,rand(15,25),0,(30+rand(30,40))*$cursor,30,$icon_color,$fontPath.'fontawesome-webfont.ttf','');
		}

		for($cursor = 0;$cursor < 7;$cursor++) //trees
		{
			imagettftext($im,rand(15,25),0,30*$cursor+rand(0,40),98,$icon_color,$fontPath.'fontawesome-webfont.ttf','');
		}

		$icons = array('',''); //
		if (isset($icons) && !empty($icons))
		{
			for($cursor = 0;$cursor < 3;$cursor++)
			{
				imagettftext($im,rand(45,55),0,70*$cursor+rand(0,40),95,$sport_color,$fontPath.'markallen.ttf',$icons[rand(0,count($icons)-1)]);
			}
		}

		//imagettftext($im,20,0,$this->width-30,30,$icon_color,ROOT.DS.'app'.DS.'webroot'.DS.'fonts'.DS.'markallen.ttf',''); // possible sun
		
		$fonts = array('Oswald-Regular.ttf','ArchivoNarrow-Regular.ttf');
		if (isset($fonts) && !empty($fonts))
		{
			for($cursor = 0;$cursor < strlen($code);$cursor++)
			{
				$size = rand(25,45);
				$angle = rand(-24,25);
				$xPos = 15+45*$cursor;
				$yPos = 70;
				$font = $fontPath.$fonts[rand(0,count($fonts)-1)];
				//if(rand(0,3) != 7)
				//{
					//imagettftext($im,$size+3,$angle,$xPos-1,$yPos+1,$sport_color,$font,substr($code,$cursor,1));
					//imagettftext($im,$size,$angle,$xPos,$yPos,$background_color,$font,substr($code,$cursor,1));
				//}
				//else
				//{
				//}
					imagettftext($im,$size,$angle,$xPos+2,$yPos+2,$icon_color,$font,substr($code,$cursor,1));
					imagettftext($im,$size,$angle,$xPos,$yPos,$text_color,$font,substr($code,$cursor,1));
			}
		}

		//imagettftext($im,24,0,15,15,$text_color,ROOT.DS.'app'.DS.'webroot'.DS.'fonts'.DS.'Oswald-Regular.ttf','Teste... Omega: &#937;');
		//imageline($im, 0, 90, 300, 90, $icon_color);
		$fileName = $name . '.png';

		imagepng($im, $this->folder . $fileName);
		imagedestroy($im);

		return 'captcha' . DS . $fileName;
	}

	public function compare()
	{
	}
}

0?>
