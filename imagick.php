<?php

thumb::$drivers['imagick'] = function($thumb) {
    try {
        $img = new Imagick($thumb->root());

        if ($img->getImageFormat() && $img->getNumberImages()) {
            // Animated gif
            $img = $img->coalesceImages();

            foreach ($img as $frame) {
                $dimensions = clone $thumb->source->dimensions();
                $dimensions->fitWidthAndHeight($thumb->options['width'], $thumb->options['height'], $thumb->options['upscale']);
                $frame->thumbnailImage($dimensions->width(), $dimensions->height(), false);
                $frame->setImagePage($dimensions->width(), $dimensions->height(), 0, 0); 
            }

            $img = $img->optimizeImageLayers();
            $img = $img->deconstructImages(); 
            $img->writeImages($thumb->destination->root, true); 

        } else {
            // Other image
            if($thumb->options['crop']) {
                $img->cropThumbnailImage($thumb->options['width'], $thumb->options['height']);
            } else {
                $dimensions = clone $thumb->source->dimensions();
                $dimensions->fitWidthAndHeight($thumb->options['width'], $thumb->options['height'], $thumb->options['upscale']);
                @$img->thumbnailImage($dimensions->width(), $dimensions->height());
            }

            if($thumb->options['grayscale']) {
                $img->modulateImage(100, 0, 100); 
            }

            if($thumb->options['blur']) {
                $img->gaussianBlurImage($thumb->options['blurpx']);
            }

            if($thumb->options['autoOrient']) {
            }   

            $img->setImageCompressionQuality( (int) $thumb->options['quality']);
            $img->stripImage();

            @$img->writeImage($thumb->destination->root);
        }
    } catch(Exception $e) {
        $thumb->error = $e;
    }
};
