<?php

class GeneraImmaginiShell extends AppShell {
    
    public $uses = array(
		'Articolo',
		'CategoriaWeb'
	);
	
	public function main() {
				
		// esegui il crop di no-image per le categorie
		$dstFolderBox = APP.'webroot/img/areariservata/categorie/cropped/box/';
		$this->cropImage(APP.'webroot/img/areariservata/', $dstFolderBox, 'no-image.jpg', 250, 250);
		// esegui il crop di no-image per gli articoli
		$dstFolderBox = APP.'webroot/img/areariservata/articoli/cropped/box/';
		$dstFolderScheda = APP.'webroot/img/areariservata/articoli/cropped/scheda/';
		$this->cropImage(APP.'webroot/img/areariservata/', $dstFolderBox, 'no-image.jpg', 250, 250);
		$this->cropImage(APP.'webroot/img/areariservata/', $dstFolderScheda, 'no-image.jpg', 600, 600);
		
		
		$articoli = $this->Articolo->find('all', array('fields' => array('id'), 'recursive' => -1));
		foreach($articoli as $articolo) {
			//$this->createCroppedImagesArticolo($articolo['Articolo']['id']);
			$this->createResizedImagesArticolo($articolo['Articolo']['id']);
		}
		$categorie = $this->CategoriaWeb->find('all', array('fields' => array('id'), 'recursive' => -1));
		foreach($categorie as $categoria) {
			//$this->createCroppedImagesCategoria($categoria['CategoriaWeb']['id']);
		}		
    }
    
