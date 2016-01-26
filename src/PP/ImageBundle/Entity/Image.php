<?php

namespace PP\ImageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\ImageBundle\Entity\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
     public function __construct()
    {
         $this->sizeList = array();
    }
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="upload_dire", type="string", length=255)
     */
    private $uploadDir;
    
    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="avgColor", type="string", length=255)
     */
    private $avgColor;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;
    
    /**
     * @var Integer
     *
     * @ORM\Column(name="width", type="integer")
     */
    private $width;
    
    /**
     * @var Integer
     *
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

    /**
     * @var array
     *
     * @ORM\Column(name="size_list", type="array")
     */
    private $sizeList;
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    
    /**
     * Set sizeList
     *
     * @param array $sizeList
     *
     * @return Image
     */
    public function setSizeList($sizeList)
    {
        $this->sizeList = $sizeList;

        return $this;
    }

    /**
     * Get sizeList
     *
     * @return array
     */
    public function getSizeList()
    {
        return $this->sizeList;
    }
    
    public function addSizeList($size)
    {
        return array_push($this->sizeList, $size);
    }
    
    //////////////////////////////////////////////
    //////////////////////////////////////////////
    
    /**
    *@Assert\File(
    *     maxSize = "5M",   
    *     maxSizeMessage = "The file is too large ({{ size }}Mo). Allowed maximum size is {{ limit }}Mo"
    *)
    *@Assert\Image()
    */
    private $file;

    private $tempFilename;
    private $tempId;

    public function getWebPath($size)
    {
            return $this->getUploadDir().'/'.$size.'/'.$this->getId().'.'.$this->getUrl();
    }
    

    public function getFile()
    {
            return $this->file;
    }
    
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (null !== $this->url) {
          // On sauvegarde l'extension du fichier pour le supprimer plus tard
          $this->tempFilename = $this->url;

          // On réinitialise les valeurs des attributs url et alt
          $this->url = null;
          $this->alt = null;
        }
    }
	
    /**
    * @ORM\PrePersist()
    * @ORM\PreUpdate()
    */
    public function preUpload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif)
        if (null === $this->file) {
          return;
        }

        // Le nom du fichier est son id, on doit juste stocker également son extension
        // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url »
        $this->url = $this->file->guessExtension();

        // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
        $this->alt = $this->file->getClientOriginalName();
        
        $rTotal = null;
        $gTotal = null;
        $bTotal = null;
        $total = null;
        $size = getimagesize($this->file);
        $width = $size[0];
        $height = $size[1];
        $this->width = $width;
        $this->height = $height;
        $mime = $size['mime'];        
        switch($mime){
            case 'image/gif':
                    $image_create = "imagecreatefromgif";
                    break;

            case 'image/png':
                    $image_create = "imagecreatefrompng";
                    break;

            case 'image/jpeg':
                    $image_create = "imagecreatefromjpeg";
                    break;
            default:
                    return false;
                    break;
        }
        $i = $image_create($this->file);
        for ($x=0;$x<imagesx($i);$x+=10) {
                for ($y=0;$y<imagesy($i);$y+=10) {
                        if($x<$width && $y<$height){
                            $rgb = imagecolorat($i,$x,$y);
                            $r   = ($rgb >> 16) & 0xFF;
                            $g   = ($rgb >> 8) & 0xFF;
                            $b   = $rgb & 0xFF;
                            $rTotal += $r;
                            $gTotal += $g;
                            $bTotal += $b;
                            $total++;
                        }
                }
        }
        $rAverage = round($rTotal/$total);
        $gAverage = round($gTotal/$total);
        $bAverage = round($bTotal/$total);
        
        $this->avgColor = dechex($rAverage).dechex($gAverage). dechex($bAverage);
    }
  
    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()
    */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif)
        if (null === $this->file) {
          return;
        }
        
        if (null !== $this->tempFilename) {
          $oldFile = $this->getUploadRootDir().'/original/'.$this->id.'.'.$this->tempFilename;
          if (file_exists($oldFile)) {
            unlink($oldFile);
          }
        }        

        // On déplace le fichier envoyé dans le répertoire de notre choix
        $this->file->move(
          $this->getUploadRootDir().'/original', // Le répertoire de destination
          $this->id.'.'.$this->url   // Le nom du fichier à créer, ici « id.extension »
        );
    }

    /**
    * @ORM\PreRemove()
    */
    public function preRemoveUpload()
    {
            // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
            $this->tempFilename = $this->getUploadRootDir().'/original/'.$this->id.'.'.$this->url;
            $this->tempId = $this->id;
    }

    /**
    * @ORM\PostRemove()
    */
    public function removeUpload()
    {            
            if (file_exists($this->tempFilename)) {              
              unlink($this->tempFilename);
              foreach ($this->sizeList as $size){
                  unlink($this->getUploadRootDir().'/'.$size.'/'.$this->tempId.'.'.$this->url);
            }
            }            
    }

    public function getUploadDir()
    {            
            return $this->uploadDir;
    }
    
    public function setUploadDir($folder)
    {
            $this->uploadDir = 'uploads/img/'.$folder;            
    }  

    public function getUploadRootDir()
    {            
            return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }
    
    public function resize($foldername, $cropWidth, $cropHeight){
        ini_set('memory_limit', '-1');
       
        $resizeRatio = $cropWidth;
        if($cropHeight > $resizeRatio)$resizeRatio =$cropHeight;
        //$resizeRatio *= 3;
        $size = getimagesize($this->getUploadRootDir()  .'/original/'. $this->id .'.'. $this->url);
        $width = $size[0];
        $height = $size[1];
        $mime = $size['mime'];
        
        switch($mime){
            case 'image/gif':
                    $image_create = "imagecreatefromgif";
                    $image = "imagegif";
                    break;

            case 'image/png':
                    $image_create = "imagecreatefrompng";
                    $image = "imagepng";
                    $quality = 7;
                    break;

            case 'image/jpeg':
                    $image_create = "imagecreatefromjpeg";
                    $image = "imagejpeg";
                    $quality = 80;
                    break;

            default:
                    return false;
                    break;
        }
                    
        $source = $image_create($this->getUploadRootDir().'/original/'. $this->id .'.'. $this->url);
                        
        
        $ratio = $size[0]/$size[1];
        if( $ratio > 1) {                
            $width = $resizeRatio;
            $height = $resizeRatio/$ratio;
        }
        else {
            $width = $resizeRatio*$ratio;
            $height = $resizeRatio;                
        }
        $destination = imagecreatetruecolor($width, $height);                       
        
        $thumbnailDir = $this->getUploadRootDir().'/'.$foldername.'/'. $this->id .'.'. $this->url;
        
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $width,$height,$size[0],$size[1]);        
        imagejpeg($destination, $thumbnailDir, 100);
        $this->crop($cropWidth, $cropHeight, $thumbnailDir, $thumbnailDir);                
        
    }
    
    public function crop($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];           
        $mime = $imgsize['mime'];
        
        switch($mime){
                    case 'image/gif':
                            $image_create = "imagecreatefromgif";
                            $image = "imagegif";
                            break;

                    case 'image/png':
                            $image_create = "imagecreatefrompng";
                            $image = "imagepng";
                            $quality = 7;
                            break;

                    case 'image/jpeg':
                            $image_create = "imagecreatefromjpeg";
                            $image = "imagejpeg";
                            $quality = 100;
                            break;

                    default:
                            return false;                            
            }                        
            $dst_img = imagecreatetruecolor($max_width, $max_height);
            $src_img = $image_create($source_file);

            $width_new = $height * $max_width / $max_height;
            $height_new = $width * $max_height / $max_width;
            //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
            if($width_new > $width){
                    //cut point by height
                    $h_point = (($height - $height_new) / 2);
                    //copy image
                    imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
            }else{
                    //cut point by width
                    $w_point = (($width - $width_new) / 2);
                    imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
            }

            $image($dst_img, $dst_dir, $quality);

            if($dst_img)imagedestroy($dst_img);
            if($src_img)imagedestroy($src_img);
    }
    
    public function resizeWidth($foldername, $cropWidth) {
        ini_set('memory_limit', '-1');
       
        $resizeRatio = $cropWidth;        
        //$resizeRatio *= 3;
        $size = getimagesize($this->getUploadRootDir()  .'/original/'. $this->id .'.'. $this->url);
        $width = $size[0];
        $height = $size[1];
        $mime = $size['mime'];
               
        switch($mime){
            case 'image/gif':
                    $image_create = "imagecreatefromgif";
                    $image = "imagegif";
                    break;

            case 'image/png':
                    $image_create = "imagecreatefrompng";
                    $image = "imagepng";
                    $quality = 7;
                    break;

            case 'image/jpeg':
                    $image_create = "imagecreatefromjpeg";
                    $image = "imagejpeg";
                    $quality = 80;
                    break;

            default:
                    return false;
                    break;
        }
                    
        $source = $image_create($this->getUploadRootDir().'/original/'. $this->id .'.'. $this->url);
                        
        if($size[0] > $cropWidth){
            $ratio = $size[0]/$size[1];
            if( $ratio > 1) {                
                $width = $resizeRatio;
                $height = $resizeRatio/$ratio;
            }
            else {
                $width = $resizeRatio*$ratio;
                $height = $resizeRatio;                
            }
        }else{
            $width = $size[0];
            $height = $size[1];
        }
        $destination = imagecreatetruecolor($width, $height);                       
        
        $thumbnailDir = $this->getUploadRootDir().'/'.$foldername.'/'. $this->id .'.'. $this->url;
        
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $width,$height,$size[0],$size[1]);        
        imagejpeg($destination, $thumbnailDir, 100);                
    }
      

    /**
     * Set avgColor
     *
     * @param string $avgColor
     *
     * @return Image
     */
    public function setAvgColor($avgColor)
    {
        $this->avgColor = $avgColor;

        return $this;
    }

    /**
     * Get avgColor
     *
     * @return string
     */
    public function getAvgColor()
    {
        return $this->avgColor;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }
}
