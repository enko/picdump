<?php

namespace enko\PicdumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Imagine;


class DefaultController extends Controller
{
    /**
     * Landing page
     *
     * @Route("/", name="index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    private function getHash($filename) {
      return base64_encode(hash_file('ripemd160',$filename,true));
    }

    private function getMediaPath() {
      return realpath(dirname(__FILE__).'/../../../../media');
    }

    private function imageExists($filename) {
      $orig_path = realpath($this->getMediaPath().'/orig');
      return file_exists($orig_path . '/' . $this->getHash($filename));
    }

    private function hashExists($store,$hash) {
      $dir = realpath($this->getMediaPath().'/'.$store);
      return file_exists($dir . '/' . $hash);
    }

    /**
     * Handle the uploads
     *
     * @Route("/")
     * @Method("POST")
     * @Template()
     */
    public function handleUploadAction()
    {
      foreach($_FILES as $file) {
        if($file['size'] > 15 * 1024 * 1024) {
          return ['error' => 'Maximum file size is 15 MB'];
        }
        $hash = $this->getHash($file['tmp_name']);
        if ($this->imageExists($file['tmp_name'])) {
          return $this->redirect($this->generateUrl('image_view', array('hash' => $this->getHash($file['tmp_name']))));
        }
        $filename = realpath($this->getMediaPath().'/orig') . '/' . $hash;
        if (move_uploaded_file($file['tmp_name'], $filename)) {
          // generate a thumbnail
          $imagine = new Imagine\Imagick\Imagine();
          $size    = new Imagine\Image\Box(512, 512);
          $mode    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
          $thumb = realpath($this->getMediaPath().'/thumb') . '/' . $hash;
          $imagine->open($filename)
            ->thumbnail($size, $mode)
            ->save($thumb);
          return $this->redirect($this->generateUrl('image_view', array('hash' => $hash)));
        }
      }
    }

    /**
     * Detailpage for an image
     *
     * @Route("/{hash}", name="image_view")
     * @Method("GET")
     * @Template("PicdumpBundle:Default:view.html.twig")
     */
    public function viewImageAction($hash) {
      if (substr($hash,strlen($hash)-5) == 'thumb') {
        $hash = substr($hash,0,strlen($hash)-6);
        if ($this->hashExists('thumb',$hash)) {
          $file = $this->getMediaPath().'/thumb' . '/' . $hash;
          if (file_exists($file)) {
            $response = new Response(file_get_contents($file));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $response->headers->set('Content-Type', finfo_file($finfo,$file));
            finfo_close($finfo);
            return $response;
          }
        }
        throw $this->createNotFoundException('Image does not exists.');
      }
      if (substr($hash,strlen($hash)-4) == 'orig') {
        $hash = substr($hash,0,strlen($hash)-5);
        if ($this->hashExists('thumb',$hash)) {
          $file = $this->getMediaPath().'/orig' . '/' . $hash;
          if (file_exists($file)) {
            $response = new Response(file_get_contents($file));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $response->headers->set('Content-Type', finfo_file($finfo,$file));
            finfo_close($finfo);
            return $response;
          }
        }
        throw $this->createNotFoundException('Image does not exists.');
      }
      if ($this->hashExists('orig',$hash)) {
        return ['hash' => $hash];
      } else {
        throw $this->createNotFoundException('Image does not exists.');
      }
    }

    /**
     * Detailpage for an image
     *
     * @Route("/{hash}_thumb", name="image_thumb_view")
     * @Method("GET")
     */
    public function viewThumbImageAction($hash) {
      die($hash);


    }

}