    public function createResizedImagesArticolo($id_articolo)
	{
		$srcFolder = APP.'webroot/img/areariservata/articoli/original/';		
		$dstFolderBox = APP.'webroot/img/areariservata/articoli/resized/box/';
		$dstFolderScheda = APP.'webroot/img/areariservata/articoli/resized/scheda/';

		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob($srcFolder.$id_articolo.".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob($srcFolder.$id_articolo."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob($srcFolder.$id_articolo."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		if(sizeof($images) == 0) return; //l'immagine dell'articolo/sottocategoria/categoria non è stata caricata

		foreach($images as $image) 
		{
			$this->resizeImage($srcFolder, $dstFolderBox, basename($image), 300);
			$this->resizeImage($srcFolder, $dstFolderScheda, basename($image), 600);
		}
	}
    
    public function createCroppedImagesArticolo($id_articolo)
	{
		$srcFolder = APP.'webroot/img/areariservata/articoli/original/';		
		$dstFolderBox = APP.'webroot/img/areariservata/articoli/cropped/box/';
		$dstFolderScheda = APP.'webroot/img/areariservata/articoli/cropped/scheda/';

		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob($srcFolder.$id_articolo.".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob($srcFolder.$id_articolo."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob($srcFolder.$id_articolo."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		if(sizeof($images) == 0) return; //l'immagine dell'articolo/sottocategoria/categoria non è stata caricata

		foreach($images as $image) 
		{
			$this->cropImage($srcFolder, $dstFolderBox, basename($image), 300, 300);
			$this->cropImage($srcFolder, $dstFolderScheda, basename($image), 600, 600);
		}
	}

	public function createCroppedImagesCategoria($id_categoria)
	{
		$srcFolder = APP.'webroot/img/areariservata/categorie/original/';		
		$dstFolderBox = APP.'webroot/img/areariservata/categorie/cropped/box/';

		$images = array();
		//verifica se esiste un'immagine principale per la categoria
		$imagesFound = glob($srcFolder.$id_categoria.".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per la categoria con nome $id_categoria-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob($srcFolder.$id_categoria."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob($srcFolder.$id_categoria."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		if(sizeof($images) == 0) return; //l'immagine dell'articolo/sottocategoria/categoria non è stata caricata

		foreach($images as $image) 
		{
			$this->cropImage($srcFolder, $dstFolderBox, basename($image), 300, 300);
		}
	}

	/*
		crea una copia "cropped" dell'immagine con dimensioni pari a quelle specificate

		@param $srcFolder
			directory di origine dell'immagine
		@param $dstFolder
			folder di destinazione dell'immagine cropped
		@param $imageName
			nome dell'immagine completo di estensione
		@param $widthCropped
			larghezza dell'immagine cropped
		@param $heightCropped
			altezza dell'immagine cropped
		
	*/
	public function cropImage($srcFolder, $dstFolder, $imageName, $widthCropped, $heightCropped)
	{
		if(file_exists($dstFolder.$imageName)) return; //già cropped!

		$imgsize = getimagesize($srcFolder.$imageName);
		$widthOriginal = $imgsize[0];
		$heightOriginal = $imgsize[1];	
		
		$widthRatio = $widthOriginal/$widthCropped;
		$heightRatio = $heightOriginal/$heightCropped;

		//determina il livello di zoom da applicare all'immagine
		$smallRatio = ($widthRatio > $heightRatio) ? $heightRatio : $widthRatio;
		$bigRatio = ($widthRatio < $heightRatio) ? $heightRatio : $widthRatio;
		$zoom = 1/$smallRatio; //è sempre il valore corretto, sia che smallRatio sia < 1 sia che sia > 1 o = 1
		
		//calcola le dimensioni dell'immagine zoomed per determinare le coordinate x y della porzione da copiare
		$widthZoomed = round($widthOriginal*$zoom);
		$heightZoomed = round($heightOriginal*$zoom);
		//calcola le coordinate dell'area dell'immagine originale di cui eseguire il crop
		if( abs($widthCropped-$widthZoomed) > abs($heightCropped-$heightZoomed) )
		{
			//è la larghezza ad uscire fuori dall'area del crop. prendi la parte centrale
			$src_x = round( abs($widthCropped-$widthZoomed)*0.5/$zoom );
			$src_y = 0;
		}
		else
		{
			//è l'altezza ad uscire fuori dall'area del crop. prendi la parte centrale
			$src_x = 0;
			$src_y = round( abs($heightCropped-$heightZoomed)*0.5/$zoom );
		}
	
		// Resample
		$image_p = imagecreatetruecolor($widthCropped, $heightCropped);
		$path_parts = pathinfo($srcFolder.$imageName);
		switch($path_parts['extension'])
		{
			case "jpeg":
			case "jpg":
			case "JPEG":
			case "JPG":
				$image = imagecreatefromjpeg($srcFolder.$imageName);
			break;

			case "gif":
			case "GIF":
				$image = imagecreatefromgif($srcFolder.$imageName);
			break;

			case "png":
			case "PNG":
				$image = imagecreatefrompng($srcFolder.$imageName);
			break;

			default:
				return; //non sono in grado di elaborare l'immagine
		}
		imagecopyresampled($image_p, $image, 0, 0, $src_x, $src_y, $widthCropped, $heightCropped, round($widthCropped/$zoom), round($heightCropped/$zoom));

		// Output
		switch($path_parts['extension'])
		{
			case "jpeg":
			case "jpg":
			case "JPEG":
			case "JPG":
				imagejpeg($image_p, $dstFolder.$imageName, 90);
			break;

			case "gif":
			case "GIF":
				imagegif($image_p, $dstFolder.$imageName);
			break;

			case "png":
			case "PNG":
				imagepng($image_p, $dstFolder.$imageName, 3);
			break;

			default:
				return; //non sono in grado di elaborare l'immagine
		}
	}
	
	
	public function resizeImage($srcFolder, $dstFolder, $imageName, $heightResized)
	{
		if(file_exists($dstFolder.$imageName)) return; //già resized!

		$imgsize = getimagesize($srcFolder.$imageName);
		$widthOriginal = $imgsize[0];
		$heightOriginal = $imgsize[1];	
		
		$zoom = floatval($heightOriginal)/$heightResized;

		$widthResized = intval( $widthOriginal/$zoom );
		
		// Resample
		$image_p = imagecreatetruecolor($widthResized, $heightResized);
		$path_parts = pathinfo($srcFolder.$imageName);
		switch($path_parts['extension'])
		{
			case "jpeg":
			case "jpg":
			case "JPEG":
			case "JPG":
				$image = imagecreatefromjpeg($srcFolder.$imageName);
			break;

			case "gif":
			case "GIF":
				$image = imagecreatefromgif($srcFolder.$imageName);
			break;

			case "png":
			case "PNG":
				$image = imagecreatefrompng($srcFolder.$imageName);
			break;

			default:
				return; //non sono in grado di elaborare l'immagine
		}
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $widthResized, $heightResized, $widthOriginal, $heightOriginal);

		// Output
		switch($path_parts['extension'])
		{
			case "jpeg":
			case "jpg":
			case "JPEG":
			case "JPG":
				imagejpeg($image_p, $dstFolder.$imageName, 90);
			break;

			case "gif":
			case "GIF":
				imagegif($image_p, $dstFolder.$imageName);
			break;

			case "png":
			case "PNG":
				imagepng($image_p, $dstFolder.$imageName, 3);
			break;

			default:
				return; //non sono in grado di elaborare l'immagine
		}
	}
    
    
}
 
